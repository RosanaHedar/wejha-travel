<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

session_start();
include '../wegha_db.php';

// 2. SECURITY PROTECTION LAYER
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication token required. Please sign in to check out."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. FETCH USER INFORMATION FOR BILLING FORMS
$user_query = "SELECT full_name, IFNULL(card_number, '**** **** **** 3456') as mask_card FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

$checkout_items = [];
$total_payment = 0;

// 4. ROUTE CHECK: DETECT CUSTOM ITINERARY BLUEPRINTS VS STANDARD BUNDLES
$is_custom = (isset($_GET['type']) && $_GET['type'] === 'custom' && isset($_GET['id']));
$custom_id = $is_custom ? intval($_GET['id']) : 0;

if ($is_custom) {
    // Branch A: Custom Builder Database Calculations
    $pkg_res = $conn->query("SELECT total_price FROM customer_packages WHERE customer_package_id = $custom_id AND user_id = '$user_id'");

    if ($pkg = $pkg_res->fetch_assoc()) {
        $total_payment = floatval($pkg['total_price']);

        // Pull selected components to build a itemized breakdown array for React
        $items_res = $conn->query("
            SELECT s.service_name, cps.quantity 
            FROM customer_package_services cps
            JOIN services s ON cps.service_id = s.service_id
            WHERE cps.customer_package_id = $custom_id
        ");

        $breakdown = [];
        while ($item = $items_res->fetch_assoc()) {
            $breakdown[] = $item['quantity'] . "x " . $item['service_name'];
        }

        $checkout_items[] = [
            "name"        => "Custom Tailored Adventure",
            "img"         => "assets/img/default.jpg",
            "description" => !empty($breakdown) ? implode(", ", $breakdown) : "Custom itinerary configuration",
            "price"       => $total_payment,
            "savings"     => 0
        ];
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Custom package records could not be resolved."]);
        exit();
    }
} else {
    // Branch B: Standard Cache Evaluation via Sessions
    if (isset($_SESSION['last_booking'])) {
        $b = $_SESSION['last_booking'];

        $p_unit = isset($b['price_unit']) ? floatval($b['price_unit']) : floatval($b['total']);
        $o_unit = isset($b['original_unit']) ? floatval($b['original_unit']) : $p_unit;
        $perc_saved = ($o_unit > $p_unit) ? round((($o_unit - $p_unit) / $o_unit) * 100) : 0;
        $total_payment = floatval($b['total']);

        $checkout_items[] = [
            "name"        => $b['name'],
            "img"         => "assets/img/" . $b['image'],
            "description" => "Date: " . ($b['date'] ?? 'TBD') . " | Travelers: " . ($b['travelers'] ?? 1),
            "price"       => $total_payment,
            "savings"     => intval($perc_saved)
        ];
    } else {
        http_response_code(400);
        echo json_encode(["error" => "No active package bookings cached inside execution history logs."]);
        exit();
    }
}

// 5. EMIT COMBINED ORDER SCHEMAS
http_response_code(200);
echo json_encode([
    "cardholderName" => $user_data['full_name'] ?? 'Guest',
    "maskedCard"     => $user_data['mask_card'],
    "isCustomPath"   => $is_custom,
    "customPackageId" => $custom_id > 0 ? $custom_id : null,
    "totalPayment"   => $total_payment,
    "lineItems"      => $checkout_items
]);
exit();
