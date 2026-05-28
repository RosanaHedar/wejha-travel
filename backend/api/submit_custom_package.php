<?php
// 1. REST API & CROSS-ORIGIN CONFIGURATION HEADERS
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

// 2. SECURITY AUTHENTICATION CHECK
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Session expired. Please log in to complete your transaction."]);
    exit();
}

// 3. EXTRACT INPUT JSON FROM REACT
$inputData = json_decode(file_get_contents("php://input"), true);

if (!empty($inputData)) {
    $user_id        = $_SESSION['user_id'];
    $calculated_tot = floatval($inputData['calculated_total'] ?? 0);
    $selected_items = $inputData['selected_services'] ?? []; // Array containing elements with: id, qty, date

    if (empty($selected_items) || $calculated_tot <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Malformed input query: Itinerary builder item array cannot be empty."]);
        exit();
    }

    // 4. TRANSACTION BLOCK: INSERT MASTER RECORD
    $total_price_clean = mysqli_real_escape_string($conn, $calculated_tot);
    $insert_package = "INSERT INTO customer_packages (user_id, total_price) VALUES ('$user_id', '$total_price_clean')";

    if (mysqli_query($conn, $insert_package)) {
        $customer_package_id = mysqli_insert_id($conn);

        // 5. INSERT COMPONENT SEGMENTS INTO JUNCTION TABLE
        $stmt = mysqli_prepare($conn, "INSERT INTO customer_package_services (customer_package_id, service_id, quantity, service_date) VALUES (?, ?, ?, ?)");

        if ($stmt) {
            foreach ($selected_items as $item) {
                $service_id   = intval($item['id']);
                $quantity     = intval($item['qty']);
                // Capture the custom user date string per specific choice item
                $service_date = !empty($item['date']) ? trim($item['date']) : null;

                mysqli_stmt_bind_param($stmt, "iiis", $customer_package_id, $service_id, $quantity, $service_date);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);

            // 6. ALLOCATE CACHE TO BIND WITH PAYMENT COMPONENT
            $_SESSION['active_custom_package'] = $customer_package_id;

            http_response_code(201); // Created Resource
            echo json_encode([
                "message" => "Custom travel blueprint compiled and logged successfully.",
                "custom_package_id" => $customer_package_id
            ]);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Junction table SQL query compilation failure."]);
            exit();
        }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database master processing entry failure: " . mysqli_error($conn)]);
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Empty custom configuration payload object received."]);
}
$conn->close();
exit();
