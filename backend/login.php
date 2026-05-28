<form action="login.php" method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Log in</button>
</form>

<?php
session_start();
include 'wegha_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // FIXED: Selected the 'is_active' data field flag to audit account states
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($user = $result->fetch_assoc()) {
        // Verify the password hash
        if (password_verify($password, $user['password_hash'])) {

            // FIXED: Intercept login authorization if the administrator marked this row as suspended
            if (intval($user['is_active']) === 0) {
                echo "<p style='color:#ff4d4d; font-weight:bold; font-family:sans-serif;'>Access Restricted: Your account has been suspended by administration.</p>";
            } else {
                // Account is verified and active -> proceed to system allocation
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];

                header("Location: index.php");
                exit();
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No account found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>