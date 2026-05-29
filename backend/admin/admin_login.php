<?php
session_start();
include '../wegha_db.php'; // Ensure this matches your DB connection filename

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Wegha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* --- WEGHA ADMIN CENTRALIZED STYLE MATRIX --- */
        :root {
            --primary-blue: #1e5494;
            --accent-orange: #f37021;
            --bg-slate: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg-slate);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 45px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(30, 84, 148, 0.05);
            width: 330px;
            border: 1px solid #edf2f7;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-header h2 {
            color: var(--primary-blue);
            margin: 0;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .brand-header p {
            color: var(--accent-orange);
            margin: 6px 0 0 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            outline: none;
            background: #fdfdfd;
            transition: all 0.2s ease;
            color: var(--text-main);
        }

        input:focus {
            border-color: var(--primary-blue);
            background: white;
            box-shadow: 0 0 0 3px rgba(30, 84, 148, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: background 0.2s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            background: #153d6b;
        }

        .error-box {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 24px;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="brand-header">
            <h2>Wegha Admin</h2>
            <p>Portal Gatekeeper</p>
        </div>

        <?php if ($error_message): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required autocomplete="username">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required autocomplete="current-password">
            </div>

            <button type="submit"><i class="fas fa-shield-alt"></i> Access Dashboard</button>
        </form>
    </div>

</body>

</html>