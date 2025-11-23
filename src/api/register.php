<?php
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check for empty fields
    if (empty($name) || empty($email) || empty($password)) {
        header("Location: /digital-time-capsule/public/register.html?error=empty_fields");
        exit;
    }

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: /digital-time-capsule/public/register.html?error=email_exists");
        exit;
    }

    // Hash the password
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user into database
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashed]);

    // Redirect to register.html with success message
    header("Location: /digital-time-capsule/public/register.html?success=1");
    exit;
}
