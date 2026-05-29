<?php
session_start();
include '../wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$alert_msg = "";
$alert_type = "success";

// ========================================================
// CONTROLLER LOGIC: APPROVE, HIDE, OR DELETE REVIEWS
// ========================================================
if (isset($_GET['action']) && isset($_GET['review_id'])) {
    $review_id = intval($_GET['review_id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $conn->query("UPDATE reviews SET status = 'Approved' WHERE review_id = $review_id");
        header("Location: admin_reviews.php?msg=approved");
        exit();
    } elseif ($action === 'hide') {
        $conn->query("UPDATE reviews SET status = 'Hidden' WHERE review_id = $review_id");
        header("Location: admin_reviews.php?msg=hidden");
        exit();
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM reviews WHERE review_id = $review_id");
        header("Location: admin_reviews.php?msg=deleted");
        exit();
    }
}

// Handle GET Query Redirect Message Alerts
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'approved') {
        $alert_msg = "✅ Review approved and published to the live website.";
        $alert_type = "success";
    }
    if ($msg === 'hidden') {
        $alert_msg = "🔒 Review hidden and quarantined from the frontend.";
        $alert_type = "orange";
    }
    if ($msg === 'deleted') {
        $alert_msg = "🗑️ Review removed permanently from the database records.";
        $alert_type = "error";
    }
}

// ========================================================
// FETCH DATA LOGIC: PENDING QUEUE vs MODERATED LOGS
// ========================================================

// Query 1: Fetch Pending Approvals (Needs immediate admin action)
$pending_query = "SELECT r.*, u.full_name, COALESCE(p.title, '🛠️ Custom Package Experience') as trip_title 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  LEFT JOIN packages p ON r.package_id = p.package_id 
                  WHERE r.status = 'Pending' 
                  ORDER BY r.created_at DESC";
$pending_result = $conn->query($pending_query);

// Query 2: Fetch Moderated History (Items already approved or hidden manually)
$history_query = "SELECT r.*, u.full_name, COALESCE(p.title, '🛠️ Custom Package Experience') as trip_title 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  LEFT JOIN packages p ON r.package_id = p.package_id 
                  WHERE r.status != 'Pending' 
                  ORDER BY r.review_id DESC LIMIT 20";
