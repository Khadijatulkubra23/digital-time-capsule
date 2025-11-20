<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

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
// Visibility Rules
$visibility = $capsule['visibility'];
$owner_id = $capsule['user_id'];
$viewer_id = $_SESSION['user_id'] ?? null;

// Public – always allowed
if ($visibility === 'public') {
    // allowed
}

// Private – only owner
elseif ($visibility === 'private') {
    if ($viewer_id !== $owner_id) {
        die("This capsule is private.");
    }
}

// Shared – owner OR users added in shared_access table
elseif ($visibility === 'shared') {

    if ($viewer_id === $owner_id) {
        // owner is allowed
    } else {
        // Check if viewer is in shared_access
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM shared_access 
            WHERE capsule_id = ? AND shared_with_user = ?
        ");
        $stmt->execute([$id, $viewer_id]);
        $allowed = $stmt->fetchColumn();

        if ($allowed == 0) {
            die("You do not have access to this shared capsule.");
        }
    }
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
    <h1><?= htmlspecialchars($capsule['title']) ?></h1>
    <p><strong>Message:</strong></p>
    <p><?= nl2br(htmlspecialchars($capsule['message'])) ?></p>

    <p><strong>Unlock Date:</strong> <?= htmlspecialchars($capsule['unlock_date']) ?></p>
    <p><strong>Visibility:</strong> <?= htmlspecialchars($capsule['visibility']) ?></p>
    
    <br>
    <a href="my_capsules.php">← Back to My Capsules</a>
</body>
</html>