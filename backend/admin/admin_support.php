<?php
session_start();
include '../wegha_db.php';

// Admin Authentication Shield
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// HANDLE UPDATE: Status & Admin Notes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_ticket'])) {
    $tid = intval($_POST['ticket_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);

    // Update the ENUM status and the private admin notes
    $conn->query("UPDATE contact_messages SET status = '$new_status', admin_notes = '$notes' WHERE id = $tid");
    header("Location: admin_support.php?msg=updated");
    exit();
}

// Fetch messages ordered by priority (Pending first)
$result = $conn->query("SELECT * FROM contact_messages ORDER BY FIELD(status, 'Pending', 'In Progress', 'Done'), id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets | Wegha Admin</title>
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

        /* Highlight rule for the active support module link item */
        .sidebar a[href="admin_support.php"] {
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

        /* Shared Dashboard Panel Card Design Layout */
        .card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            margin-bottom: 30px;
        }

        /* Success/Notification Alert Banner box styling */
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

        /* Unified Table Structural Matrix Design */
        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #edf2f7;
        }

        .tickets-table th,
        .tickets-table td {
            padding: 18px 16px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
        }

        .tickets-table th {
            background: #f8fafc;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        .customer-info-block strong {
            font-size: 1rem;
            color: var(--text-main);
        }

        .customer-info-block small {
            display: block;
            color: var(--text-muted);
            margin-top: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .ticket-message-text {
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
            white-space: pre-line;
        }

        /* Unified Status Badges Layout Sheets */
        .badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-top: 12px;
        }

        .status-Pending {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
        }

        .status-In-Progress {
            background: #fffaf0;
            color: #c2410c;
            border: 1px solid #fbd38d;
        }

        .status-Done {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
        }

        /* Form elements configuration blueprints inside table space cells */
        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            margin-top: 8px;
        }

        .form-label:first-of-type {
            margin-top: 0;
        }

        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.9rem;
            color: var(--text-main);
            background-color: #fbfcfd;
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
            margin-bottom: 12px;
        }

        select:focus,
        textarea:focus {
            border-color: var(--primary-blue);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(30, 84, 148, 0.1);
        }

        textarea {
            resize: vertical;
        }

        .btn-update {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 14px;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 4px 6px -1px rgba(30, 84, 148, 0.15);
            transition: all 0.2s ease;
        }

        .btn-update:hover {
            background: #153d6b;
            transform: translateY(-1px);
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
        <h1>Customer Support Tickets</h1>
        <p class="subtitle">Review traveler inquiries, handle incoming feedback messages, and document resolution notes:</p>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="alert"><i class="fas fa-check-circle"></i> Support ticket logs and notes modified successfully.</div>
        <?php endif; ?>

        <div class="card">
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Customer Info</th>
                        <th style="width: 45%;">Message</th>
                        <th style="width: 30%;">Update Status & Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="customer-info-block">
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    <small><i class="fas fa-envelope" style="width: 16px;"></i> <?php echo htmlspecialchars($row['email']); ?></small>
                                    <small><i class="fas fa-phone" style="width: 16px;"></i> <?php echo htmlspecialchars($row['phone']); ?></small>

                                    <span class="badge status-<?php echo str_replace(' ', '-', $row['status']); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <p class="ticket-message-text">"<?php echo htmlspecialchars($row['message']); ?>"</p>
                                </td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">

                                        <label class="form-label">Set Status</label>
                                        <select name="new_status">
                                            <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>🔴 Pending</option>
                                            <option value="In Progress" <?php if ($row['status'] == 'In Progress') echo 'selected'; ?>>🟡 In Progress</option>
                                            <option value="Done" <?php if ($row['status'] == 'Done') echo 'selected'; ?>>🟢 Done</option>
                                        </select>

                                        <label class="form-label">Admin Notes</label>
                                        <textarea name="admin_notes" rows="3" placeholder="Add private resolution logs..."><?php echo htmlspecialchars($row['admin_notes']); ?></textarea>

                                        <button type="submit" name="update_ticket" class="btn-update">Save Changes</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 40px; color: var(--text-muted); font-weight: 500;">No support tickets or contact history found in database parameters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>