$history_result = $conn->query($history_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Moderation | Wegha Admin</title>
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

        /* Highlight rule when active inside administration routes context */
        .sidebar a[href="admin_reviews.php"] {
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

        /* --- CONTENT MAIN INTERFACE FRAMEWORK --- */
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

        h2 {
            color: var(--primary-blue);
            font-size: 1.25rem;
            margin: 0 0 20px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Shared Dashboard Panel Card Design Layout */
        .data-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            margin-bottom: 35px;
        }

        /* Unified Table Structural Matrix Design */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #edf2f7;
        }

        th,
        td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        td {
            font-size: 0.95rem;
        }

        /* Dynamic Star Rendering Colors */
        .stars-gold {
            color: #fbbf24;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .text-comment {
            color: #475569;
            font-style: italic;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            display: inline-block;
            line-height: 1.5;
            border-left: 3px solid #cbd5e1;
        }

        /* Unified Status Badges Styling Layout */
        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-badge.approved {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
        }

        .status-badge.hidden-log {
            background: #fffaf0;
            color: #c2410c;
            border: 1px solid #fbd38d;
        }

        /* Action Management System Controls Links */
        .ctrl-link {
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            display: inline-block;
            margin-bottom: 4px;
        }

        .ctrl-approve {
            color: #10b981;
        }

        .ctrl-approve:hover {
            color: #059669;
            text-decoration: underline;
        }

        .ctrl-hide {
            color: #ea580c;
        }

        .ctrl-hide:hover {
            color: #c2410c;
            text-decoration: underline;
        }

        .ctrl-delete {
            color: #ef4444;
        }

        .ctrl-delete:hover {
            color: #dc2626;
            text-decoration: underline;
        }

        /* Unified Notification Status Alert Display Banner */
        .alert-banner {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .msg-green {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
        }

        .msg-orange {
            background: #fffaf0;
            color: #c2410c;
            border: 1px solid #fbd38d;
        }

        .msg-red {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
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
        <h1>Customer Feedback Moderation</h1>
        <p class="subtitle">Audit user-submitted traveler ratings, approve verified responses, or restrict testimonials:</p>

        <?php if (!empty($alert_msg)): ?>
            <div class="alert-banner <?php echo $alert_type === 'success' ? 'msg-green' : ($alert_type === 'orange' ? 'msg-orange' : 'msg-red'); ?>">
                <?php echo $alert_msg; ?>
            </div>
        <?php endif; ?>

        <div class="data-card">
            <h2><i class="fas fa-hourglass-start" style="color: var(--accent-orange);"></i> Awaiting Review Audit (Pending)</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%;">Traveler / Date</th>
                        <th style="width: 20%;">Target Itinerary</th>
                        <th style="width: 15%;">Score Rating</th>
                        <th style="width: 30%;">Comment Text</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending_result->num_rows > 0): ?>
                        <?php while ($r = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($r['full_name']); ?></strong><br>
                                    <small style="color: var(--text-muted); font-weight: 500;"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                                </td>
                                <td><span style="color: var(--primary-blue); font-weight: 600;"><?php echo htmlspecialchars($r['trip_title']); ?></span></td>
                                <td class="stars-gold">
                                    <?php for ($i = 1; $i < 6; $i++) {
                                        echo ($i <= $r['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    } ?>
                                    <span style="color: var(--text-main); font-weight: 700; margin-left: 4px;"><?php echo $r['rating']; ?>.0</span>
                                </td>
                                <td><span class="text-comment">"<?php echo htmlspecialchars($r['comment']); ?>"</span></td>
                                <td>
                                    <a href="admin_reviews.php?action=approve&review_id=<?php echo $r['review_id']; ?>" class="ctrl-link ctrl-approve"><i class="fas fa-check"></i> Approve</a><br>
                                    <a href="admin_reviews.php?action=delete&review_id=<?php echo $r['review_id']; ?>" class="ctrl-link ctrl-delete" onclick="return confirm('Completely purge this comment entry from storage registries?')"><i class="fas fa-trash-alt"></i> Trash</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted); font-weight: 500;">🎉 The review moderation queue is completely clear! No pending items.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="data-card">
            <h2><i class="fas fa-history" style="color: var(--primary-blue);"></i> Moderation History Log (Recent Activity)</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 15%;">Traveler</th>
                        <th style="width: 20%;">Target Itinerary</th>
                        <th style="width: 25%;">Review Details</th>
                        <th style="width: 13%;">State Status</th>
                        <th style="width: 12%;">Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history_result->num_rows > 0): ?>
                        <?php while ($h = $history_result->fetch_assoc()):
                            $is_approved = ($h['status'] === 'Approved');
                        ?>
                            <tr>
                                <td style="color: var(--text-muted); font-weight: 500; white-space: nowrap;"><?php echo date('M d, Y', strtotime($h['created_at'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($h['full_name']); ?></strong></td>
                                <td><span style="color: #475569; font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($h['trip_title']); ?></span></td>
                                <td>
                                    <div class="stars-gold" style="margin-bottom: 5px;">
                                        <?php for ($i = 1; $i < 6; $i++) {
                                            echo ($i <= $h['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                        } ?>
                                    </div>
                                    <small style="color: var(--text-muted); font-style: italic; font-weight: 500; display: block; line-height: 1.4;">"<?php echo htmlspecialchars($h['comment']); ?>"</small>
                                </td>
                                <td>
                                    <?php if ($is_approved): ?>
                                        <span class="status-badge approved">Live On Site</span>
                                    <?php else: ?>
                                        <span class="status-badge hidden-log">Quarantined</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_approved): ?>
                                        <a href="admin_reviews.php?action=hide&review_id=<?php echo $h['review_id']; ?>" class="ctrl-link ctrl-hide" title="Take comment offline"><i class="fas fa-eye-slash"></i> Hide</a>
                                    <?php else: ?>
                                        <a href="admin_reviews.php?action=approve&review_id=<?php echo $h['review_id']; ?>" class="ctrl-link ctrl-approve" title="Restore comment live visibility"><i class="fas fa-check"></i> Restore</a>
                                    <?php endif; ?><br>
                                    <a href="admin_reviews.php?action=delete&review_id=<?php echo $h['review_id']; ?>" class="ctrl-link ctrl-delete" onclick="return confirm('Permanently remove this entry row from active platform logging metrics?')"><i class="fas fa-trash-alt"></i> Wipe</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted); font-weight: 500;">No archived history records found inside active review matrices.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>