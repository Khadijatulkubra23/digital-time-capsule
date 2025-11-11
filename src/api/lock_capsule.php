<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Invalid capsule ID.");
}

// Update the capsule's status to locked - only if it belongs to the logged-in user
$stmt = $pdo->prepare("UPDATE capsules SET status = 'locked' WHERE id =? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

if ($stmt->rowCount() > 0) {
    echo "Capsule locked successfully.";
} else {
    echo "Failed to lock capsule. Maybe it doesn't belong to you or is already locked.";
}
?>