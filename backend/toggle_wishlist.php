<?php
session_start();
include 'wegha_db.php';

if (isset($_SESSION['user_id']) && isset($_GET['package_id'])) {
    $user_id = $_SESSION['user_id'];
    $package_id = intval($_GET['package_id']);

    // Check if it's already in the wishlist
    $check = $conn->query("SELECT * FROM wishlist WHERE user_id = $user_id AND package_id = $package_id");

    if ($check->num_rows > 0) {
        // Remove it
        $conn->query("DELETE FROM wishlist WHERE user_id = $user_id AND package_id = $package_id");
    } else {
        // Add it
        $conn->query("INSERT INTO wishlist (user_id, package_id) VALUES ($user_id, $package_id)");
    }
}
// Go back to the page the user was on
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
