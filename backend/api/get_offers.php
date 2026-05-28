<?php
// 1. MANDATORY CORS & REST API DATA HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

session_start();
include '../wegha_db.php';

// 2. UNIFIED UNION QUERY LOGIC
$query = "
    (SELECT 
        package_id, title, description, image_url, 
        original_price, new_price, category, 'special' as offer_type 
     FROM offers 
     WHERE is_active = 1)
    UNION
    (SELECT 
        p.package_id, p.title, p.short_desc as description, 
        CONCAT('assets/img/', p.image_url) as image_url, 
        p.price as original_price, p.discount_price as new_price, 
        IFNULL(c.name, 'Trip') as category, 'discount' as offer_type 
     FROM packages p 
     LEFT JOIN categories c ON p.category_id = c.category_id 
     WHERE p.discount_price IS NOT NULL AND p.discount_price > 0 AND p.is_active = 1)
    ORDER BY new_price ASC";

$result = mysqli_query($conn, $query);
$offers_list = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Compute structural pricing properties server-side
        $original_price = floatval($row['original_price']);
        $new_price = floatval($row['new_price']);
        $discount_percentage = 0;

        if ($original_price > 0) {
            $discount_percentage = round((($original_price - $new_price) / $original_price) * 100);
        }

        // Map cleaner structured variable keys matching frontend camelCase conventions
        $offers_list[] = [
            "packageId"          => intval($row['package_id']),
            "title"              => $row['title'],
            "description"        => $row['description'],
            "img"                => $row['image_url'],
            "originalPrice"      => $original_price,
            "newPrice"           => $new_price,
            "category"           => $row['category'],
            "offerType"          => $row['offer_type'], // Separates standalone offers from markdown items
            "discountPercentage" => intval($discount_percentage)
        ];
    }

    http_response_code(200);
    echo json_encode($offers_list);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Database operations grouping failed: " . mysqli_error($conn)]);
}
exit();
