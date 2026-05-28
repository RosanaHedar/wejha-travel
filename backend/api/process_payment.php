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

session_start();
include '../wegha_db.php';

// 2. SECURITY PROTECTION LAYER
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Session expired or context missing. Please re-authenticate."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. INTERCEPT INCOMING POST BODY STREAM
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    $method       = mysqli_real_escape_string($conn, trim($inputData['payment_method'] ?? 'cash'));
    $total_amount = floatval($inputData['total_amount'] ?? 0);
    $is_custom    = isset($inputData['is_custom']) && $inputData['is_custom'] == true;
    $custom_id    = intval($inputData['custom_package_id'] ?? 0);
    $cardholder   = mysqli_real_escape_string($conn, trim($inputData['cardholder_name'] ?? 'Guest'));

    if ($total_amount <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid arithmetic constraint values: Total cost cannot be 0."]);
        exit();
    }

    // 4. WRITE RECORD TRANSACTION INTO THE PAYMENTS MATRIX
    $payment_sql = "INSERT INTO payments (user_id, card_name, amount, payment_method) 
                    VALUES ('$user_id', '$cardholder', '$total_amount', '$method')";

    if (mysqli_query($conn, $payment_sql)) {

        // 5. EXTENSION: INJECT DYNAMIC CUSTOM ROWS INTO BOOKINGS QUEUES
        if ($is_custom && $custom_id > 0) {
            $booking_sql = "INSERT INTO bookings (user_id, package_id, custom_package_id, total_price, status, booking_date) 
                            VALUES ('$user_id', NULL, '$custom_id', '$total_amount', 'Pending', NOW())";
            mysqli_query($conn, $booking_sql);
        }

        // Clear historical booking caches upon order completion
        unset($_SESSION['last_booking']);
        unset($_SESSION['active_custom_package']);

        http_response_code(201); // Created
        echo json_encode(["message" => "Transaction settled successfully. Pack your bags, your trip is pending confirmation!"]);
        exit();
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database operation transactional logging failure: " . mysqli_error($conn)]);
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty checkout payment confirmation block received."]);
}
$conn->close();
exit();
