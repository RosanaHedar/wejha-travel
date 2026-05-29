<?php
session_start();
include '../wegha_db.php';

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
        // FIXED: Added "../" so the server finds the image file up one level in the root assets directory
        $file_path = "../assets/img/" . $img_data['image_url'];
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

// UPDATED SQL: Using LEFT JOIN and IFNULL to show all packages
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages | Wegha Admin</title>
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

        /* Highlight rule for the active manage packages navigation item */
        .sidebar a[href="admin_manage_packages.php"] {
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

        /* Unified Table Structural Matrix Design */
        .package-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #edf2f7;
        }

        .package-table th,
        .package-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .package-table th {
            background: #f8fafc;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }

        .package-table td {
            font-size: 0.95rem;
        }

        .thumb {
            width: 80px;
            height: 52px;
            object-fit: cover;
            border-radius: 8px;
            background: #f1f5f9;
            border: 1px solid #edf2f7;
        }

        /* Unified Status Badges Styling Lookheets */
        .badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .badge-active {
            background: #e6fffa;
            color: #047857;
            border: 1px solid #b2f5ea;
        }

        .badge-disabled {
            background: #fff5f5;
            color: #b91c1c;
            border: 1px solid #feb2b2;
        }

        /* Action elements layout mappings styling links */
        .btn-edit {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .btn-toggle {
            text-decoration: none;
            font-weight: 700;
            margin-left: 12px;
            font-size: 0.85rem;
        }

        .btn-delete {
            color: #ef4444;
            text-decoration: none;
            font-weight: 700;
            margin-left: 12px;
            font-size: 0.85rem;
        }

        .btn-edit:hover,
        .btn-toggle:hover,
        .btn-delete:hover {
            text-decoration: underline;
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
        <h1>Manage Tour Packages</h1>
        <p class="subtitle">Administer active travel options, modify itineraries, or configure discount tags:</p>

        <div class="card">
            <form method="GET" action="" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search title or category group..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <td><img src="../assets/img/<?php echo $row['image_url']; ?>" class="thumb" alt="trip"></td>
                                <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                <td><span style="color: <?php echo ($row['category_name'] == 'General/None') ? '#94a3b8' : 'inherit'; ?>; font-weight: 500;"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                                <td style="font-weight: 600; color: var(--primary-blue);"><?php echo number_format($row['price']); ?> EGP</td>
                                <td>
                                    <?php if ($row['is_active'] == 1): ?>
                                        <span class="badge badge-active">Live</span>
                                    <?php else: ?>
                                        <span class="badge badge-disabled">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="admin_edit_package.php?id=<?php echo $row['package_id']; ?>" class="btn-edit">Edit</a>
                                    <a href="admin_manage_packages.php?toggle_id=<?php echo $row['package_id']; ?>" class="btn-toggle" style="color: <?php echo ($row['is_active'] == 1) ? '#ea580c' : '#16a34a'; ?>;">
                                        <?php echo ($row['is_active'] == 1) ? 'Disable' : 'Enable'; ?>
                                    </a>
                                    <a href="admin_manage_packages.php?delete_id=<?php echo $row['package_id']; ?>" class="btn-delete" onclick="return confirm('Delete package permanently along with local file parameters?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted); font-weight: 500;">No packages matching criteria found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>