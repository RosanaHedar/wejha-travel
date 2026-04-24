<?php
session_start();
include 'wegha_db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. HANDLE DELETE ACTION
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM users WHERE user_id = $delete_id");
    header("Location: admin_users.php?msg=user_deleted");
    exit();
}

// 2. FETCH USERS WITH SEARCH
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT user_id, full_name, email, created_at FROM users";

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
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            background: #f4f7f6;
        }

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

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
        }

        /* Search Bar */
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

        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .user-table th,
        .user-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .user-table th {
            background: #f8f9fa;
            color: #1e5494;
        }

        .btn-edit {
            color: #1e5494;
            text-decoration: none;
            font-weight: bold;
            margin-right: 15px;
        }

        .btn-delete {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: bold;
        }

        .status-msg {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
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

        <?php if (isset($_GET['msg'])): ?>
            <div class="status-msg <?php echo ($_GET['msg'] == 'updated') ? 'msg-green' : 'msg-red'; ?>">
                <?php echo ($_GET['msg'] == 'updated') ? "Profile updated." : "Account removed."; ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="admin_users.php" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Search</button>
            <?php if ($search !== ''): ?> <a href="admin_users.php" style="align-self:center; color:#666; text-decoration:none;">Clear</a> <?php endif; ?>
        </form>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#999">#<?php echo $user['user_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="admin_edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn-edit">Edit</a>
                            <a href="admin_users.php?delete_id=<?php echo $user['user_id']; ?>" class="btn-delete" onclick="return confirm('Delete user?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>