<?php
// 1. MANDATORY CORS & REST API HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
include '../wegha_db.php';

// 2. READ THE RAW JSON FROM REACT
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    $email    = mysqli_real_escape_string($conn, trim($inputData['email'] ?? ''));
    $password = trim($inputData['password'] ?? '');

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "Please fill in both email and password fields."]);
        exit();
    }

    // 3. FETCH THE USER RECORD
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // 4. VERIFY THE PASSWORD CRYPTOGRAPHY HASH MATRIX
        if (password_verify($password, $user['password_hash'])) {

            // SECURITY AUDIT CHECK: Block access instantly if admin toggled status flag to suspended
            if (intval($user['is_active']) === 0) {
                http_response_code(403); // 403 Forbidden Access
                echo json_encode(["error" => "Access Restricted: Your account has been suspended by administration."]);
                exit();
            }

            // Authentication Successful -> Initialize State Sessions
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];

            http_response_code(200); // 200 OK
            echo json_encode([
                "message"   => "Login successful.",
                "user_id"   => intval($user['user_id']),
                "full_name" => $user['full_name']
            ]);
        } else {
            http_response_code(401); // 401 Unauthorized
            echo json_encode(["error" => "Invalid password. Please check your credentials and try again."]);
        }
    } else {
        http_response_code(404); // 404 Not Found
        echo json_encode(["error" => "No account located matching that email address."]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty credential payload data package received."]);
}
$conn->close();
exit();
