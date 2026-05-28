<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle pre-flight browser security authentication checks safely
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
include '../wegha_db.php';

// 2. AUTHENTICATION SHIELD PROTECTION
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Authentication required. Please log in to submit a review."]);
    exit();
}

// 3. READ THE RAW JSON BODY INPUT STREAM FROM REACT
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    $user_id = $_SESSION['user_id'];
    $rating  = intval($inputData['rating'] ?? 0);
    $comment = mysqli_real_escape_string($conn, trim($inputData['comment'] ?? ''));

    // Safely evaluate if standard itinerary package ID or custom configuration path (NULL)
    $package_id     = !empty($inputData['package_id']) ? intval($inputData['package_id']) : null;
    $package_id_val = ($package_id !== null) ? $package_id : "NULL";

    // Server-side validation boundary rules
    if ($rating < 1 || $rating > 5 || empty($comment)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Invalid input constraints. Score rating and comment text are required."]);
        exit();
    }

    // 4. WRITE RECORD: Insert review item in isolated quarantine status ('Pending')
    $sql = "INSERT INTO reviews (user_id, package_id, rating, comment, status) 
            VALUES ($user_id, $package_id_val, $rating, '$comment', 'Pending')";

    if (mysqli_query($conn, $sql)) {
        http_response_code(201); // Resource Created
        echo json_encode(["message" => "Your review was submitted successfully! It is currently in the queue awaiting admin approval."]);
        exit();
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Database transaction execution failure: " . mysqli_error($conn)]);
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty body payload data packet received."]);
}

$conn->close();
exit();
