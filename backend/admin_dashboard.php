<?php
session_start();
include 'wegha_db.php';

// --- THE ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. Fetch Stats from the Database
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$active_packages = $conn->query("SELECT COUNT(*) as total FROM packages WHERE is_active = 1")->fetch_assoc()['total'];
$hidden_packages = $conn->query("SELECT COUNT(*) as total FROM packages WHERE is_active = 0")->fetch_assoc()['total'];
$booking_count = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];

// New tracker for custom items pool
$custom_services_count = $conn->query("SELECT COUNT(*) as total FROM services")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Wegha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
        }

        /* --- STYLED SIDEBAR OVERRIDE COMPONENT --- */
        .sidebar {
            width: 250px !important;
            background: #1e5494 !important;
            color: white !important;
            height: 100vh !important;
            padding: 20px !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            box-sizing: border-box !important;
            z-index: 9999 !important;
        }

        .sidebar h2 {
            text-align: center !important;
            color: #f37021 !important;
            margin-bottom: 30px !important;
            margin-top: 10px !important;
        }

        .sidebar a {
            display: block !important;
            color: white !important;
            text-decoration: none !important;
            padding: 12px !important;
            margin-bottom: 5px !important;
            border-radius: 5px !important;
            font-size: 0.9rem !important;
            transition: 0.2s ease !important;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        /* Forces this specific link to highlight orange on the landing dashboard view */
        .sidebar a[href="admin_dashboard.php"] {
            background: rgba(255, 255, 255, 0.2) !important;
            border-left: 4px solid #f37021 !important;
            padding-left: 8px !important;
        }

        .sidebar .logout {
            margin-top: 30px !important;
            color: #ff6b6b !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding-top: 20px !important;
        }

        /* --- MAIN CONTENT WINDOW AREA --- */
        .main-content {
            margin-left: 270px;
            /* Safe clearing block gutter from the sidebar anchor panel */
            padding: 40px;
            width: calc(100% - 270px);
            box-sizing: border-box;
        }

        h1 {
            color: #1e5494;
            margin-top: 0;
        }

        /* Responsive Metric Card Auto-Matrix Setup */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            text-align: center;
            border-top: 4px solid #1e5494;
        }

        .stat-card h3 {
            margin: 0;
            color: #888;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card p {
            margin: 12px 0 0;
            font-size: 2.2rem;
            font-weight: bold;
            color: #1e5494;
        }

        .count-active {
            color: #2ecc71 !important;
        }

        .count-hidden {
            color: #e67e22 !important;
        }

        .count-custom {
            color: #f37021 !important;
        }

        .action-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!</h1>
        <p style="color: #666; margin-bottom: 30px;">Here is an overview of the Wegha platform performance metrics:</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Travelers</h3>
                <p><i class="fas fa-users" style="font-size: 1.2rem; margin-right: 5px; color: #888;"></i> <?php echo $user_count; ?></p>
            </div>

            <div class="stat-card" style="border-top-color: #2ecc71;">
                <h3>Live Bundles</h3>
                <p class="count-active"><?php echo $active_packages; ?></p>
            </div>

            <div class="stat-card" style="border-top-color: #e67e22;">
                <h3>Hidden / Drafts</h3>
                <p class="count-hidden"><?php echo $hidden_packages; ?></p>
            </div>

            <div class="stat-card" style="border-top-color: #f37021;">
                <h3>Custom Options</h3>
                <p class="count-custom"><i class="fas fa-sliders-h" style="font-size: 1.2rem;"></i> <?php echo $custom_services_count; ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?php echo $booking_count; ?></p>
            </div>
        </div>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid #ddd;">

        <div class="action-box">
            <h3 style="color: #1e5494; margin-top: 0;"><i class="fas fa-bolt"></i> Quick Actions Center</h3>
            <p style="color: #555; font-size: 0.95rem; line-height: 1.5;">
                Ready to update the marketplace? Use the navigation sidebar on the left to review incoming traveler requests, manage tour category tags, or add inventory options inside the custom itinerary planner utility.
            </p>
        </div>
    </div>

</body>

</html>