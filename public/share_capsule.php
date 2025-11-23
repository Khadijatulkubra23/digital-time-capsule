<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$id) {
    die("Invalid capsule ID.");
}

//  Check ownership
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE id = ?");
$stmt->execute([$id]);
$capsule = $stmt->fetch();

if (!$capsule) die("Capsule not found.");
if ($capsule['user_id'] != $user_id) die("You are not the owner.");

//  Handle POST — sharing capsule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shared_with = $_POST['shared_with'] ?? null;

    if ($shared_with) {
        // Check if already shared
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM shared_access WHERE capsule_id = ? AND shared_with_user = ?");
        $stmt->execute([$id, $shared_with]);
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            $stmt = $pdo->prepare("INSERT INTO shared_access (capsule_id, shared_with_user) VALUES (?, ?)");
            $stmt->execute([$id, $shared_with]);

            // Optional: notification
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, capsule_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$shared_with, $id, "A capsule has been shared with you."]);

            echo "Capsule shared successfully!";
        } else {
            echo "Already shared with this user.";
        }
    } else {
        echo "Select a user to share with.";
    }
}

//  Fetch all other users for dropdown
$stmt = $pdo->prepare("SELECT id, email FROM users WHERE id != ?");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll();
?>

<h2>Share Capsule: <?= htmlspecialchars($capsule['title']) ?></h2>

<form method="post">
    <label>Select User:</label>
    <select name="shared_with">
        <option value="">--Choose a user--</option>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['email']) ?></option>
            <?php endforeach; ?>
    </select>
    <button type="submit">Share</button>
</form>

<a href="my_capsules.php">← Back to My Capsule</a>