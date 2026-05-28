<form action="signup.php" method="POST">

    <input type="text" name="full_name" placeholder="Full Name" required>

    <input type="email" name="email" placeholder="Email" required>

    <input type="password" name="password" placeholder="Password" required>

    <button type="submit">Sign up</button>
</form>

<?php

include 'wegha_db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_full_name = $_POST['full_name'];
    $user_email = $_POST['email'];

    $hashed_pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("sss", $user_full_name, $user_email, $hashed_pass);

    if ($stmt->execute()) {
        echo "Registration successful! You can now check phpMyAdmin.";
    } else {
        echo "Execute failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>