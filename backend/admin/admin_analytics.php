<?php
session_start();
include '../wegha_db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

/** * 1. DETAILED FINANCIAL KPI LOGIC
 * Separating revenue flows between static packages and custom builder itineraries.
 */

// Total Combined Revenue (All Confirmed Bookings)
$rev_res = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status = 'Confirmed'");
$total_revenue = $rev_res->fetch_assoc()['total'] ?? 0;

// Pre-built Packages Revenue (package_id is NOT NULL)
$prebuilt_rev_res = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status = 'Confirmed' AND package_id IS NOT NULL");
$prebuilt_revenue = $prebuilt_rev_res->fetch_assoc()['total'] ?? 0;

// Custom Builder Revenue (package_id IS NULL)
$custom_rev_res = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status = 'Confirmed' AND package_id IS NULL");
$custom_revenue = $custom_rev_res->fetch_assoc()['total'] ?? 0;

// Confirmed Bookings Count
$confirmed_res = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'Confirmed'");
$confirmed_bookings = $confirmed_res->fetch_assoc()['total'] ?? 0;

// Pending Action Approvals Count
$pending_res = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'Pending'");
$pending_count = $pending_res->fetch_assoc()['total'] ?? 0;

// Total Registered User Base
$user_res = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $user_res->fetch_assoc()['total'] ?? 0;


/** * 2. DETAILED BREAKDOWN PER PACKAGE
 * Uses SQL UNION ALL to cleanly append a row for user-built custom packages
 */
$package_stats = $conn->query("
    SELECT 
        p.package_id,
        p.title, 
        p.image_url, 
        p.price, 
        p.discount_price,
        COUNT(CASE WHEN b.status = 'Confirmed' THEN b.booking_id END) as confirmed_sales,
        SUM(CASE WHEN b.status = 'Confirmed' THEN b.total_price ELSE 0 END) as total_revenue
    FROM packages p
    LEFT JOIN bookings b ON p.package_id = b.package_id
    GROUP BY p.package_id
    
    UNION ALL
    
    SELECT 
        NULL as package_id,
        '🛠️ Custom Tailored Trips (User Built)' as title,
        'default.jpg' as image_url,
        0 as price,
        0 as discount_price,
        COUNT(CASE WHEN b.status = 'Confirmed' THEN b.booking_id END) as confirmed_sales,
        SUM(CASE WHEN b.status = 'Confirmed' THEN b.total_price ELSE 0 END) as total_revenue
    FROM bookings b
    WHERE b.package_id IS NULL
    
    ORDER BY total_revenue DESC
");


/** * 3. RECENT ACTIVITY LOG
 */
$recent_activity = $conn->query("
    SELECT b.booking_date, u.full_name, COALESCE(p.title, '🛠️ Custom Tailored Trip') as title, b.total_price, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN packages p ON b.package_id = p.package_id
    ORDER BY b.booking_date DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Analytics | Wegha Admin</title>
    <!-- FontAwesome integration for icons -->
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

        /* Forces this link to highlight as active inside administration routes context */
        .sidebar a[href="admin_analytics.php"] {
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

        /* --- CONTENT AREA --- */
        .main-content {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
            box-sizing: border-box;
            min-height: 100vh;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        h1 {
            color: var(--primary-blue);
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Export Button Styles */
        .btn-export {
            background: #10b981;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1);
        }

        .btn-export:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -1px rgba(16, 185, 129, 0.2);
        }

        /* --- FIXED AUTO-GRID KPI ROW --- */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .kpi-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            border-top: 4px solid var(--primary-blue);
        }

        .kpi-card h3 {
            margin: 0;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .kpi-card p {
            margin: 10px 0 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1;
        }

        /* Layout panels formatting structure sheets */
        .table-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            margin-bottom: 35px;
        }

        .table-card h2 {
            margin-top: 0;
            color: var(--primary-blue);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: #f8fafc;
            padding: 14px 16px;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #edf2f7;
            font-size: 0.95rem;
            color: var(--text-main);
            vertical-align: middle;
        }

        .thumb {
            width: 54px;
            height: 54px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #edf2f7;
            background: #f1f5f9;
        }

        /* Modern Status Badge Indicators */
        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .confirmed {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
        }

        .pending {
            background: #fffaf0;
            color: #c2410c;
            border: 1px solid #fbd38d;
        }

        .cancelled {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR CONTAINER MODULE -->
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

    <!-- MAIN INTERFACE MAIN BODY -->
    <div class="main-content">
        <div class="header-flex">
            <h1><i class="fas fa-chart-pie"></i> Financial Insights</h1>
            <a href="export_report.php" class="btn-export">
                <i class="fas fa-file-excel"></i> Export CSV Report
            </a>
        </div>

        <div class="kpi-row">
            <div class="kpi-card" style="border-top-color: #10b981;">
                <h3>Total Revenue</h3>
                <p><?php echo number_format($total_revenue); ?> EGP</p>
            </div>
            <div class="kpi-card" style="border-top-color: var(--primary-blue);">
                <h3>Pre-Built Revenue</h3>
                <p><?php echo number_format($prebuilt_revenue); ?> EGP</p>
            </div>
            <div class="kpi-card" style="border-top-color: var(--accent-orange);">
                <h3>Custom Builder Rev</h3>
                <p><?php echo number_format($custom_revenue); ?> EGP</p>
            </div>
            <div class="kpi-card" style="border-top-color: #3b82f6;">
                <h3>Finalized Sales</h3>
                <p><?php echo $confirmed_bookings; ?></p>
            </div>
            <div class="kpi-card" style="border-top-color: #8b5cf6;">
                <h3>Pending Approval</h3>
                <p><?php echo $pending_count; ?></p>
            </div>
            <div class="kpi-card" style="border-top-color: #64748b;">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
        </div>

        <div class="table-card">
            <h2>Revenue Breakdown per Package</h2>
            <table>
                <thead>
                    <tr>
                        <th>Trip</th>
                        <th>Trip Name</th>
                        <th>Current Price</th>
                        <th>Confirmed Sold</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pkg = $package_stats->fetch_assoc()):
                        $active_price = ($pkg['discount_price'] > 0) ? $pkg['discount_price'] : $pkg['price'];
                    ?>
                        <tr>
                            <!-- FIXED: Correct path adjustment for subfolder configuration mapping elements -->
                            <td><img src="../assets/img/<?php echo $pkg['image_url']; ?>" class="thumb" alt="trip"></td>
                            <td><strong><?php echo htmlspecialchars($pkg['title']); ?></strong></td>
                            <td style="color: #475569; font-weight: 500;"><?php echo ($pkg['package_id'] !== NULL) ? number_format($active_price) . " EGP" : "Variable Scale"; ?></td>
                            <td><span style="color: var(--primary-blue); font-weight: 700;"><?php echo $pkg['confirmed_sales']; ?></span> Confirmed</td>
                            <td style="font-weight: 700; color: #059669;">
                                <?php echo number_format($pkg['total_revenue']); ?> EGP
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h2>Recent Activity Log</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Trip</th>
                        <th>Total Paid</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_activity->fetch_assoc()): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;"><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td style="font-weight: 700; color: var(--primary-blue);"><?php echo number_format($row['total_price']); ?> EGP</td>
                            <td>
                                <span class="status-badge <?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>