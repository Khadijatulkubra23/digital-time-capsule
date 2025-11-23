<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

// Check user session
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

<header>
    <nav>
        <a href="dashboard.php">Dashboard</a> |
        <a href="my_capsules.php">My Capsules</a> |
        <a href="shared_capsules.php">Shared With Me</a> |
        <a href="notifications.php">Notifications</a> |
        <a href="../src/api/logout.php">Logout</a>
    </nav>
</header>

<h2>Capsules Shared With Me</h2>

<?php if ($shared_capsules): ?>
<table border="1" cellpadding="8">
    <tr>
        <th>Title</th>
        <th>Owner</th>
        <th>Unlock Date</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($shared_capsules as $capsule): ?>
        <tr>
            <td><?= htmlspecialchars($capsule['title']) ?></td>
            <td><?= htmlspecialchars($capsule['owner_name']) ?></td>
            <td><?= htmlspecialchars($capsule['unlock_date']) ?></td>
            <td>
                <?= $capsule['status'] === 'locked' ? "🔒 Locked" : "✅ Unlocked" ?>
            </td>
            <td>
                <?php if ($capsule['status'] === 'unlocked'): ?>
                    <a href="view_capsule.php?id=<?= $capsule['id'] ?>">View</a>
                <?php else: ?>
                    Locked
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<?php else: ?>
<p>No capsules have been shared with you yet.</p>
<?php endif; ?>

</body>
</html>
