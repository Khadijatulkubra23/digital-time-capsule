<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: /digital-time-capsule/public/login.html");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid capsule ID.");
}

// Fetch capsule by ID
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE id = ?");
$stmt->execute([$id]);
$capsule = $stmt->fetch();

if (!$capsule) {
    die("Capsule not found.");
}

// Check visibility rules
$visibility = $capsule['visibility'];
$owner_id = $capsule['user_id'];
$viewer_id = $_SESSION['user_id'];

// Public – always allowed
if ($visibility === 'public') {
    // ok
}

// Private – only owner
elseif ($visibility === 'private') {
    if ($viewer_id !== $owner_id) {
        die("This capsule is private.");
    }
}

// Shared – owner OR users added in shared_access table
elseif ($visibility === 'shared') {
    if ($viewer_id !== $owner_id) {

        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM shared_access 
            WHERE capsule_id = ? AND shared_with_user = ?
        ");
        $stmt->execute([$id, $viewer_id]);
        $allowed = $stmt->fetchColumn();

        if ($allowed == 0) {
            die("You do not have access to this shared capsule.");
        }
    }
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

<h1><?= htmlspecialchars($capsule['title']) ?></h1>

<p><strong>Message:</strong></p>
<p><?= nl2br(htmlspecialchars($capsule['message'])) ?></p>

<p><strong>Unlock Date:</strong> <?= htmlspecialchars($capsule['unlock_date']) ?></p>
<p><strong>Visibility:</strong> <?= htmlspecialchars($capsule['visibility']) ?></p>

<br>
<a href="my_capsules.php">← Back to My Capsules</a>

</body>
</html>
