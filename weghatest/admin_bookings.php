<?php
session_start();
include 'wegha_db.php';

// --- THE ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. HANDLE STATUS UPDATE
if (isset($_GET['id']) && isset($_GET['new_status'])) {
    $booking_id = intval($_GET['id']);
    $new_status = $_GET['new_status'];

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);

    if ($stmt->execute()) {
        header("Location: admin_bookings.php?msg=status_updated");
        exit();
    }
}

// 2. FETCH ALL BOOKINGS WITH SEARCH LOGIC
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT 
            b.booking_id, 
            u.full_name as customer_name, 
            u.email as customer_email,
            p.title as package_name, 
            b.num_travelers, 
            b.total_price, 
            b.travel_date, 
            b.status 
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN packages p ON b.package_id = p.package_id";

if ($search !== '') {
    $sql .= " WHERE u.full_name LIKE ? OR p.title LIKE ?";
}

$sql .= " ORDER BY b.booking_id DESC";

$stmt = $conn->prepare($sql);

if ($search !== '') {
    $term = "%$search%";
    $stmt->bind_param("ss", $term, $term);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Bookings | Wegha Admin</title>
    <style>
        /* Global Layout */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            background: #f4f7f6;
        }

        /* Fixed Sidebar Styling */
        .sidebar {
            width: 250px;
            background: #1e5494;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
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
            padding: 12px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Main Content Layout */
        .main-content {
            margin-left: 270px;
            /* Ensures content starts after the sidebar */
            padding: 40px;
            width: calc(100% - 270px);
        }

        /* Search Bar Styles */
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
        }

        .btn-search {
            background: #1e5494;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        /* Table Styling */
        .booking-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .booking-table th,
        .booking-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .booking-table th {
            background: #f8f9fa;
            color: #1e5494;
            font-weight: 600;
        }

        /* Status Colors */
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fffaf0;
            color: #9c4221;
        }

        .status-confirmed {
            background: #f0fff4;
            color: #2f855a;
        }

        .status-cancelled {
            background: #fff5f5;
            color: #c53030;
        }

        .action-links a {
            text-decoration: none;
            font-size: 0.8rem;
            margin-right: 10px;
            font-weight: bold;
        }

        .confirm-link {
            color: #2f855a;
        }

        .cancel-link {
            color: #c53030;
        }

        .msg {
            background: #f0fff4;
            color: #2f855a;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c6f6d5;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1>Travel Bookings</h1>
        <p>Monitor transactions and update itinerary status.</p>

        <?php if (isset($_GET['msg'])): ?>
            <div class="msg">Booking status has been updated successfully.</div>
        <?php endif; ?>

        <form method="GET" action="admin_bookings.php" class="search-container">
            <input type="text" name="search" class="search-input"
                placeholder="Search by customer name or trip title..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Search</button>
            <?php if ($search !== ''): ?>
                <a href="admin_bookings.php" style="align-self: center; color: #666; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>

        <table class="booking-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Trip</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['booking_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                                <small style="color:#888"><?php echo htmlspecialchars($row['customer_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['package_name']); ?> (<?php echo $row['num_travelers']; ?> pax)</td>
                            <td><?php echo date('M d, Y', strtotime($row['travel_date'])); ?></td>
                            <td><strong><?php echo number_format($row['total_price']); ?> EGP</strong></td>
                            <td>
                                <span class="status status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="action-links">
                                <?php if ($row['status'] == 'Pending'): ?>
                                    <a href="admin_bookings.php?id=<?php echo $row['booking_id']; ?>&new_status=Confirmed" class="confirm-link">Confirm</a>
                                    <a href="admin_bookings.php?id=<?php echo $row['booking_id']; ?>&new_status=Cancelled" class="cancel-link">Cancel</a>
                                <?php else: ?>
                                    <span style="color:#ccc; font-size: 0.8rem;">No Actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #666;">No bookings found matching your search.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>