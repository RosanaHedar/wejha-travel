<?php
session_start();
include 'wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$alert_msg = "";
$alert_type = "msg-green";

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
        $alert_type = "msg-green";
    }
    if ($msg === 'hidden') {
        $alert_msg = "🔒 Review hidden and quarantined from the frontend.";
        $alert_type = "msg-orange";
    }
    if ($msg === 'deleted') {
        $alert_msg = "🗑️ Review removed permanently from the database records.";
        $alert_type = "msg-red";
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
    <title>Review Moderation | Wegha Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
        }

        /* --- UNIFIED ADMIN SIDEBAR OVERRIDES --- */
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

        /* Highlight rule when active inside administration routes context */
        .sidebar a[href="admin_reviews.php"] {
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

        /* --- CONTENT MAIN INTERFACE FRAMEWORK --- */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
            box-sizing: border-box;
        }

        h1 {
            color: #1e5494;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        h2 {
            color: #4a4a4a;
            font-size: 1.2rem;
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
        }

        /* Data display tables */
        .data-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        th {
            background: #f8f9fa;
            color: #1e5494;
            font-weight: 600;
        }

        /* Dynamic Star Rendering Engine */
        .stars-gold {
            color: #f1c40f;
            font-size: 0.85rem;
        }

        .text-comment {
            color: #555;
            font-style: italic;
            background: #fafafa;
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 5px;
            line-height: 1.4;
        }

        /* Badges & Actions Links CSS Layout styles */
        .status-badge {
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .approved {
            background: #d4edda;
            color: #155724;
        }

        .hidden-log {
            background: #ffeeba;
            color: #856404;
        }

        .ctrl-link {
            text-decoration: none;
            font-weight: bold;
            margin-right: 12px;
            font-size: 0.85rem;
            transition: 0.2s;
        }

        .ctrl-approve {
            color: #2ecc71;
        }

        .ctrl-approve:hover {
            color: #27ae60;
        }

        .ctrl-hide {
            color: #e67e22;
        }

        .ctrl-hide:hover {
            color: #d35400;
        }

        .ctrl-delete {
            color: #e74c3c;
        }

        .ctrl-delete:hover {
            color: #c0392b;
        }

        /* Alert Display Windows */
        .alert-banner {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .msg-green {
            background: #f0fff4;
            color: #2f855a;
            border: 1px solid #c6f6d5;
        }

        .msg-orange {
            background: #fffaf0;
            color: #dd6b20;
            border: 1px solid #fbd38d;
        }

        .msg-red {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-comments"></i> Customer Feedback Moderation</h1>
        <p style="color:#666; margin-bottom: 25px;">Audit, approve, or restrict user-submitted traveler ratings and experience testimonials.</p>

        <?php if (!empty($alert_msg)): ?>
            <div class="alert-banner <?php echo $alert_type; ?>">
                <?php echo $alert_msg; ?>
            </div>
        <?php endif; ?>

        <div class="data-card">
            <h2><i class="fas fa-hourglass-start" style="color:#e67e22;"></i> Awaiting Review Audit (Pending)</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Traveler / Date</th>
                        <th style="width: 25%;">Target Itinerary</th>
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
                                    <small style="color:#aaa;"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                                </td>
                                <td><span style="color:#1e5494; font-weight:600;"><?php echo htmlspecialchars($r['trip_title']); ?></span></td>
                                <td class="stars-gold">
                                    <?php for ($i = 1; $i < 6; $i++) {
                                        echo ($i <= $r['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    } ?>
                                    <span style="color:#4a4a4a; font-weight:600; margin-left:4px;"><?php echo $r['rating']; ?>.0</span>
                                </td>
                                <td><span class="text-comment">"<?php echo htmlspecialchars($r['comment']); ?>"</span></td>
                                <td>
                                    <a href="admin_reviews.php?action=approve&review_id=<?php echo $r['review_id']; ?>" class="ctrl-link ctrl-approve"><i class="fas fa-check"></i> Approve</a><br>
                                    <a href="admin_reviews.php?action=delete&review_id=<?php echo $r['review_id']; ?>" class="ctrl-link ctrl-delete" onclick="return confirm('Completely purge this comment entry?')"><i class="fas fa-trash-alt"></i> Trash</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #aaa; font-weight: 500;">🎉 The review moderation queue is completely clear! No pending items.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="data-card">
            <h2><i class="fas fa-history" style="color:#1e5494;"></i> Moderation History Log (Recent Activity)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Traveler</th>
                        <th>Target Itinerary</th>
                        <th>Review Details</th>
                        <th>State Status</th>
                        <th>Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history_result->num_rows > 0): ?>
                        <?php while ($h = $history_result->fetch_assoc()):
                            $is_approved = ($h['status'] === 'Approved');
                        ?>
                            <tr>
                                <td><small style="color:#666;"><?php echo date('M d, Y', strtotime($h['created_at'])); ?></small></td>
                                <td><strong><?php echo htmlspecialchars($h['full_name']); ?></strong></td>
                                <td><small style="font-weight:600;"><?php echo htmlspecialchars($h['trip_title']); ?></small></td>
                                <td>
                                    <div class="stars-gold" style="margin-bottom:3px;">
                                        <?php for ($i = 1; $i < 6; $i++) {
                                            echo ($i <= $h['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                        } ?>
                                    </div>
                                    <small style="color:#666; font-style:italic;">"<?php echo htmlspecialchars($h['comment']); ?>"</small>
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
                                    <?php endif; ?>
                                    <a href="admin_reviews.php?action=delete&review_id=<?php echo $h['review_id']; ?>" class="ctrl-link ctrl-delete" onclick="return confirm('Permanently remove from logs?')"><i class="fas fa-trash-alt"></i> Wipe</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 25px; color: #999;">No archived history records found inside active review matrices.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>