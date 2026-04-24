<?php
session_start();
include 'wegha_db.php';

// --- THE ADMIN SHIELD ---
// This ensures only logged-in admins can see this page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. Fetch Stats from the Database
// Total registered travelers
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// Packages currently visible to users
$active_packages = $conn->query("SELECT COUNT(*) as total FROM packages WHERE is_active = 1")->fetch_assoc()['total'];

// Packages marked as 'Hidden' or 'Coming Soon'
$hidden_packages = $conn->query("SELECT COUNT(*) as total FROM packages WHERE is_active = 0")->fetch_assoc()['total'];

// Total booking transactions recorded
$booking_count = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Wegha</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            background: #f4f7f6;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #1e5494;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .logout {
            color: #ff6b6b !important;
            margin-top: 50px;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
        }

        /* Grid updated to 4 columns to fit the new status breakdown */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-card h3 {
            margin: 0;
            color: #666;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card p {
            margin: 10px 0 0;
            font-size: 2.5rem;
            font-weight: bold;
            color: #1e5494;
        }

        /* Status Colors */
        .count-active {
            color: #2ecc71 !important;
        }

        .count-hidden {
            color: #e67e22 !important;
        }
    </style>
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h1>
        <p>Here is an overview of the Wegha platform performance:</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Travelers</h3>
                <p><?php echo $user_count; ?></p>
            </div>

            <div class="stat-card">
                <h3>Live Packages</h3>
                <p class="count-active"><?php echo $active_packages; ?></p>
            </div>

            <div class="stat-card">
                <h3>Hidden / Drafts</h3>
                <p class="count-hidden"><?php echo $hidden_packages; ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?php echo $booking_count; ?></p>
            </div>
        </div>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid #ddd;">

        <h3>Quick Actions</h3>
        <p>Ready to update the marketplace? Use the sidebar to manage your content or view user reports.</p>
    </div>

</body>

</html>