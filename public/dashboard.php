<?php
session_start();
require_once __DIR__ . '/../src/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /public/login.html");
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
  <header>
      <h1>Welcome, <?= htmlspecialchars($user['email']) ?>!</h1>
      <nav>
          <a href="my_capsules.php">My Capsules</a> |
          <a href="shared_capsules.php">Shared With Me</a> |
          <a href="notifications.php">Notifications</a> |
          <a href="../src/api/logout.php">Logout</a>
      </nav>
  </header>

  <main>
      <p>Your capsules and notifications will appear here.</p>
  </main>
</body>
</html>
