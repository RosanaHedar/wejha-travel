<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

session_start();
include '../wegha_db.php';

// 2. CAPTURE THE PACKAGES IDENTIFIER INBOUND PARAMETER
$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($package_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Invalid or missing trip package ID parameter."]);
    exit();
}

// 3. FETCH PACKAGES DATA RECORD JOINED WITH CATEGORIES
$sql = "SELECT p.*, c.name AS category_name 
        FROM packages p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.package_id = $package_id";

$result = $conn->query($sql);
$package = $result->fetch_assoc();

if (!$package) {
    http_response_code(404); // Not Found
    echo json_encode(["error" => "The requested tourism package was not found inside database records."]);
    exit();
}

// 4. PRICING & DISCOUNTS MATRIX CALCULATIONS
$has_discount = !empty($package['discount_price']) && $package['discount_price'] > 0;
$current_price = $has_discount ? floatval($package['discount_price']) : floatval($package['price']);
$discount_percentage = 0;

if ($has_discount && $package['price'] > 0) {
    $discount_percentage = round((($package['price'] - $package['discount_price']) / $package['price']) * 100);
}

// 5. FETCH APPROVED FRONTEND REVIEWS FOR THIS TRIP
$reviews_sql = "SELECT r.review_id, r.rating, r.comment, r.created_at, u.full_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.package_id = $package_id AND r.status = 'Approved' 
                ORDER BY r.created_at DESC";
$reviews_result = $conn->query($reviews_sql);

$reviews_list = [];
while ($rev = $reviews_result->fetch_assoc()) {
    $reviews_list[] = [
        "reviewId"   => intval($rev['review_id']),
        "userName"   => $rev['full_name'],
        "rating"     => intval($rev['rating']),
        "comment"    => $rev['comment'],
        "date"       => date('M d, Y', strtotime($rev['created_at']))
    ];
}

// 6. MAP DYNAMIC JSON ARRAYS TO PREVENT MAP LOOPS FAILURES IN REACT
$itinerary_array = !empty($package['long_itinerary']) ? explode("\n", str_replace("\r", "", $package['long_itinerary'])) : [];
$includes_array  = !empty($package['inclusions_list']) ? explode(",", $package['inclusions_list']) : [];

// 7. ASSEMBLE PURE UNIFIED OBJECT FEED
$response_data = [
    "id"                 => intval($package['package_id']),
    "title"              => $package['title'],
    "category"           => $package['category_name'],
    "img"                => "assets/img/" . $package['image_url'],
    "longDescription"    => $package['long_desc'],
    "basePrice"          => floatval($package['price']),
    "discountPrice"      => $has_discount ? floatval($package['discount_price']) : null,
    "currentPrice"       => $current_price,
    "discountPercentage" => $discount_percentage,
    "hasDiscount"        => $has_discount,
    "days"               => $package['duration_label'] ?? '3 Days',
    "group"              => $package['group_size_label'] ?? 'Free Group',
    "loc"                => $package['location_label'] ?? 'Egypt',
    "itinerary"          => $itinerary_array,
    "includes"           => $includes_array,
    "reviews"            => $reviews_list // Embedded list array payload
];

http_response_code(200); // OK
echo json_encode($response_data);
exit();
