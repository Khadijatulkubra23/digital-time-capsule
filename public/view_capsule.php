<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: /digital-time-capsule/public/login.html");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid capsule ID.");

// Fetch capsule
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE id = ?");
$stmt->execute([$id]);
$capsule = $stmt->fetch();

if (!$capsule) die("Capsule not found.");

// Check access
$visibility = $capsule['visibility'];
$owner_id = $capsule['user_id'];
$viewer_id = $_SESSION['user_id'];

if ($visibility === 'private' && $viewer_id !== $owner_id) {
    die("This capsule is private.");
} elseif ($visibility === 'shared' && $viewer_id !== $owner_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM shared_access WHERE capsule_id = ? AND shared_with_user = ?");
    $stmt->execute([$id, $viewer_id]);
    if ($stmt->fetchColumn() == 0) die("You do not have access to this shared capsule.");
}

// Locked check
if ($capsule['status'] === 'locked') {
    die("This capsule will unlock on " . htmlspecialchars($capsule['unlock_date']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($capsule['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="main-header">
    <nav class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="my_capsules.php">My Capsules</a>
        <a href="shared_capsules.php">Shared With Me</a>
        <a href="notifications.php">Notifications</a>
        <a href="../src/api/logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <div class="card" style="max-width: 600px; margin: 30px auto;">
        <h2><?= htmlspecialchars($capsule['title']) ?></h2>

        <p><strong>Message:</strong></p>
        <p><?= nl2br(htmlspecialchars($capsule['message'])) ?></p>

        <p><strong>Unlock Date:</strong> <?= htmlspecialchars($capsule['unlock_date']) ?></p>
        <p><strong>Visibility:</strong> <?= ucfirst(htmlspecialchars($capsule['visibility'])) ?></p>

        <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($capsule['status'])) ?></p>

        <a href="my_capsules.php" class="btn" style="margin-top: 15px; display: inline-block;">← Back to My Capsules</a>
    </div>
</main>

<footer style="text-align: center; margin-top: 40px;">
    <small>&copy; <span id="year"></span> Digital Time Capsule</small>
</footer>

<script>
    document.getElementById('year').textContent = new Date().getFullYear();
</script>

</body>
</html>
