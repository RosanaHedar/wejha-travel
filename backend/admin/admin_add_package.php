<?php
session_start();
include '../wegha_db.php'; // Correct relative folder path to find db connection up one level

// --- THE ADMIN SHIELD ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 1. Fetch Categories for the dropdown
$categories = $conn->query("SELECT * FROM categories");

$message = "";

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $short_desc = $_POST['short_desc'];
    $long_desc = $_POST['long_desc'];
    $price = $_POST['price'];
    // Capture the discount price (handles empty values as NULL)
    $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : NULL;
    $duration = $_POST['duration'];

    // --- IMAGE UPLOAD LOGIC ---
    $target_dir = "../assets/img/"; // FIXED: Navigates out of admin subfolder into the root asset pool
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    // Move file to assets/img/ folder
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // 3. Insert into Database (Updated to include discount_price column)
        $sql = "INSERT INTO packages (title, category_id, short_desc, long_desc, price, discount_price, duration_days, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        // bind_param types: s=string, i=int, d=double/decimal
        $stmt->bind_param("sissddis", $title, $category_id, $short_desc, $long_desc, $price, $discount_price, $duration, $image_name);

        if ($stmt->execute()) {
            $message = "<div style='background: #e6fffa; color: #2c7a7b; padding: 15px; border-radius: 12px; border: 1px solid #b2f5ea; margin-bottom: 25px; font-weight: 600;'>🎉 Package added successfully! It will appear in Offers if a discount was set.</div>";
        } else {
            $message = "<div style='background: #fff5f5; color: #c53030; padding: 15px; border-radius: 12px; border: 1px solid #feb2b2; margin-bottom: 25px; font-weight: 600;'>❌ Database error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div style='background: #fff5f5; color: #c53030; padding: 15px; border-radius: 12px; border: 1px solid #feb2b2; margin-bottom: 25px; font-weight: 600;'>❌ Error uploading image to the root folder.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Package | Wegha Admin</title>

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

        /* Highlight rule for the active add package link item */
        .sidebar a[href="admin_add_package.php"] {
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

        /* Unified Form Card Panel Styling */
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
            transition: all 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-blue);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(30, 84, 148, 0.1);
        }

        .price-row {
            display: flex;
            gap: 24px;
        }

        .price-row div {
            flex: 1;
        }

        button[type="submit"] {
            background: var(--primary-blue);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            font-size: 1.05rem;
            margin-top: 30px;
            box-shadow: 0 4px 6px -1px rgba(30, 84, 148, 0.2);
            transition: all 0.2s ease;
        }

        button[type="submit"]:hover {
            background: #153d6b;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -1px rgba(30, 84, 148, 0.3);
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
        <h1>Add New Travel Package</h1>
        <p class="subtitle">Expand the marketplace catalog by creating a localized tourism experience bundle:</p>

        <?php echo $message; ?>

        <div class="form-card">
            <form action="" method="POST" enctype="multipart/form-data">

                <label>Package Title</label>
                <input type="text" name="title" placeholder="e.g. Siwa Oasis Magic" required>

                <label>Category Group</label>
                <select name="category_id">
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <div class="price-row">
                    <div>
                        <label>Base Retail Price (EGP)</label>
                        <input type="number" name="price" step="0.01" placeholder="5000" required>
                    </div>
                    <div>
                        <label>Flash Deal Price (EGP) <small style="color: var(--text-muted); text-transform: none;">(Optional)</small></label>
                        <input type="number" name="discount_price" step="0.01" placeholder="Leave blank if no deal exists">
                    </div>
                </div>

                <label>Trip Duration (Days)</label>
                <input type="number" name="duration" placeholder="3" required>

                <label>Short Description (Card Teaser Summary)</label>
                <input type="text" name="short_desc" placeholder="Brief marketing catchphrase for previews..." required>

                <label>Full Comprehensive Itinerary</label>
                <textarea name="long_desc" rows="6" placeholder="Day 1: Arrival & Hotel Check-in&#10;Day 2: Great Sand Sea Safari Excursion..."></textarea>

                <label>Cover Showcase Photo</label>
                <input type="file" name="image" accept="image/*" required style="padding: 8px;">

                <button type="submit">Publish Live Package</button>
            </form>
        </div>
    </div>
</body>

</html>