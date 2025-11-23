<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /digital-time-capsule/public/login.html");
    exit;
}

// Fetch notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../public/css/style.css">
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

<h2>Notifications</h2>

<ul>
    <?php if ($notifications): ?>
        <?php foreach ($notifications as $note): ?>
            <li>
                <?= htmlspecialchars($note['message']) ?>
                
                <?php if (!$note['is_read']): ?>
                    <strong>(New)</strong>
                <?php endif; ?>

                <a href="mark_read.php?id=<?= $note['id'] ?>">Mark as Read</a>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No notifications yet.</li>
    <?php endif; ?>
</ul>

</body>
</html>
