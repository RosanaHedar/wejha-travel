<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../wegha_db.php';

// 2. READ THE RAW JSON BODY STREAM FROM REACT
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    $email        = mysqli_real_escape_string($conn, trim($inputData['email'] ?? ''));
    $new_password = trim($inputData['new_password'] ?? '');

    // Server-side input validation guard
    if (empty($email) || empty($new_password)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Both email and new password fields are required."]);
        exit();
    }

    // 3. AUDIT AUDIENCE: Verify the email address actually exists first
    $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(["error" => "No registered account located matching that email address."]);
        $check_email->close();
        exit();
    }
    $check_email->close();

    // 4. SECURELY RE-HASH PASSWORD AND EXECUTE UPDATE
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        http_response_code(200); // OK
        echo json_encode(["message" => "Account password rewritten and updated successfully!"]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Database operation rewrite execution failure: " . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty password modification data payload received."]);
}
$conn->close();
exit();
