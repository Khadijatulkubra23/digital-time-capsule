<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid capsule ID.");

// Fetch capsule data
$stmt = $pdo->prepare("SELECT * FROM capsules WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$capsule = $stmt->fetch();

if (!$capsule) {
    die("Capsule not found or you don’t have permission to edit it.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Capsule</title>
</head>
<body>
  <h2>Edit Capsule</h2>

  <form action="../src/api/edit_capsule.php" method="POST">
    <input type="hidden" name="capsule_id" value="<?= htmlspecialchars($capsule['id']) ?>">

    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($capsule['title']) ?>" required><br><br>

    <label>Message:</label>
    <textarea name="message" rows="5" required><?= htmlspecialchars($capsule['message']) ?></textarea>



    <label>Unlock Date:</label>
    <input type="date" name="unlock_date" value="<?= htmlspecialchars($capsule['unlock_date']) ?>" required><br><br>
    
    <button type="submit">Update Capsule</button>
  </form>
</body>
</html>
