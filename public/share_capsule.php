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

            $message = "Capsule shared successfully!";
        } else {
            $message = "Already shared with this user.";
        }
    } else {
        $message = "Select a user to share with.";
    }
}

//  Fetch all other users for dropdown
$stmt = $pdo->prepare("SELECT id, email FROM users WHERE id != ?");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Capsule</title>
<style>
    body {
        font-family: "Poppins", sans-serif;
        background: #0d1b2a;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .share-container {
        background: #ffffff;
        padding: 32px;
        width: 430px;
        border-radius: 18px;
        border: 2px solid #d5f2df; /* light mint border */
        box-shadow: 0 8px 26px rgba(0,0,0,0.06);
        animation: fadeIn 0.35s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h2 {
        margin: 0 0 22px;
        font-size: 23px;
        text-align: center;
        font-weight: 600;
        color: #2f473a; /* deep mint green */
    }

    .message {
        padding: 13px 15px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .success { 
        background: #e8fdf2; 
        color: #2b6a4b; 
        border: 1px solid #c7f1d6;
    }

    .error { 
        background: #ffecec; 
        color: #a13838; 
        border: 1px solid #ffcccc;
    }

    label {
        display: block;
        margin-bottom: 7px;
        font-weight: 600;
        color: #2f473a;
    }

    select {
        width: 100%;
        padding: 14px;
        border-radius: 12px;
        border: 1px solid #c6ecd5; /* pistachio border */
        margin-bottom: 22px;
        font-size: 15px;
        background: #f4fdf7; /* soft mint background */
        color: #2f473a;
        outline: none;
        transition: 0.3s;
    }

    select:focus {
        border-color: #9ddfbf; /* darker pistachio */
        background: #f0fcf5;
    }

    button {
        width: 100%;
        padding: 14px;
        background: #bcefcf; /* soft pistachio */
        border: none;
        border-radius: 12px;
        font-size: 15px;
        color: #1e3d2f; 
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s ease;
    }

    button:hover {
        background: #a7e6bf; /* deeper pistachio mint */
    }

    .back {
        display: block;
        margin-top: 18px;
        text-align: center;
        color: #2b6a4b;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }

    .back:hover {
        text-decoration: underline;
    }
</style>

</head>

<body>

<div class="share-container">
    <h2>Share Capsule: <?= htmlspecialchars($capsule['title']) ?></h2>

    <?php if (!empty($message)) : ?>
        <div class="message <?= strpos($message,'success') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Select User:</label>
        <select name="shared_with">
            <option value="">-- Choose a user --</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>">
                    <?= htmlspecialchars($user['email']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Share Capsule</button>
    </form>

    <a href="my_capsules.php" class="back">← Back to My Capsules</a>
</div>

</body>
</html>
