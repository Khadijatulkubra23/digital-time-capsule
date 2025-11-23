<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /digital-time-capsule/public/login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch capsules shared with this user
$stmt = $pdo->prepare("
    SELECT c.*, u.email AS owner_name
    FROM capsules c
    JOIN shared_access s ON c.id = s.capsule_id
    JOIN users u ON c.user_id = u.id
    WHERE s.shared_with_user = ?
");
$stmt->execute([$user_id]);
$shared_capsules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shared With Me</title>
<link rel="stylesheet" href="../public/css/style.css">
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
    <h2>Capsules Shared With Me</h2>

    <?php if ($shared_capsules): ?>
        <?php foreach ($shared_capsules as $capsule): ?>
            <div class="card">
                <h3><?= htmlspecialchars($capsule['title']) ?></h3>
                <p><strong>Owner:</strong> <?= htmlspecialchars($capsule['owner_name']) ?></p>
                <p><strong>Unlock Date:</strong> <?= htmlspecialchars($capsule['unlock_date']) ?></p>
                <p><strong>Status:</strong> <?= $capsule['status'] === 'locked' ? "🔒 Locked" : "✅ Unlocked" ?></p>
                <p>
                    <?php if ($capsule['status'] === 'unlocked'): ?>
                        <a href="view_capsule.php?id=<?= $capsule['id'] ?>" class="btn">View</a>
                    <?php else: ?>
                        Locked
                    <?php endif; ?>
                </p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <p>No capsules have been shared with you yet.</p>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
