<?php
// Turn on error reporting so we can see any issues
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'wegha_db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=please_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $package_id = intval($_POST['package_id']);

    // Capture inputs from the form
    $package_name = isset($_POST['package_name']) ? $_POST['package_name'] : 'Selected Package';
    $image_url    = isset($_POST['image_url']) ? $_POST['image_url'] : 'default.jpg';
    $travel_date  = $_POST['travel_date'];
    $num_travelers = intval($_POST['num_travelers']);

    // Logic for pricing
    $price_per_person = floatval($_POST['price']);
    $original_price   = isset($_POST['original_price']) ? floatval($_POST['original_price']) : $price_per_person;

    $total_price = $price_per_person * $num_travelers;

    // Save to Database
    $sql = "INSERT INTO bookings (user_id, package_id, travel_date, num_travelers, total_price, status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iisid", $user_id, $package_id, $travel_date, $num_travelers, $total_price);

        if ($stmt->execute()) {
            // STORE IN SESSION (Crucial for payment.php)
            $_SESSION['last_booking'] = [
                'name'          => $package_name,
                'image'         => $image_url,
                'price_unit'    => $price_per_person,
                'original_unit' => $original_price,
                'travelers'     => $num_travelers,
                'total'         => $total_price,
                'date'          => $travel_date
            ];

            header("Location: payment.php");
            exit();
        } else {
            die("Database Error: " . $stmt->error);
        }
    } else {
        die("Preparation Error: " . $conn->error);
    }
}
