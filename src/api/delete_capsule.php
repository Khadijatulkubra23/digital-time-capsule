<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$id || !$user_id) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

// Check ownership
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$capsule = $stmt->fetch();

if (!$capsule) {
    echo json_encode(["success" => false, "message" => "Capsule not found."]);
    exit;
}

// Delete capsule
$pdo->prepare("DELETE FROM capsules WHERE id = ? AND user_id = ?")->execute([$id, $user_id]);

echo json_encode(["success" => true, "message" => "Capsule deleted successfully!"]);
exit;
?>
