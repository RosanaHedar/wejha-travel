<?php
session_start();
include 'wegha_db.php';

// 1. Security Check: Are they logged in?
if (!isset($_SESSION['user_id'])) {
    // If not logged in, send them to login with a message
    header("Location: login.php?error=please_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 2. Collect Data
    $user_id = $_SESSION['user_id'];
    $package_id = $_POST['package_id'];
    $travel_date = $_POST['travel_date'];
    $num_travelers = $_POST['num_travelers'];
    $price_per_person = $_POST['price'];

    // 3. Calculate Total
    $total_price = $price_per_person * $num_travelers;

    // 4. Insert into Database
    $sql = "INSERT INTO bookings (user_id, package_id, travel_date, num_travelers, total_price, status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisid", $user_id, $package_id, $travel_date, $num_travelers, $total_price);

    if ($stmt->execute()) {
        // SUCCESS: Send to a "My Bookings" page or Success page
        header("Location: my_bookings.php?status=success");
    } else {
        echo "Error: " . $conn->error;
    }
}
