<?php
session_start();
include '../wegha_db.php';

// --- THE ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";
$msg_type = "";

// 1. ADD CATEGORY logic
if (isset($_POST['add_cat'])) {
    $name = trim($_POST['cat_name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $msg = "Category added successfully!";
            $msg_type = "success";
        }
    }
}

// 2. DELETE CATEGORY logic
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $check = $conn->query("SELECT COUNT(*) as total FROM packages WHERE category_id = $id")->fetch_assoc();

    if ($check['total'] > 0) {
        $msg = "Error: Cannot delete category while it contains packages!";
        $msg_type = "error";
    } else {
        $conn->query("DELETE FROM categories WHERE category_id = $id");
        $msg = "Category removed successfully.";
        $msg_type = "success";
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Wegha Admin</title>
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

        /* Highlight rule for the active page link */
        .sidebar a[href="admin_categories.php"] {
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

        /* --- CONTENT WINDOW --- */
        .main-content {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
            box-sizing: border-box;
            min-height: 100vh;
        }

        h1 {
            color: var(--primary-blue);
            margin: 0 0 20px 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            margin-bottom: 30px;
            border: 1px solid #edf2f7;
        }

        input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            margin-right: 10px;
        }

        .btn {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
        }

        th {
            background: #f8f9fa;
            color: var(--primary-blue);
            padding: 15px;
            text-align: left;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .msg {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success {
            background: #e6fffa;
            color: #2c7a7b;
            border: 1px solid #b2f5ea;
        }

        .error {
            background: #fff5f5;
            color: #c53030;
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
        <h1>Manage Categories</h1>

        <?php if ($msg): ?>
            <div class="msg <?php echo $msg_type; ?>"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Add New Category</h3>
            <form method="POST">
                <input type="text" name="cat_name" placeholder="e.g. Adventure, History, Luxury" required>
                <button type="submit" name="add_cat" class="btn">Add Category</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Category ID</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $cat['category_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                        <td>
                            <a href="admin_categories.php?delete_id=<?php echo $cat['category_id']; ?>"
                                style="color: #ff4d4d; text-decoration: none; font-weight: bold;"
                                onclick="return confirm('Warning: Are you sure you want to remove this category?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>