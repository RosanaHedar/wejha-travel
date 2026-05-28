<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle browser pre-flight validation requests securely
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../wegha_db.php';

// 2. READ RAW JSON CONTEXT INPUT FROM THE STREAM
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    // Extracted payload keys mapped directly to custom body variables
    $name    = mysqli_real_escape_string($conn, trim($inputData['name'] ?? ''));
    $email   = mysqli_real_escape_string($conn, trim($inputData['email'] ?? ''));
    $phone   = mysqli_real_escape_string($conn, trim($inputData['phone'] ?? ''));
    $message = mysqli_real_escape_string($conn, trim($inputData['message'] ?? ''));

    // Server-side validation gatekeeping rules
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "All form fields are strictly required to log a support ticket."]);
        exit();
    }

    // 3. EXECUTE DATA RECORD PERSISTENCE
    $sql = "INSERT INTO contact_messages (name, email, phone, message, status) 
            VALUES ('$name', '$email', '$phone', '$message', 'Pending')";

    if (mysqli_query($conn, $sql)) {
        http_response_code(201); // Resource Created
        echo json_encode(["message" => "Your inquiries have been logged successfully! The support desk will contact you shortly."]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Database failure: Could not record message records: " . mysqli_error($conn)]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty body payload data packet received."]);
}

$conn->close();
exit();
