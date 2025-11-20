<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to edit a capsule.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $capsule_id = $_POST['capsule_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $unlock_date = $_POST['unlock_date'] ?? '';

    if (!$capsule_id || !$title || !$message || !$unlock_date) {
        die("All fields are required.");
    }

    // Check if capsule belongs to the logged-in user
    $stmt = $pdo->prepare("SELECT status FROM capsules WHERE id = ? AND user_id = ?");
    $stmt->execute([$capsule_id, $_SESSION['user_id']]);
    $capsule = $stmt->fetch();

    if (!$capsule) {
        die("Capsule not found or access denied.");
    }

    if ($capsule['status'] === 'locked') {
        die("You cannot edit a locked capsule.");
    }

    // Update the capsule data
    $stmt = $pdo->prepare("
        UPDATE capsules
        SET title = ?, message = ?, unlock_date = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$title, $message, $unlock_date, $capsule_id, $_SESSION['user_id']]);

    echo "<p>Capsule updated successfully!</p>";
    echo '<a href="/public/my_capsules.php">Back to My Capsules</a>';
    exit;
}
?>
