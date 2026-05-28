<?php
session_start();
include 'wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$status_msg = "";
$msg_type = "msg-green";

// ========================================================
// CONTROLLERS LOGIC: HANDLE ACCOUNT STATE ACTIONS
// ========================================================

// Handle Status Toggles (Suspend / Reactivate)
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $u_id = intval($_GET['user_id']);
    $action = $_GET['action'];

    if ($action === 'suspend') {
        $conn->query("UPDATE users SET is_active = 0 WHERE user_id = $u_id");
        header("Location: admin_users.php?msg=suspended");
        exit();
    } elseif ($action === 'activate') {
        $conn->query("UPDATE users SET is_active = 1 WHERE user_id = $u_id");
        header("Location: admin_users.php?msg=activated");
        exit();
    }
}

// Handle Permanent Hard Delete Action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Will fail gracefully if constrained by matching foreign keys in bookings
    if ($conn->query("DELETE FROM users WHERE user_id = $delete_id")) {
        header("Location: admin_users.php?msg=user_deleted");
    } else {
        header("Location: admin_users.php?msg=delete_failed");
    }
    exit();
}

// Process URL Response Alert Flags
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'suspended') {
        $status_msg = "🔒 Traveler account suspended successfully.";
        $msg_type = "msg-red";
    }
    if ($msg === 'activated') {
        $status_msg = "🔓 Traveler account reactivated and set live.";
        $msg_type = "msg-green";
    }
    if ($msg === 'user_deleted') {
        $status_msg = "🗑️ User record wiped permanently from registry.";
        $msg_type = "msg-red";
    }
    if ($msg === 'updated') {
        $status_msg = "📝 Profile information updated.";
        $msg_type = "msg-green";
    }
    if ($msg === 'delete_failed') {
        $status_msg = "❌ Cannot delete user! Historical booking records exist. Use Suspend instead.";
        $msg_type = "msg-red";
    }
}

// ========================================================
// FETCH DATA LOGIC WITH SEARCH FILTERS
// ========================================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT user_id, full_name, email, created_at, is_active FROM users";

if ($search !== '') {
    $sql .= " WHERE full_name LIKE ? OR email LIKE ?";
}
$sql .= " ORDER BY user_id DESC";

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
    <title>Manage Users | Wegha Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            background: #f4f7f6;
        }

        /* --- SELF-CONTAINED FIXED SIDEBAR OVERRIDES --- */
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

        .sidebar a[href="admin_users.php"] {
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

        /* --- CONTENT WRAPPER HUB --- */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
            box-sizing: border-box;
        }

        h1 {
            color: #1e5494;
            margin-bottom: 35px;
        }

        /* Search Layout */
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
            outline: none;
            font-size: 0.95rem;
        }

        .search-input:focus {
            border-color: #1e5494;
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

        /* Records Logs Dash Table */
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .user-table th,
        .user-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .user-table th {
            background: #f8f9fa;
            color: #1e5494;
        }

        /* Action Controls Layout Anchors */
        .btn-action {
            text-decoration: none;
            font-weight: bold;
            margin-right: 12px;
            font-size: 0.85rem;
        }

        .btn-edit {
            color: #1e5494;
        }

        .btn-suspend {
            color: #e67e22;
        }

        .btn-activate {
            color: #2ecc71;
        }

        .btn-delete {
            color: #ff4d4d;
        }

        /* Row Soft State modifiers */
        .suspended-row {
            background: #fafafa;
            opacity: 0.65;
        }

        /* Account Status Pill Badges */
        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 8px;
            display: inline-block;
        }

        .badge-active {
            background: #e6fffa;
            color: #2c7a7b;
        }

        .badge-suspended {
            background: #fff5f5;
            color: #c53030;
        }

        /* Status Banner Box Panels */
        .status-msg {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .msg-red {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .msg-green {
            background: #f0fff4;
            color: #2f855a;
            border: 1px solid #c6f6d5;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1>Registered Travelers</h1>

        <?php if (!empty($status_msg)): ?>
            <div class="status-msg <?php echo $msg_type; ?>">
                <?php echo $status_msg; ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="admin_users.php" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Search accounts by full name or email address..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Search</button>
            <?php if ($search !== ''): ?>
                <a href="admin_users.php" style="align-self:center; margin-left:10px; color:#888; text-decoration:none; font-weight:600;">Clear</a>
            <?php endif; ?>
        </form>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Join Date</th>
                    <th>Management Controls</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()):
                        $is_suspended = ($user['is_active'] == 0);
                    ?>
                        <tr class="<?php echo $is_suspended ? 'suspended-row' : ''; ?>">
                            <td style="color:#aaa">#<?php echo $user['user_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                <?php if ($is_suspended): ?>
                                    <span class="badge badge-suspended">Suspended</span>
                                <?php else: ?>
                                    <span class="badge badge-active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="admin_edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn-action btn-edit">Edit</a>

                                <?php if ($is_suspended): ?>
                                    <a href="admin_users.php?action=activate&user_id=<?php echo $user['user_id']; ?>" class="btn-action btn-activate">Activate</a>
                                <?php else: ?>
                                    <a href="admin_users.php?action=suspend&user_id=<?php echo $user['user_id']; ?>" class="btn-action btn-suspend" onclick="return confirm('Freeze access parameters for this user account?')">Suspend</a>
                                <?php endif; ?>

                                <a href="admin_users.php?delete_id=<?php echo $user['user_id']; ?>" class="btn-action btn-delete" onclick="return confirm('Permanently purge this user account layout model? Warning: Will delete profile references.')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #888;">No traveler profile records matching search criteria were located inside active tables.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>