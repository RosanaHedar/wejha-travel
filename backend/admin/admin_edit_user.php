<?php
session_start();
include '../wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Capture User ID safely from either GET (initial page load) or POST (form submission flow)
$user_id = 0;
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
} elseif (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
}

// Fallback error boundary guard
if ($user_id <= 0) {
    die("Invalid request: Missing or invalid user ID parameters.");
}

$error = "";

// 1. HANDLE THE POST UPDATE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['full_name']);
    $new_email = trim($_POST['email']);

    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
    $update_stmt->bind_param("ssi", $new_name, $new_email, $user_id);

    if ($update_stmt->execute()) {
        header("Location: admin_users.php?msg=updated");
        exit();
    } else {
        $error = "Error updating user metrics: " . $conn->error;
    }
}

// 2. ALWAYS FETCH CURRENT DATA STATE (Ensures $user is defined even on failed form submittals)
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Traveler record not found inside database.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Wegha Admin</title>
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
            margin: 0;
            display: flex;
        }

        /* --- UNIFIED SIDEBAR NAVIGATION --- */
        .sidebar {
            width: 260px;
            background: var(--primary-blue);
            color: white;
            height: 100vh;
            padding: 24px 16px;
            position: fixed;
            top: 0;
            left: 0;
            box-sizing: border-box;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            z-index: 999;
        }

        .sidebar h2 {
            text-align: center;
            color: var(--accent-orange);
            margin: 0 0 32px 0;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 16px;
            margin-bottom: 6px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Highlight rule maps edit user view under its contextual users parent domain link path selection */
        .sidebar a[href="admin_users.php"] {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid var(--accent-orange);
            font-weight: 600;
            padding-left: 12px;
        }

        .sidebar .logout {
            margin-top: auto;
            color: #f87171;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 16px;
        }

        .sidebar .logout:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* --- CONTENT WINDOW AREA --- */
        .main-content {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
            box-sizing: border-box;
            min-height: 100vh;
        }

        h1 {
            color: var(--primary-blue);
            margin: 0 0 8px 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .subtitle {
            color: var(--text-muted);
            margin: 0 0 35px 0;
            font-size: 0.95rem;
        }

        /* Unified Form Card Panel Layout */
        .form-card {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            max-width: 600px;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: 700;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            margin: 8px 0 0 0;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 0.95rem;
            color: var(--text-main);
            background-color: #fbfcfd;
            outline: none;
            transition: all 0.2s ease;
        }

        input:focus {
            border-color: var(--primary-blue);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(30, 84, 148, 0.1);
        }

        .btn-submit {
            background: var(--primary-blue);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            font-size: 1.05rem;
            margin-top: 25px;
            box-shadow: 0 4px 6px -1px rgba(30, 84, 148, 0.2);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: #153d6b;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -1px rgba(30, 84, 148, 0.3);
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .btn-cancel:hover {
            color: #ef4444;
            text-decoration: underline;
        }

        .alert-danger {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
            padding: 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Wegha Admin</h2>
        <a href="admin_analytics.php"><i class="fas fa-chart-pie"></i> Business Analytics</a>
        <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard Overview</a>
        <a href="admin_categories.php"><i class="fas fa-folder"></i> Manage Categories</a>
        <a href="admin_add_package.php"><i class="fas fa-plus-circle"></i> Add New Package</a>
        <a href="admin_build_package.php"><i class="fas fa-tools"></i> Setup Custom Options</a>
        <a href="admin_manage_packages.php"><i class="fas fa-boxes"></i> Manage Packages</a>
        <a href="admin_users.php"><i class="fas fa-users"></i> Registered Users</a>
        <a href="admin_bookings.php"><i class="fas fa-plane"></i> Manage Bookings</a>
        <a href="admin_support.php"><i class="fas fa-envelope"></i> Support Tickets</a>
        <a href="admin_reviews.php"><i class="fas fa-comments"></i> Manage Reviews</a>
        <a href="admin_logout.php" class="logout"><i class="fas fa-door-open"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1>Edit Traveler Profile</h1>
        <p class="subtitle">Modify account authentication strings or identity keys for traveler instance #<?php echo $user_id; ?>:</p>

        <?php if (!empty($error)): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form action="" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">

                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <button type="submit" class="btn-submit">Save Profiling Parameters</button>
                <a href="admin_users.php" class="btn-cancel">Discard Changes and Return</a>
            </form>
        </div>
    </div>

</body>

</html>