<?php
session_start();
include 'wegha_db.php';

// 1. Security Check: Are they logged in?
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=please_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $package_id = $_POST['package_id'];
    $travel_date = $_POST['travel_date'];
    $num_travelers = $_POST['num_travelers'];
    $price_per_person = $_POST['price'];

    $total_price = $price_per_person * $num_travelers;

    // 4. Insert into Database
    $sql = "INSERT INTO bookings (user_id, package_id, travel_date, num_travelers, total_price, status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisid", $user_id, $package_id, $travel_date, $num_travelers, $total_price);

    if ($stmt->execute()) {
        // SUCCESS: Hand off to your teammate's payment page
        header("Location: payment.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
