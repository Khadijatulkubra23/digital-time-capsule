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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="../public/css/style.css">
  <style>
      body {
          font-family: "Poppins", Arial, sans-serif;
          background: #f3fbf5;
          color: #2f3b2f;
          margin: 0;
          padding: 0;
          position: relative;
      }
      header.main-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 20px 40px;
          background: #88d6a5;
          color: #fff;
          box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      }
      header.main-header h1 {
          font-size: 1.5rem;
          margin: 0;
      }
      header.main-header nav a {
          margin-left: 20px;
          text-decoration: none;
          color: #fff;
          font-weight: 500;
          transition: 0.2s;
      }
      header.main-header nav a:hover {
          text-decoration: underline;
      }
      main {
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: calc(100vh - 120px);
          position: relative;
          overflow: hidden;
      }
      main::before {
          content: "DIGITAL TIME CAPSULE";
          position: absolute;
          font-size: 6rem;
          color: rgba(200,200,200,0.1);
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%) rotate(-30deg);
          white-space: nowrap;
          pointer-events: none;
          z-index: 0;
      }
      .card {
          position: relative;
          z-index: 1;
          background: #fff;
          padding: 40px;
          border-radius: 15px;
          box-shadow: 0 5px 20px rgba(0,0,0,0.1);
          max-width: 700px;
          width: 90%;
          text-align: center;
      }
      .card h2 {
          margin-bottom: 20px;
          font-size: 1.8rem;
      }
      .btn {
          display: inline-block;
          padding: 12px 25px;
          margin: 10px;
          border-radius: 8px;
          background: #88d6a5;
          color: #fff;
          text-decoration: none;
          font-weight: 500;
          transition: 0.2s;
      }
      .btn:hover {
          background: #6cc88e;
      }
      footer.card {
          text-align: center;
          margin: 20px auto;
          padding: 10px 0;
          max-width: 700px;
          border-radius: 15px;
          background: #fff;
          box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      }
  </style>
</head>
<body>

<header class="main-header">
    <h1>Welcome, <?= htmlspecialchars($user['email']) ?>!</h1>
    <nav class="navbar">
        <a href="my_capsules.php">My Capsules</a>
        <a href="shared_capsules.php">Shared With Me</a>
        <a href="notifications.php">Notifications</a>
        <a href="../src/api/logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <div class="card">
        <h2>Your Digital Time Capsule</h2>
        <p>Store your memories. Unlock them when the time is right.</p>
        <div>
            <a href="create_capsule.html" class="btn">+ Create New Capsule</a>
            <a href="my_capsules.php" class="btn" style="background:#6cc88e;">View My Capsules</a>
            <a href="shared_capsules.php" class="btn" style="background:#88d6a5;">Shared With Me</a>
        </div>
    </div>
</main>

<footer class="card">
    <small>&copy; <span id="year"></span> Digital Time Capsule</small>
</footer>

<script>
    document.getElementById('year').textContent = new Date().getFullYear();
</script>

</body>
</html>
