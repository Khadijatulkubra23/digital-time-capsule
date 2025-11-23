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
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="main-header">
    <h1>Digital Time Capsule</h1>
    <nav class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="my_capsules.php">My Capsules</a>
        <a href="shared_capsules.php">Shared With Me</a>
        <a href="notifications.php">Notifications</a>
        <a href="../src/api/logout.php" class="logout">Logout</a>
    </nav>
</header>

<main style="max-width: 800px; margin: 30px auto;">
    <h2>Notifications</h2>

    <?php if ($notifications): ?>
        <?php foreach ($notifications as $note): ?>
            <div class="notification <?= !$note['is_read'] ? 'new' : '' ?>">
                <span><?= htmlspecialchars($note['message']) ?></span>
                <?php if (!$note['is_read']): ?>
                    <a href="mark_read.php?id=<?= $note['id'] ?>" class="btn btn-small">Mark as Read</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <p>No notifications yet.</p>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
