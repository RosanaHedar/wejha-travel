<?php
session_start();
include 'wegha_db.php'; //

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
} //

$msg = "";

// 1. ADD CATEGORY logic
if (isset($_POST['add_cat'])) {
    $name = $_POST['cat_name'];
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)"); //
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        $msg = "Category added!";
    }
}

// 2. DELETE CATEGORY logic
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    // Check if packages are using this category before deleting
    $check = $conn->query("SELECT COUNT(*) as total FROM packages WHERE category_id = $id")->fetch_assoc();

    if ($check['total'] > 0) {
        $msg = "Error: Cannot delete category while it contains packages!";
    } else {
        $conn->query("DELETE FROM categories WHERE category_id = $id");
        $msg = "Category removed.";
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Categories | Wegha Admin</title>
    <style>
        /* Full Dashboard Layout CSS */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            background: #f4f7f6;
        }

        /* Sidebar Styling (What was missing) */
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
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .logout {
            color: #ff6b6b !important;
            margin-top: 50px;
        }

        /* Main Content Layout */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            margin-right: 10px;
        }

        .btn {
            background: #1e5494;
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
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            color: #1e5494;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?> <div class="main-content">
        <h1>Manage Categories</h1>

        <?php if ($msg): ?>
            <p style="background: #e6fffa; color: #2c7a7b; padding: 10px; border-radius: 5px; border: 1px solid #b2f5ea;">
                <?php echo $msg; ?>
            </p>
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