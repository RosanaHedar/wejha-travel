<?php
// 1. MANDATORY CORS & REST API HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. INITIALIZE AND ERASE SESSION VALUES
session_start();

// Unset all session variables
$_SESSION = array();

// 3. FORCE THE BROWSER TO KILL THE SESSION COOKIE
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. DESTROY SERVER MEMORY ALLOCATION
session_destroy();

// 5. RESPOND TO REACT
http_response_code(200);
echo json_encode(["message" => "Logged out successfully. Session destroyed safely."]);
exit();
