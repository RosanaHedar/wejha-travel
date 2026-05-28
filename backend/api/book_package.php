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

// 2. AUTHENTICATION SHIELD JURY CHECK
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Authentication required. Please log in to book this trip."]);
    exit();
}

// 3. CAPTURE RAW JSON INPUT STREAM FROM REACT
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    $user_id       = $_SESSION['user_id'];
    $package_id    = intval($inputData['package_id'] ?? 0);
    $package_name  = mysqli_real_escape_string($conn, trim($inputData['package_name'] ?? 'Selected Package'));
    $image_url     = mysqli_real_escape_string($conn, trim($inputData['image_url'] ?? 'default.jpg'));
    $travel_date   = mysqli_real_escape_string($conn, trim($inputData['travel_date'] ?? ''));
    $num_travelers = intval($inputData['num_travelers'] ?? 1);

    // Server-Side Pricing Arithmetic Calculations
    $price_per_person = floatval($inputData['price'] ?? 0);
    $original_price   = isset($inputData['original_price']) ? floatval($inputData['original_price']) : $price_per_person;

    $total_price = $price_per_person * $num_travelers;

    // Validate date input boundaries
    if (empty($travel_date) || $package_id <= 0 || $price_per_person <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Invalid itinerary order parameters. Please check your travel details."]);
        exit();
    }

    // 4. PREPARED STATEMENT INSERTION
    $sql = "INSERT INTO bookings (user_id, package_id, travel_date, num_travelers, total_price, status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iisid", $user_id, $package_id, $travel_date, $num_travelers, $total_price);

        if ($stmt->execute()) {

            // 5. CACHE STATE IN SESSION FOR payment.php COMPATIBILITY
            $_SESSION['last_booking'] = [
                'name'          => $package_name,
                'image'         => $image_url,
                'price_unit'    => $price_per_person,
                'original_unit' => $original_price,
                'travelers'     => $num_travelers,
                'total'         => $total_price,
                'date'          => $travel_date
            ];

            http_response_code(201); // Created
            echo json_encode([
                "message" => "Booking registered successfully! Redirecting to payment hub...",
                "total_price" => $total_price
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database processing transaction execution failure: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(["error" => "SQL configuration preparation failure: " . $conn->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty booking form payload received."]);
}
$conn->close();
exit();
