<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

session_start();
include '../wegha_db.php';

// 2. CAPTURE DYNAMIC INCOMING FILTERS ASYNC PARAMS
$category_filter = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$keyword         = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// 3. IDENTIFY AUTHENTICATED USER WISHLIST HOOKS
$user_id = $_SESSION['user_id'] ?? 0;
$user_wishlist = [];
if ($user_id > 0) {
    $wish_sql = "SELECT package_id FROM wishlist WHERE user_id = $user_id";
    $wish_res = $conn->query($wish_sql);
    if ($wish_res) {
        while ($w = $wish_res->fetch_assoc()) {
            $user_wishlist[] = intval($w['package_id']);
        }
    }
}

// 4. FETCH AVAILABLE CATEGORIES MATRIX
$cat_sql = "SELECT category_id, name FROM categories ORDER BY name ASC";
$cat_result = $conn->query($cat_sql);
$categories = [];
while ($cat = $cat_result->fetch_assoc()) {
    $categories[] = [
        "id"   => intval($cat['category_id']),
        "name" => $cat['name']
    ];
}

// 5. COMPILING MASTER PACKAGES QUERY FLOW
$sql = "SELECT p.*, IFNULL(c.name, 'General') AS category_name 
        FROM packages p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_active = 1";

// Append criteria constraints dynamically safely
$params = [];
$types = "";

if ($category_filter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if ($keyword !== '') {
    $sql .= " AND (p.title LIKE ? OR p.short_desc LIKE ?)";
    $search_term = "%" . $keyword . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

$sql .= " ORDER BY p.package_id DESC";

// Execute secure prepared parameters query matrix
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$pkg_result = $stmt->get_result();

$packages = [];
while ($row = $pkg_result->fetch_assoc()) {
    $pkg_id = intval($row['package_id']);

    // Evaluate if price breaks down into active markdown state
    $has_discount = !empty($row['discount_price']) && $row['discount_price'] > 0;

    $packages[] = [
        "id"            => $pkg_id,
        "title"         => $row['title'],
        "category"      => $row['category_name'],
        "categoryId"    => intval($row['category_id']),
        "durationDays"  => intval($row['duration_days'] ?? 3),
        "shortDesc"     => $row['short_desc'],
        "img"           => "assets/img/" . (!empty($row['image_url']) ? $row['image_url'] : 'placeholder.jpg'),
        "price"         => floatval($row['price']),
        "discountPrice" => $has_discount ? floatval($row['discount_price']) : null,
        "hasDiscount"   => $has_discount,
        // Compares directly with user wishlist context to pass boolean heart tags
        "isLiked"       => in_array($pkg_id, $user_wishlist)
    ];
}

// 6. RESPOND AS UNIFIED OBJECT RECORD FEED
http_response_code(200);
echo json_encode([
    "categories" => $categories,
    "packages"   => $packages
]);
exit();
