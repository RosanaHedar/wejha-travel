<?php
include 'wegha_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']); // NEW
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if (!empty($name) && !empty($email) && !empty($phone) && !empty($message)) {
        // Updated SQL to include phone_number
        $sql = "INSERT INTO contact_messages (name, email, phone, message, status) 
                VALUES ('$name', '$email', '$phone', '$message', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo "error";
        }
    }
}
