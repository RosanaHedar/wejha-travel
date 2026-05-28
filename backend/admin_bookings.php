<?php
session_start();
include 'wegha_db.php';

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
    <title>Manage Bookings | Wegha Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
        }

        /* --- SELF-CONTAINED SIDEBAR LAYOUT OVERRIDES --- */
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
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .sidebar a[href="admin_bookings.php"] {
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

        /* --- MAIN CONTENT AREA --- */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
            box-sizing: border-box;
        }

        h1 {
            color: #1e5494;
            margin-bottom: 30px;
        }

        /* Search Controls Style */
        .search-container {
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex-grow: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }

        .search-input:focus {
            border-color: #1e5494;
            box-shadow: 0 0 8px rgba(30, 84, 148, 0.1);
        }

        .btn-search {
            background: #1e5494;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn-search:hover {
            background: #163f72;
        }

        /* Notification Alert Box */
        .alert {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        /* Table Dashboard Styles */
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .bookings-table th,
        .bookings-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .bookings-table th {
            background: #f8f9fa;
            color: #1e5494;
            font-size: 0.9rem;
        }

        .bookings-table td {
            font-size: 0.9rem;
            vertical-align: middle;
        }

        /* Status Pill Indicators */
        .badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .confirmed {
            background: #e6fffa;
            color: #2c7a7b;
            border: 1px solid #b2f5ea;
        }

        .pending {
            background: #fffaf0;
            color: #dd6b20;
            border: 1px solid #fbd38d;
        }

        .cancelled {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        /* Action Buttons */
        .btn-action {
            text-decoration: none;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-right: 5px;
            transition: 0.2s;
            display: inline-block;
        }

        .btn-confirm {
            background: #2ecc71;
            color: white;
        }

        .btn-confirm:hover {
            background: #27ae60;
        }

        .btn-cancel {
            background: #e74c3c;
            color: white;
        }

        .btn-cancel:hover {
            background: #c0392b;
        }

        .custom-item-list {
            font-size: 0.8rem;
            color: #555;
            background: #fafafa;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 5px;
            border-left: 3px solid #f37021;
            line-height: 1.5;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1>Manage Bookings Logs</h1>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'status_changed'): ?>
            <div class="alert"><i class="fas fa-check-circle"></i> Booking transaction state updated successfully.</div>
        <?php endif; ?>

        <form method="GET" action="admin_bookings.php" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Search by customer name, phone number, or trip choice..." value="<?php echo htmlspecialchars($search); ?>">
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
                            <td>#<?php echo $row['booking_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                                <small style="color:#666;"><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($row['customer_phone']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['trip_title']); ?></strong>

                                <?php if ($row['custom_package_id'] !== NULL): ?>
                                    <div class="custom-item-list">
                                        <?php echo $row['custom_details']; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: bold; color: #1e5494;"><?php echo number_format($row['total_price']); ?> EGP</td>
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
                                    <span style="color:#aaa; font-style:italic; font-size:0.85rem;">No Actions Needed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color:#888;">No matching active travel bookings found in the system log.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>