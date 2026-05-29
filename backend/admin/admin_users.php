<?php
session_start();
include '../wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$status_msg = "";
$msg_type = "success";

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
        $msg_type = "error";
    }
    if ($msg === 'activated') {
        $status_msg = "🔓 Traveler account reactivated and set live.";
        $msg_type = "success";
    }
    if ($msg === 'user_deleted') {
        $status_msg = "🗑️ User record wiped permanently from registry.";
        $msg_type = "error";
    }
    if ($msg === 'updated') {
        $status_msg = "📝 Profile information updated.";
        $msg_type = "success";
    }
    if ($msg === 'delete_failed') {
        $status_msg = "❌ Cannot delete user! Historical booking records exist. Use Suspend instead.";
        $msg_type = "error";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Wegha Admin</title>
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

        /* Highlight rule for the active users configuration list model item */
        .sidebar a[href="admin_users.php"] {
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

        /* Shared Panel Design Structure Sheet */
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

        /* Records Logs Dash Table Matrix Layout */
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #edf2f7;
        }

        .user-table th,
        .user-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .user-table th {
            background: #f8fafc;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        .user-table td {
            font-size: 0.95rem;
        }

        /* Action Controls System Vectors */
        .btn-action {
            text-decoration: none;
            font-weight: 700;
            margin-right: 12px;
            font-size: 0.85rem;
        }

        .btn-edit {
            color: var(--primary-blue);
        }

        .btn-suspend {
            color: #ea580c;
        }

        .btn-activate {
            color: #16a34a;
        }

        .btn-delete {
            color: #ef4444;
        }

        .btn-action:hover {
            text-decoration: underline;
        }

        /* Soft State Modifiers */
        .suspended-row {
            background: #fdfdfd;
            opacity: 0.6;
        }

        /* Account Status Pill Badges Sheets */
        .badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 8px;
            display: inline-block;
        }

        .badge-active {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
        }

        .badge-suspended {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
        }

        /* Status Banner Container Systems */
        .status-msg {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .msg-red {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
        }

        .msg-green {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
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
        <h1>Registered Travelers</h1>
        <p class="subtitle">Monitor registered user demographics, alter access permissions, or suspend accounts:</p>

        <?php if (!empty($status_msg)): ?>
            <div class="status-msg <?php echo ($msg_type === 'error') ? 'msg-red' : 'msg-green'; ?>">
                <?php echo $status_msg; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="GET" action="" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search accounts by full name or email address..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search">Search</button>
                <?php if ($search !== ''): ?>
                    <a href="admin_users.php" style="align-self:center; margin-left:15px; color: var(--text-muted); text-decoration:none; font-weight:700; font-size:0.9rem;">Clear Filters</a>
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
                                <td style="color: var(--text-muted); font-weight: 500;">#<?php echo $user['user_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                    <?php if ($is_suspended): ?>
                                        <span class="badge badge-suspended">Suspended</span>
                                    <?php else: ?>
                                        <span class="badge badge-active">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 500; color: #475569;"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td style="color: var(--text-muted); font-weight: 500;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
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
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted); font-weight: 500;">No traveler profile records matching search criteria were located inside active tables.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>