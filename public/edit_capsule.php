<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid capsule ID.");

// Fetch capsule data
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$capsule = $stmt->fetch();

if (!$capsule) {
    die("Capsule not found or you don’t have permission to edit it.");
}

// Fetch all emails shared for this capsule
$sharedStmt = $pdo->prepare("
    SELECT u.email 
    FROM shared_access sa
    JOIN users u ON sa.shared_with_user = u.id
    WHERE sa.capsule_id = ?
");
$sharedStmt->execute([$id]);
$sharedUsers = $sharedStmt->fetchAll(PDO::FETCH_COLUMN);
$sharedEmails = implode(", ", $sharedUsers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Capsule</title>
</head>
<body>

<header>
    <nav>
        <a href="dashboard.php">Dashboard</a> |
        <a href="my_capsules.php">My Capsules</a> |
        <a href="shared_capsules.php">Shared With Me</a> |
        <a href="notifications.php">Notifications</a> |
        <a href="../src/api/logout.php">Logout</a>
    </nav>
</header>

<h2>Edit Capsule</h2>

<form action="../src/api/edit_capsule.php" method="POST">
    <input type="hidden" name="capsule_id" value="<?= htmlspecialchars($capsule['id']) ?>">

    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($capsule['title']) ?>" required>
    <br><br>

    <label>Message:</label>
    <textarea name="message" rows="5" required><?= htmlspecialchars($capsule['message']) ?></textarea>
    <br><br>

    <label>Unlock Date:</label>
    <input type="date" name="unlock_date" value="<?= htmlspecialchars($capsule['unlock_date']) ?>" required>
    <br><br>

    <label>Visibility:</label>
    <select name="visibility" id="visibility" required>
        <option value="public"   <?= $capsule['visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
        <option value="private"  <?= $capsule['visibility'] === 'private' ? 'selected' : '' ?>>Private</option>
        <option value="shared"   <?= $capsule['visibility'] === 'shared' ? 'selected' : '' ?>>Shared</option>
    </select>
    <br><br>

    <!-- Only visible when Shared -->
    <div id="sharedSection" style="display: <?= $capsule['visibility'] === 'shared' ? 'block' : 'none' ?>;">
        <label>Share with (emails, comma-separated):</label><br>
        <input type="text" name="shared_emails" placeholder="user1@example.com, user2@example.com"
               value="<?= htmlspecialchars($sharedEmails) ?>" style="width: 300px;">
        <br><br>
    </div>

    <button type="submit">Update Capsule</button>
</form>

<br>
<a href="my_capsules.php">← Back to My Capsules</a>

<script>
    const visibility = document.getElementById("visibility");
    const sharedSection = document.getElementById("sharedSection");

    visibility.addEventListener("change", () => {
        sharedSection.style.display = (visibility.value === "shared") ? "block" : "none";
    });
</script>

</body>
</html>
