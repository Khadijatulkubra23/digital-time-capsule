<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$stmt = $pdo->prepare("SELECT * FROM capsules WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$capsules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Capsules</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>My Time Capsules</h2>
    <a href="create_capsule.html">+ Create New Capsule</a><br><br>

    <table border="1" cellpadding="8">
        <tr>
            <th>Title</th>
            <th>Unlock Date</th>
            <th>Visibility</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($capsules): ?>
            <?php foreach ($capsules as $capsule): ?>
                <tr>
                    <td><?= htmlspecialchars($capsule['title']) ?></td>
                    <td><?= htmlspecialchars($capsule['unlock_date']) ?></td>
                    <td><?= htmlspecialchars($capsule['visibility']) ?></td>
                    <td><?= htmlspecialchars($capsule['status']) ?></td>
                    <td>
                        <?php if ($capsule['status'] === 'locked'): ?>
                            Locked
                        <?php else: ?>
                            <a href="../src/api/lock_capsule.php?id=<?= $capsule['id'] ?>">Lock</a> |
                            <a href="edit_capsule.php?id=<?= $capsule['id'] ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No capsules found yet. Create one!</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
