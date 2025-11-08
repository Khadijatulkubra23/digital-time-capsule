<?php
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    // Check if user already exists

    $stmt =$pdo->prepare("SELECT id FROM users WHERE email =?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("Email already registered");
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashed]);

    echo "Registration successful! <a href='login.html'>Login</a>";
}
?>