<?php
session_start();
include '../wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// --- HANDLE STATUS UPDATES (Confirm / Cancel) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $action = $_GET['action'];

    $new_status = 'Pending';
    if ($action === 'confirm') $new_status = 'Confirmed';
    if ($action === 'cancel') $new_status = 'Cancelled';

    $update_sql = "UPDATE bookings SET status = '$new_status' WHERE booking_id = $booking_id";
    if ($conn->query($update_sql)) {
        header("Location: admin_bookings.php?msg=status_changed");
        exit();
    }
}

// --- FETCH BOOKINGS WITH SEARCH LOGIC ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// FIXED: Adjusted internal select string manipulation matrix to map individual service dates inline
$query = "SELECT 
            b.booking_id,
            b.booking_date,
            b.total_price,
            b.status,
            u.full_name as customer_name,
            u.phone as customer_phone,
            COALESCE(p.title, '🛠️ Custom Tailored Trip') as trip_title,
            b.custom_package_id,
            (SELECT GROUP_CONCAT(
                CONCAT(
                    cps.quantity, 'x ', s.service_name, 
                    IF(cps.service_date IS NOT NULL AND cps.service_date != '0000-00-00' AND cps.service_date != '', 
                       CONCAT(' <small style=\"color:#1e5494; font-weight:bold;\">[📅 ', DATE_FORMAT(cps.service_date, '%b %d, %Y'), ']</small>'), 
                       ''
                    )
                ) SEPARATOR '<br>'
             ) 
             FROM customer_package_services cps 
             JOIN services s ON cps.service_id = s.service_id 
             WHERE cps.customer_package_id = b.custom_package_id) as custom_details
          FROM bookings b
          JOIN users u ON b.user_id = u.user_id
          LEFT JOIN packages p ON b.package_id = p.package_id";

if ($search !== '') {
    $query .= " WHERE u.full_name LIKE ? OR u.phone LIKE ? OR p.title LIKE ? OR (p.title IS NULL AND 'Custom' LIKE ?)";
}

$query .= " ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($query);

if ($search !== '') {
    $term = "%$search%";
    $stmt->bind_param("ssss", $term, $term, $term, $term);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Wegha Admin</title>
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

        /* Highlight rule for the active bookings navigation tracking model item */
        .sidebar a[href="admin_bookings.php"] {
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

        /* Shared Container Panels Structure Sheets */
        .card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            margin-bottom: 30px;
        }

        .search-container {
            margin-bottom: 25px;
            display: flex;
            gap: 12px;
        }

        .search-input {
            flex-grow: 1;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            background-color: #fbfcfd;
            transition: all 0.2s ease;
            color: var(--text-main);
        }

        .search-input:focus {
            border-color: var(--primary-blue);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(30, 84, 148, 0.1);
        }

        .btn-search {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 0 28px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 4px 6px -1px rgba(30, 84, 148, 0.15);
            transition: all 0.2s ease;
        }

        .btn-search:hover {
            background: #153d6b;
            transform: translateY(-1px);
        }

        /* Success Alert Banner styling boxes sheets */
        .alert {
            padding: 15px;
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Unified Table Structural Matrix Configurations */
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #edf2f7;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .bookings-table th {
            background: #f8fafc;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        .bookings-table td {
            font-size: 0.95rem;
        }

        /* Account Status Pill Badges Sheets Mappings */
        .badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .badge.confirmed {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
        }

        .badge.pending {
            background: #fffaf0;
            color: #c2410c;
            border: 1px solid #fbd38d;
        }

        .badge.cancelled {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
        }

        /* Management Control System Actions Buttons */
        .btn-action {
            text-decoration: none;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-right: 6px;
            transition: all 0.2s ease;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-confirm {
            background: #10b981;
            color: white;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.1);
        }

        .btn-confirm:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #ef4444;
            color: white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);
        }

        .btn-cancel:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Custom Builder Sub-List Breakout Box Styles Configuration */
        .custom-item-list {
            font-size: 0.82rem;
            color: #475569;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            margin-top: 8px;
            border-left: 3px solid var(--accent-orange);
            line-height: 1.6;
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
        <h1>Manage Bookings Logs</h1>
        <p class="subtitle">Monitor processing orders, review incoming traveler custom itineraries, or cancel logs:</p>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'status_changed'): ?>
            <div class="alert"><i class="fas fa-check-circle"></i> Booking transaction state updated successfully.</div>
        <?php endif; ?>

        <div class="card">
            <form method="GET" action="" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search by customer name, phone number, or trip title choice..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>

            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date Ordered</th>
                        <th>Customer Details</th>
                        <th>Adventure Choice / Breakdown</th>
                        <th>Total Price</th>
                        <th>Current Status</th>
                        <th>Approval Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td style="color: var(--text-muted); font-weight: 500;">#<?php echo $row['booking_id']; ?></td>
                                <td style="color: var(--text-muted); font-weight: 500; white-space: nowrap;"><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                                    <small style="color: var(--text-muted); font-weight: 500;"><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($row['customer_phone']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['trip_title']); ?></strong>

                                    <?php if ($row['custom_package_id'] !== NULL): ?>
                                        <div class="custom-item-list">
                                            <?php echo $row['custom_details']; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 700; color: var(--primary-blue); white-space: nowrap;"><?php echo number_format($row['total_price']); ?> EGP</td>
                                <td>
                                    <span class="badge <?php echo strtolower($row['status']); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <a href="admin_bookings.php?action=confirm&id=<?php echo $row['booking_id']; ?>" class="btn-action btn-confirm" onclick="return confirm('Approve and confirm this booking request?')">Confirm</a>
                                        <a href="admin_bookings.php?action=cancel&id=<?php echo $row['booking_id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Reject and cancel this booking request?')">Cancel</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic; font-size: 0.85rem; font-weight: 500;">No Actions Needed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted); font-weight: 500;">No matching active travel bookings found in the system log.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>