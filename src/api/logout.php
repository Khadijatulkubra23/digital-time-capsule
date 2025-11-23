<?php
session_start();

// Clear all session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login page
header("Location: /digital-time-capsule/public/login.html");
exit;
?>
