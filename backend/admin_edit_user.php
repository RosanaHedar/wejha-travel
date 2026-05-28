<?php
session_start();
include 'wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. Fetch the user's current info based on the ID in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }
}

// 2. Handle the Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = $_POST['full_name'];
    $new_email = $_POST['email'];
    $user_id = $_POST['user_id'];

    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
    $update_stmt->bind_param("ssi", $new_name, $new_email, $user_id);

    if ($update_stmt->execute()) {
        header("Location: admin_users.php?msg=updated");
        exit();
    } else {
        $error = "Error updating user: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User | Wegha Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .edit-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            color: #1e5494;
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 15px;
            color: #666;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-save {
            background: #1e5494;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            font-weight: bold;
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #999;
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="edit-card">
        <h2>Edit Traveler Profile</h2>
        <form action="admin_edit_user.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">

            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <button type="submit" class="btn-save">Save Changes</button>
            <a href="admin_users.php" class="btn-cancel">Cancel and Go Back</a>
        </form>
    </div>

</body>

</html>