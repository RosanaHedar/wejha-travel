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
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Session expired or invalid. Please log in again."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. FETCH USER DATA PROFILE
$sql = "SELECT full_name, email, phone, image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_res = $stmt->get_result()->fetch_assoc();

if (!$user_res) {
    http_response_code(404);
    echo json_encode(["error" => "User account matching this session could not be located."]);
    exit();
}

$user_data = [
    "fullName" => $user_res['full_name'],
    "email"    => $user_res['email'],
    "phone"    => $user_res['phone'] ?? '',
    "img"      => !empty($user_res['image']) ? $user_res['image'] : 'uploads/avatar.png'
];
$stmt->close();

// 4. FETCH INDIVIDUAL BOOKINGS (WITH SUBQUERY COMPONENT AGGREGATORS)
$booking_query = "SELECT b.booking_id, b.booking_date, b.total_price, b.status, b.travel_date,
                         COALESCE(p.title, '🛠️ Custom Tailored Trip') as title, 
                         COALESCE(p.image_url, 'default.jpg') as image_url, 
                         p.package_id,
                         (SELECT GROUP_CONCAT(CONCAT(cps.quantity, 'x ', s.service_name) SEPARATOR '<br>') 
                          FROM customer_package_services cps 
                          JOIN services s ON cps.service_id = s.service_id 
                          WHERE cps.customer_package_id = b.custom_package_id) as custom_details
                  FROM bookings b 
                  LEFT JOIN packages p ON b.package_id = p.package_id 
                  WHERE b.user_id = ? 
                  ORDER BY b.booking_date DESC";

$stmt_book = $conn->prepare($booking_query);
$stmt_book->bind_param("i", $user_id);
$stmt_book->execute();
$booking_res = $stmt_book->get_result();

$bookings = [];
while ($b = $booking_res->fetch_assoc()) {
    $bookings[] = [
        "bookingId"      => intval($b['booking_id']),
        "title"          => $b['title'],
        "img"            => "assets/img/" . $b['image_url'],
        "packageId"      => !empty($b['package_id']) ? intval($b['package_id']) : null,
        "totalPrice"     => floatval($b['total_price']),
        "status"         => $b['status'],
        "orderDate"      => date('M d, Y', strtotime($b['booking_date'])),
        "travelDate"     => !empty($b['travel_date']) ? date('M d, Y', strtotime($b['travel_date'])) : null,
        "customDetails"  => $b['custom_details'] // Injected raw newline component details string
    ];
}
$stmt_book->close();

// 5. FETCH WISHLIST METRICS
$wish_query = "SELECT w.wishlist_id, p.package_id, p.title, p.image_url, p.price, p.discount_price 
               FROM wishlist w 
               JOIN packages p ON w.package_id = p.package_id 
               WHERE w.user_id = ?";

$stmt_wish = $conn->prepare($wish_query);
$stmt_wish->bind_param("i", $user_id);
$stmt_wish->execute();
$wish_res = $stmt_wish->get_result();

$wishlist = [];
while ($w = $wish_res->fetch_assoc()) {
    $has_discount = !empty($w['discount_price']) && $w['discount_price'] > 0;

    $wishlist[] = [
        "wishlistId"    => intval($w['wishlist_id']),
        "packageId"     => intval($w['package_id']),
        "title"         => $w['title'],
        "img"           => "assets/img/" . $w['image_url'],
        "price"         => floatval($w['price']),
        "discountPrice" => $has_discount ? floatval($w['discount_price']) : null,
        "hasDiscount"   => $has_discount
    ];
}
$stmt_wish->close();

// 6. COMPILE INTEGRATED DATA MATRIX RESPONSE
http_response_code(200);
echo json_encode([
    "user"     => $user_data,
    "bookings" => $bookings,
    "wishlist" => $wishlist
]);
exit();
