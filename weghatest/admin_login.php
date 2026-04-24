<?php
session_start();
include 'wegha_db.php'; // Ensure this matches your DB connection filename

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    // 1. Prepare the query to find the admin by username
    $stmt = $conn->prepare("SELECT admin_id, username, password_hash FROM admins WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        // 2. Check the password
        // Note: If you haven't hashed your password in the DB yet, use ($pass == $admin['password_hash'])
        // But password_verify() is the professional standard.
        if ($pass == $admin['password_hash']) {

            // 3. Success! Set the Admin Session
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['is_admin'] = true;

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "Admin username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login | Wegha</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        h2 {
            color: #1e5494;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #1e5494;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .error {
            color: red;
            font-size: 0.8rem;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h2>Admin Portal</h2>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Access Dashboard</button>
        </form>
    </div>

</body>

</html>