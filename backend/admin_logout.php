<?php
session_start(); // 1. Open the current session

// 2. Clear all session variables
$_SESSION = array();

// 3. Destroy the session entirely
session_destroy();

// 4. Redirect the admin back to the login page
header("Location: admin_login.php");
exit();
