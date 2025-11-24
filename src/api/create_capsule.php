<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: /digital-time-capsule/public/login.html?error=login_required");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $unlock_date = $_POST['unlock_date'] ?? '';
    $visibility = $_POST['visibility'] ?? 'private';

    // Validate fields
    if (empty($title) || empty($message) || empty($unlock_date)) {
        header("Location: /digital-time-capsule/public/create_capsule.html?error=empty_fields");
        exit;
    }

    // Determine status
    $status = (strtotime($unlock_date) <= time()) ? 'unlocked' : 'locked';

    // Prepare media upload folder
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $mediaPaths = ['images' => [], 'videos' => [], 'audio' => []];

    // Function to handle multiple file uploads
    function handleFiles($files, $dir, &$paths) {
        foreach ($files['tmp_name'] as $i => $tmpName) {
            if (empty($tmpName)) continue;
            $name = uniqid() . '_' . basename($files['name'][$i]);
            $target = $dir . $name;
            if (move_uploaded_file($tmpName, $target)) {
                $paths[] = 'uploads/' . $name;
            }
        }
    }

    // Upload media
    if (!empty($_FILES['images']['name'][0])) handleFiles($_FILES['images'], $uploadDir, $mediaPaths['images']);
    if (!empty($_FILES['videos']['name'][0])) handleFiles($_FILES['videos'], $uploadDir, $mediaPaths['videos']);
    if (!empty($_FILES['audio']['name'][0])) handleFiles($_FILES['audio'], $uploadDir, $mediaPaths['audio']);

    $mediaJson = json_encode($mediaPaths);

    // Insert into database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO capsules 
            (user_id, title, message, unlock_date, visibility, status, capsule_media)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $title, $message, $unlock_date, $visibility, $status, $mediaJson]);

        // Success popup with localStorage
        echo "<script>
                localStorage.setItem('capsule_success', '1');
                window.location.href = '../../public/create_capsule.html';
              </script>";
        exit;
    } catch (PDOException $e) {
        // Redirect with error
        header("Location: /digital-time-capsule/public/create_capsule.html?error=db_error");
        exit;
    }
} else {
    header("Location: /digital-time-capsule/public/create_capsule.html?error=invalid_request");
    exit;
}
