<?php
session_start();
include 'wegha_db.php'; //

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Status Toggle (Enable/Disable)
if (isset($_GET['toggle_id'])) {
    $tid = intval($_GET['toggle_id']);
    $conn->query("UPDATE packages SET is_active = 1 - is_active WHERE package_id = $tid");
    header("Location: admin_manage_packages.php?msg=status_updated");
    exit();
}

// 1. HANDLE DELETE ACTION
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $img_query = $conn->query("SELECT image_url FROM packages WHERE package_id = $delete_id");
    $img_data = $img_query->fetch_assoc();

    if ($img_data) {
        $file_path = "assets/img/" . $img_data['image_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $conn->query("DELETE FROM packages WHERE package_id = $delete_id");
    header("Location: admin_manage_packages.php?msg=deleted");
    exit();
}

// 2. FETCH PACKAGES WITH SEARCH LOGIC
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// UPDATED SQL: Using LEFT JOIN and IFNULL to show all 8 packages
$sql = "SELECT p.*, IFNULL(c.name, 'General/None') as category_name 
        FROM packages p 
        LEFT JOIN categories c ON p.category_id = c.category_id";

if ($search !== '') {
    $sql .= " WHERE p.title LIKE ? OR c.name LIKE ?";
}

$sql .= " ORDER BY p.package_id DESC";

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
    <title>Manage Packages | Wegha Admin</title>
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

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
        }

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

        .btn-search {
            background: #1e5494;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .package-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .package-table th,
        .package-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .package-table th {
            background: #f8f9fa;
            color: #1e5494;
        }

        .thumb {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            background: #eee;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-active {
            background: #e6fffa;
            color: #2c7a7b;
            border: 1px solid #b2f5ea;
        }

        .badge-disabled {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .btn-edit {
            color: #1e5494;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-toggle {
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
        }

        .btn-delete {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <h1>Manage Tour Packages</h1>

        <form method="GET" action="admin_manage_packages.php" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Search title or category..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>

        <table class="package-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><img src="assets/img/<?php echo $row['image_url']; ?>" class="thumb" alt="trip"></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><span style="color: <?php echo ($row['category_name'] == 'General/None') ? '#999' : 'inherit'; ?>"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                            <td><?php echo number_format($row['price']); ?> EGP</td>
                            <td>
                                <?php if ($row['is_active'] == 1): ?>
                                    <span class="badge badge-active">Live</span>
                                <?php else: ?>
                                    <span class="badge badge-disabled">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin_edit_package.php?id=<?php echo $row['package_id']; ?>" class="btn-edit">Edit</a>
                                <a href="admin_manage_packages.php?toggle_id=<?php echo $row['package_id']; ?>" class="btn-toggle" style="color: <?php echo ($row['is_active'] == 1) ? '#e67e22' : '#2ecc71'; ?>;">
                                    <?php echo ($row['is_active'] == 1) ? 'Disable' : 'Enable'; ?>
                                </a>
                                <a href="admin_manage_packages.php?delete_id=<?php echo $row['package_id']; ?>" class="btn-delete" onclick="return confirm('Delete package permanently?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px;">No packages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>