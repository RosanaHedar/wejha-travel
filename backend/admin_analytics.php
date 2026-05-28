<?php
session_start();
include 'wegha_db.php';

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
    <title>Business Analytics | Wegha Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
        }

        /* --- SIDEBAR STYLE --- */
        .sidebar {
            width: 250px;
            background: #1e5494;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            box-sizing: border-box;
        }

        .sidebar h2 {
            text-align: center;
            color: #f37021;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            margin-bottom: 5px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #f37021;
        }

        .sidebar .logout {
            margin-top: 30px;
            color: #ff6b6b;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        /* --- CONTENT AREA --- */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: 100%;
            box-sizing: border-box;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 {
            color: #1e5494;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Export Button */
        .btn-export {
            background: #2ecc71;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-export:hover {
            background: #27ae60;
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }

        /* --- FIXED AUTO-GRID KPI ROW --- */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-top: 5px solid #1e5494;
        }

        .kpi-card h3 {
            margin: 0;
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-card p {
            margin: 10px 0 0;
            font-size: 1.35rem;
            font-weight: bold;
            color: #1e5494;
        }

        /* Data Cards */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .table-card h2 {
            margin-top: 0;
            color: #1e5494;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: #f8f9fa;
            padding: 12px;
            color: #666;
            font-size: 0.85rem;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .thumb {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .confirmed {
            background: #d4edda;
            color: #155724;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Wegha Admin</h2>
        <a href="admin_analytics.php" class="active">📊 Business Analytics</a>
        <a href="admin_dashboard.php">🏠 Dashboard Overview</a>
        <a href="admin_categories.php">📂 Manage Categories</a>
        <a href="admin_add_package.php">➕ Add New Package</a>
        <a href="admin_manage_packages.php">📦 Manage Packages</a>
        <a href="admin_users.php">👥 Registered Users</a>
        <a href="admin_bookings.php">✈️ Manage Bookings</a>
        <a href="admin_support.php">📩 Support Tickets</a>
        <a href="admin_logout.php" class="logout">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="header-flex">
            <h1><i class="fas fa-chart-pie"></i> Financial Insights</h1>
            <a href="export_report.php" class="btn-export">
                <i class="fas fa-file-excel"></i> Export CSV Report
            </a>
        </div>

        <div class="kpi-row">
            <div class="kpi-card" style="border-top-color: #2ecc71;">
                <h3>Total Revenue</h3>
                <p><?php echo number_format($total_revenue); ?> EGP</p>
            </div>
            <div class="kpi-card">
                <h3>Pre-Built Revenue</h3>
                <p><?php echo number_format($prebuilt_revenue); ?> EGP</p>
            </div>
            <div class="kpi-card" style="border-top-color: #f37021;">
                <h3>Custom Builder Rev</h3>
                <p><?php echo number_format($custom_revenue); ?> EGP</p>
            </div>
            <div class="kpi-card">
                <h3>Finalized Sales</h3>
                <p><?php echo $confirmed_bookings; ?></p>
            </div>
            <div class="kpi-card" style="border-top-color: #9b59b6;">
                <h3>Pending Approval</h3>
                <p><?php echo $pending_count; ?></p>
            </div>
            <div class="kpi-card">
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
                            <td><img src="assets/img/<?php echo $pkg['image_url']; ?>" class="thumb"></td>
                            <td><strong><?php echo htmlspecialchars($pkg['title']); ?></strong></td>
                            <td style="color: #666;"><?php echo ($pkg['package_id'] !== NULL) ? number_format($active_price) . " EGP" : "Variable Scale"; ?></td>
                            <td><span style="color:#1e5494; font-weight:bold;"><?php echo $pkg['confirmed_sales']; ?></span> Confirmed</td>
                            <td style="font-weight: bold; color: #2e7d32;">
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
                            <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo number_format($row['total_price']); ?> EGP</td>
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