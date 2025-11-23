<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: notifications.php");
exit;