<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in first");
}

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $unlock_date = $_POST['unlock_date'] ?? '';
    $visibility = $_POST['visibility'] ?? 'private';

    // Validate required fields
    if (empty($title) || empty($message) || empty($unlock_date)) {
        die("All fields are required.");
    }

    try {

        // decide locked or unlocked
        $status = (strtotime($unlock_date) <= time()) ? 'unlocked' : 'locked';

        $stmt = $pdo->prepare("
            INSERT INTO capsules (user_id, title, message, unlock_date, visibility, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $message,
            $unlock_date,
            $visibility,
            $status
        ]);

        echo "Capsule created successfully!";

    } catch (Exception $e) {
        echo "Error creating capsule:" . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
