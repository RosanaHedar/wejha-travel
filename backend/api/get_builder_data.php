<?php
// 1. REST API & CROSS-ORIGIN CONFIGURATION HEADERS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

session_start();
include '../wegha_db.php';

// 2. SECURITY AUTHENTICATION CHECK
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required. Please log in to open the custom builder."]);
    exit();
}

// 3. FETCH ACTIVE DESTINATIONS
$dest_query = "SELECT destination_id, destination_name, image_url FROM destinations WHERE is_active = 1 ORDER BY destination_name ASC";
$dest_res = $conn->query($dest_query);
$destinations = [];
while ($row = $dest_res->fetch_assoc()) {
    $destinations[] = [
        "id"   => intval($row['destination_id']),
        "name" => $row['destination_name'],
        "img"  => "assets/img/" . ($row['image_url'] ?? 'default_dest.jpg')
    ];
}

// 4. FETCH ACTIVE SERVICE COMPONENTS
$srv_query = "SELECT s.service_id, s.destination_id, s.service_name, s.service_type, s.price, d.destination_name 
              FROM services s 
              JOIN destinations d ON s.destination_id = d.destination_id 
              WHERE s.is_active = 1 ORDER BY s.service_id DESC";
$srv_res = $conn->query($srv_query);
$services = [];
while ($row = $srv_res->fetch_assoc()) {
    $services[] = [
        "id"        => intval($row['service_id']),
        "destId"    => intval($row['destination_id']),
        "destName"  => $row['destination_name'],
        "name"      => $row['service_name'],
        "type"      => $row['service_type'], // 'Accommodation', 'Activity', 'Transport'
        "price"     => floatval($row['price'])
    ];
}

// 5. SHIP CONSOLIDATED DATA BASELINE TO REACT
http_response_code(200);
echo json_encode([
    "destinations" => $destinations,
    "services"     => $services
]);
exit();
