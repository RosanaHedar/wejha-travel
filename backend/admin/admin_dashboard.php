<?php
session_start();
include '../wegha_db.php'; // Correct relative folder path to find db connection up one level

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Wegha</title>

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

        /* Highlight rule for the active dashboard page link */
        .sidebar a[href="admin_dashboard.php"] {
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
            /* Shifts your content to clear the sidebar perfectly */
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

        /* Responsive Metric Card Grid Layout */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-top: 25px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-blue);
        }

        .stat-info h3 {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .stat-info p {
            margin: 8px 0 0 0;
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1;
        }

        .stat-icon {
            font-size: 2rem;
            color: rgba(30, 84, 148, 0.12);
        }

        /* Specific card edge colors */
        .card-live::before {
            background: #10b981;
        }

        .card-live .stat-icon {
            color: rgba(16, 185, 129, 0.12);
        }

        .card-hidden::before {
            background: #e67e22;
        }

        .card-hidden .stat-icon {
            color: rgba(230, 126, 34, 0.12);
        }

        .card-custom::before {
            background: var(--accent-orange);
        }

        .card-custom .stat-icon {
            color: rgba(243, 112, 33, 0.12);
        }

        .action-box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            margin-top: 35px;
        }

        .action-box h3 {
            color: var(--primary-blue);
            margin: 0 0 12px 0;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-box p {
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
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
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!</h1>
        <p class="subtitle">Here is an overview of the Wegha platform performance metrics:</p>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Travelers</h3>
                    <p><?php echo number_format($user_count); ?></p>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>

            <div class="stat-card card-live">
                <div class="stat-info">
                    <h3>Live Bundles</h3>
                    <p><?php echo number_format($active_packages); ?></p>
                </div>
                <div class="stat-icon"><i class="fas fa-eye"></i></div>
            </div>

            <div class="stat-card card-hidden">
                <div class="stat-info">
                    <h3>Hidden / Drafts</h3>
                    <p><?php echo number_format($hidden_packages); ?></p>
                </div>
                <div class="stat-icon"><i class="fas fa-eye-slash"></i></div>
            </div>

            <div class="stat-card card-custom">
                <div class="stat-info">
                    <h3>Custom Options</h3>
                    <p><?php echo number_format($custom_services_count); ?></p>
                </div>
                <div class="stat-icon"><i class="fas fa-sliders-h"></i></div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Bookings</h3>
                    <p><?php echo number_format($booking_count); ?></p>
                </div>
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
            </div>
        </div>

        <div class="action-box">
            <h3><i class="fas fa-bolt" style="color: var(--accent-orange);"></i> Quick Actions Center</h3>
            <p>
                Ready to update the marketplace? Use the navigation sidebar on the left to review incoming traveler requests, manage tour category tags, or add inventory options inside the custom itinerary planner utility.
            </p>
        </div>
    </div>

</body>

</html>