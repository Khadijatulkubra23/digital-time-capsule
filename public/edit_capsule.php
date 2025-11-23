<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /public/login.html");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid capsule ID.");

// Initialize messages
$successMessage = '';
$errorMessage = '';

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['capsule_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $unlock_date = $_POST['unlock_date'] ?? '';
    $visibility = $_POST['visibility'] ?? 'private';
    $shared_emails = trim($_POST['shared_emails'] ?? '');
    
    if (!$id || empty($title) || empty($message) || empty($unlock_date)) {
        $errorMessage = "Please fill in all required fields.";
    } else {
        $status = (strtotime($unlock_date) <= time()) ? 'unlocked' : 'locked';

        try {
            // Handle media upload if any
            $mediaPath = null;
            if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/';
                $fileTmp = $_FILES['media']['tmp_name'];
                $fileName = uniqid('capsule_') . '_' . basename($_FILES['media']['name']);
                $fileDest = $uploadDir . $fileName;

                if (move_uploaded_file($fileTmp, $fileDest)) {
                    $mediaPath = $fileName;
                } else {
                    $errorMessage = "Failed to upload media.";
                }
            }

            // Update capsule
            if (!$errorMessage) {
                $query = "UPDATE capsules 
                          SET title=?, message=?, unlock_date=?, visibility=?, status=?";
                $params = [$title, $message, $unlock_date, $visibility, $status];

                if ($mediaPath) {
                    $query .= ", capsule_media=?";
                    $params[] = $mediaPath;
                }

                $query .= " WHERE id=? AND user_id=?";
                $params[] = $id;
                $params[] = $_SESSION['user_id'];

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);

                // Handle shared users
                if ($visibility === 'shared') {
                    $pdo->prepare("DELETE FROM shared_access WHERE capsule_id=?")->execute([$id]);
                    $emails = array_map('trim', explode(',', $shared_emails));
                    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE email=?");
                    $stmtInsert = $pdo->prepare("INSERT INTO shared_access (capsule_id, shared_with_user) VALUES (?, ?)");
                    foreach ($emails as $email) {
                        if ($email) {
                            $stmtUser->execute([$email]);
                            $userId = $stmtUser->fetchColumn();
                            if ($userId) $stmtInsert->execute([$id, $userId]);
                        }
                    }
                }

                // Redirect with success
                header("Location: edit_capsule.php?id=$id&success=1");
                exit;
            }

        } catch (PDOException $e) {
            $errorMessage = "Error updating capsule. Please try again.";
        }
    }
}

// Fetch capsule data for displaying the form
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE id=? AND user_id=?");
$stmt->execute([$id, $_SESSION['user_id']]);
$capsule = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$capsule) die("Capsule not found or you don’t have permission to edit it.");

// Fetch shared users
$sharedStmt = $pdo->prepare("
    SELECT u.email 
    FROM shared_access sa
    JOIN users u ON sa.shared_with_user = u.id
    WHERE sa.capsule_id = ?
");
$sharedStmt->execute([$id]);
$sharedUsers = $sharedStmt->fetchAll(PDO::FETCH_COLUMN);
$sharedEmails = implode(", ", $sharedUsers);

// Check for GET messages (after redirect)
if (isset($_GET['success'])) $successMessage = "Capsule updated successfully!";
if (isset($_GET['error'])) $errorMessage = "Error updating capsule. Please try again.";

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Capsule</title>
<link rel="stylesheet" href="css/style.css">
<style>
body { background: #f3fbf5; font-family: "Poppins", Arial, sans-serif; color: #2f3b2f; }
header { text-align: center; margin-bottom: 40px; }
nav a { margin: 0 10px; text-decoration: none; color: #4a5b4a; font-weight: 500; }
nav a:hover { color: #6cc88e; }
main { display: flex; justify-content: center; }
.card { background: #fff; padding: 30px 40px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-width: 600px; width: 100%; position: relative; }
h2 { text-align: center; margin-bottom: 20px; color: #2e4a34; }
label { font-weight: 500; display: block; margin-bottom: 5px; }
input[type="text"], input[type="date"], textarea, select { padding: 10px; border-radius: 8px; border: 1px solid #b8edc6; width: 100%; box-sizing: border-box; margin-bottom: 15px; font-size: 16px; }
button { width: 100%; padding: 12px; border-radius: 8px; border: none; background: #88d6a5; color: #fff; font-size: 16px; font-weight: 500; cursor: pointer; transition: 0.2s; }
button:hover { background: #6cc88e; }
a.back-link { display: block; margin-top: 20px; text-align: center; color: #6cc88e; }
a.back-link:hover { text-decoration: underline; }
img.capsule-image { max-width: 100%; margin-bottom: 15px; border-radius: 8px; }
.success-message { background: #88d6a5; color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align:center; }
.error-message { background: #f56565; color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align:center; }
</style>
</head>
<body>

<header class="main-header">
    <h2>Edit Capsule</h2>
    <nav class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="my_capsules.php">My Capsules</a>
        <a href="shared_capsules.php">Shared With Me</a>
        <a href="notifications.php">Notifications</a>
        <a href="../src/api/logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
<div class="card">

    <?php if ($successMessage): ?>
        <div id="successMessage" class="success-message"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="error-message"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="capsule_id" value="<?= htmlspecialchars($capsule['id']) ?>">

        <label>Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($capsule['title']) ?>" required>

        <label>Message:</label>
        <textarea name="message" rows="5" required><?= htmlspecialchars($capsule['message']) ?></textarea>

        <label>Unlock Date:</label>
        <input type="date" name="unlock_date" value="<?= htmlspecialchars($capsule['unlock_date']) ?>" required>

        <label>Visibility:</label>
        <select name="visibility" id="visibility" required>
            <option value="public" <?= $capsule['visibility']==='public'?'selected':''?>>Public</option>
            <option value="private" <?= $capsule['visibility']==='private'?'selected':''?>>Private</option>
            <option value="shared" <?= $capsule['visibility']==='shared'?'selected':''?>>Shared</option>
        </select>

        <div id="sharedSection" style="display: <?= $capsule['visibility']==='shared'?'block':'none' ?>;">
            <label>Share with (emails, comma-separated):</label>
            <input type="text" name="shared_emails" value="<?= htmlspecialchars($sharedEmails) ?>" placeholder="user1@example.com, user2@example.com">
        </div>

        <label>Current Media:</label>
        <?php if(!empty($capsule['capsule_media']) && file_exists(__DIR__.'/../uploads/'.$capsule['capsule_media'])): ?>
            <img src="../uploads/<?= htmlspecialchars($capsule['capsule_media']) ?>" alt="Capsule Media" class="capsule-image">
        <?php else: ?>
            <p>No media uploaded.</p>
        <?php endif; ?>

        <label>Update Media (optional):</label>
        <input type="file" name="media" accept="image/*,video/*">

        <button type="submit">Update Capsule</button>
    </form>

    <a href="my_capsules.php" class="back-link">← Back to My Capsules</a>
</div>
</main>

<script>
const visibility = document.getElementById("visibility");
const sharedSection = document.getElementById("sharedSection");
visibility.addEventListener("change", () => {
    sharedSection.style.display = (visibility.value === "shared") ? "block" : "none";
});

// Auto-hide success message
const successMessage = document.getElementById('successMessage');
if(successMessage){
    setTimeout(() => { successMessage.style.display = 'none'; }, 3000);
}
</script>

</body>
</html>
