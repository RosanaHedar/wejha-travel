<?php
session_start();
include '../wegha_db.php';

// --- ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. Fetch current data
$id = intval($_GET['id']);
$package = $conn->query("SELECT * FROM packages WHERE package_id = $id")->fetch_assoc();

if (!$package) {
    die("Package not found!");
}

$message = "";

// 2. Handle the Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $cat_id = $_POST['category_id'];
    $short = $_POST['short_desc'];
    $long = $_POST['long_desc'];
    $price = $_POST['price'];
    $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : NULL;
    $days = $_POST['duration'];
    $active = isset($_POST['is_active']) ? 1 : 0;
    $image_name = $package['image_url'];

    if (!empty($_FILES["image"]["name"])) {
        $image_name = basename($_FILES["image"]["name"]);
        // FIXED: Adjusted target path destination directory parameters up one level safely
        move_uploaded_file($_FILES["image"]["tmp_name"], "../assets/img/" . $image_name);
    }

    // Updated SQL to include discount_price
    $sql = "UPDATE packages SET 
            title=?, category_id=?, short_desc=?, long_desc=?, 
            price=?, discount_price=?, duration_days=?, image_url=?, is_active=? 
            WHERE package_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissddisii", $title, $cat_id, $short, $long, $price, $discount_price, $days, $image_name, $active, $id);

    if ($stmt->execute()) {
        header("Location: admin_manage_packages.php?msg=updated");
        exit();
    } else {
        $message = "<div class='msg error'>Database execution update error: " . $conn->error . "</div>";
    }
}

$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package | Wegha Admin</title>
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

        /* Highlight rule maps edit package under its contextual manage packages parent domain link path selection */
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

        /* Unified Card Configuration Blueprint Layout */
        .form-card {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            border: 1px solid #edf2f7;
            max-width: 850px;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: 700;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            margin: 8px 0 0 0;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 0.95rem;
            color: var(--text-main);
            background-color: #fbfcfd;
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-blue);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(30, 84, 148, 0.1);
        }

        .price-group {
            display: flex;
            gap: 24px;
        }

        .price-group div {
            flex: 1;
        }

        /* Checkbox Layout State Block Modifier */
        .checkbox-group {
            margin: 24px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 10px;
            border: 1px solid #edf2f7;
        }

        .checkbox-group label {
            margin: 0;
            text-transform: none;
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.95rem;
            letter-spacing: 0;
            cursor: pointer;
        }

        .img-preview {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 6px;
            font-weight: 500;
            background-color: #f1f5f9;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
        }

        .btn-submit {
            background: var(--primary-blue);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            font-size: 1.05rem;
            margin-top: 15px;
            box-shadow: 0 4px 6px -1px rgba(30, 84, 148, 0.2);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: #153d6b;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -1px rgba(30, 84, 148, 0.3);
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .btn-cancel:hover {
            color: #ef4444;
            text-decoration: underline;
        }

        .msg.error {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 0.95rem;
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
        <h1>Modify Marketplace Listing</h1>
        <p class="subtitle">Alter internal itinerary parameters, update pricing matrices, or visibility states:</p>

        <?php echo $message; ?>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data" action="">

                <label>Package Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($package['title']); ?>" placeholder="e.g. Giza Pyramids Tour" required>

                <label>Category Group</label>
                <select name="category_id">
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if ($cat['category_id'] == $package['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <div class="price-group">
                    <div>
                        <label>Original Price (EGP)</label>
                        <input type="number" name="price" value="<?php echo $package['price']; ?>" step="0.01" required>
                    </div>
                    <div>
                        <label>Discounted Price (EGP) <small style="color: var(--text-muted); text-transform: none;">(Optional)</small></label>
                        <input type="number" name="discount_price" value="<?php echo $package['discount_price']; ?>" step="0.01" placeholder="Leave empty for no discount">
                    </div>
                </div>

                <label>Duration (Number of Days)</label>
                <input type="number" name="duration" value="<?php echo $package['duration_days']; ?>" required>

                <label>Short Summary (For Marketplace Previews)</label>
                <textarea name="short_desc" rows="2" placeholder="Brief marketing teaser..."><?php echo htmlspecialchars($package['short_desc']); ?></textarea>

                <label>Full Comprehensive Itinerary</label>
                <textarea name="long_desc" rows="6" placeholder="Detailed day-by-day plan..."><?php echo htmlspecialchars($package['long_desc']); ?></textarea>

                <div class="checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" style="width:auto; margin:0;" <?php if ($package['is_active']) echo 'checked'; ?>>
                    <label Lothar for="is_active">Publish package on the user-facing website platform index</label>
                </div>

                <label>Replace Showcase Image</label>
                <input type="file" name="image" accept="image/*" style="padding: 8px;">
                <div class="img-preview"><i class="fas fa-image"></i> Active Image Blueprint Reference: <strong><?php echo htmlspecialchars($package['image_url']); ?></strong></div>

                <button type="submit" class="btn-submit">Save Consolidated Changes</button>
                <a href="admin_manage_packages.php" class="btn-cancel">Discard and Cancel</a>
            </form>
        </div>
    </div>

</body>

</html>