<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$id || !$user_id) {
    echo json_encode(["success"=>false,"message"=>"Invalid request."]);
    exit;
}

// Fetch current status
$stmt = $pdo->prepare("SELECT status FROM capsules WHERE id=? AND user_id=?");
$stmt->execute([$id, $user_id]);
$capsule = $stmt->fetch();

if (!$capsule) {
    echo json_encode(["success"=>false,"message"=>"Capsule not found."]);
    exit;
}

// Toggle status
$new_status = $capsule['status'] === 'locked' ? 'unlocked' : 'locked';
$update = $pdo->prepare("UPDATE capsules SET status=? WHERE id=? AND user_id=?");
$update->execute([$new_status, $id, $user_id]);

echo json_encode([
    "success"=>true,
    "message"=>"Capsule status updated to " . ucfirst($new_status) . "!",
    "status"=>ucfirst($new_status)
]);
exit;
