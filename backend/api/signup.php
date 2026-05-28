<?php
// 1. MANDATORY CORS & REST API HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle pre-flight browser authorization checks safely
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../wegha_db.php';

// 2. READ THE RAW JSON FROM REACT
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    // Map the keys exactly as they are named in React's state (fullName, email, password)
    $full_name = mysqli_real_escape_string($conn, trim($inputData['fullName'] ?? ''));
    $email     = mysqli_real_escape_string($conn, trim($inputData['email'] ?? ''));
    $password  = trim($inputData['password'] ?? '');

    // Basic server-side validation check
    if (empty($full_name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "All registration fields are required."]);
        exit();
    }

    // 3. AUDIT DUPLICATE USER PROFILE ENTRIES
    $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        http_response_code(409); // 409 Conflict
        echo json_encode(["error" => "An account with this email address already exists."]);
        $check_email->close();
        exit();
    }
    $check_email->close();

    // 4. SECURELY HASH PASSWORD & INSERT RECORD
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Explicitly set is_active to 1 (Active) during user enrollment
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, is_active) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $full_name, $email, $password_hash);

    if ($stmt->execute()) {
        http_response_code(201); // 201 Created
        echo json_encode(["message" => "Account created successfully! Welcome to Wegha."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database enrollment execution error: " . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty registration body payload received."]);
}
$conn->close();
exit();
