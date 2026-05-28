<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle pre-flight browser authorization validation loops securely
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
include '../wegha_db.php';

// 2. SECURITY AUTHENTICATION SHIELD CHECK
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(["error" => "Authentication required. Please log in to save items to your wishlist."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. CAPTURE RAW JSON INPUT BODY FROM REACT
$inputData = json_decode(file_get_contents("php://input"), true);
$package_id = isset($inputData['package_id']) ? intval($inputData['package_id']) : 0;

if ($package_id <= 0) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(["error" => "Invalid target package ID constraint input parameter."]);
    exit();
}

// 4. SECURELY CHECK TRANSACTION STATUS VIA PREPARED STATEMENTS
$check_stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND package_id = ?");
$check_stmt->bind_param("ii", $user_id, $package_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Branch A: Record exists -> Delete / Remove from Wishlist
    $delete_stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND package_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $package_id);

    if ($delete_stmt->execute()) {
        http_response_code(200); // OK
        echo json_encode([
            "status" => "removed",
            "message" => "Adventure removed from your wishlist successfully."
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Database operation execution failure during record removal."]);
    }
    $delete_stmt->close();
} else {
    // Branch B: Record does not exist -> Insert / Add to Wishlist
    $insert_stmt = $conn->prepare("INSERT INTO wishlist (user_id, package_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $package_id);

    if ($insert_stmt->execute()) {
        http_response_code(201); // 201 Created Resource
        echo json_encode([
            "status" => "added",
            "message" => "Adventure saved to your wishlist successfully!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database operation execution failure during record insertion."]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
exit();
