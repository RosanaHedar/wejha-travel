<?php
session_start();
include 'wegha_db.php';

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

// 2. Handle the Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $cat_id = $_POST['category_id'];
    $short = $_POST['short_desc'];
    $long = $_POST['long_desc'];
    $price = $_POST['price'];
    $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : NULL; // NEW: Discount Logic
    $days = $_POST['duration'];
    $active = isset($_POST['is_active']) ? 1 : 0;
    $image_name = $package['image_url'];

    if (!empty($_FILES["image"]["name"])) {
        $image_name = basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "assets/img/" . $image_name);
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
    }
}

$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Package | Wegha Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            background: #f4f7f6;
        }

        /* Stable Sidebar */
        .sidebar {
            width: 250px;
            background: #1e5494;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
            box-sizing: border-box;
        }

        .form-container {
            max-width: 700px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        h2 {
            color: #1e5494;
            margin-top: 0;
            border-bottom: 2px solid #f4f7f6;
            padding-bottom: 10px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
            font-size: 0.9rem;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin: 5px 0 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .price-group {
            display: flex;
            gap: 20px;
        }

        .price-group div {
            flex: 1;
        }

        .checkbox-group {
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }

        .btn {
            background: #1e5494;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn:hover {
            background: #153d6b;
        }

        .img-preview {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="form-container">
            <h2>Edit Package: <?php echo htmlspecialchars($package['title']); ?></h2>

            <form method="POST" enctype="multipart/form-data">

                <label>Package Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($package['title']); ?>" placeholder="e.g. Giza Pyramids Tour" required>

                <label>Category</label>
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
                        <label>Discounted Price (EGP) <small style="color:orange;">(Optional)</small></label>
                        <input type="number" name="discount_price" value="<?php echo $package['discount_price']; ?>" step="0.01" placeholder="Leave empty for no discount">
                    </div>
                </div>

                <label>Duration (Number of Days)</label>
                <input type="number" name="duration" value="<?php echo $package['duration_days']; ?>" required>

                <label>Short Summary (For Cards)</label>
                <textarea name="short_desc" rows="2" placeholder="Brief marketing teaser..."><?php echo htmlspecialchars($package['short_desc']); ?></textarea>

                <label>Full Itinerary</label>
                <textarea name="long_desc" rows="5" placeholder="Detailed day-by-day plan..."><?php echo htmlspecialchars($package['long_desc']); ?></textarea>

                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" style="width:auto;" <?php if ($package['is_active']) echo 'checked'; ?>>
                    <label style="margin:0;">Show this package on the website</label>
                </div>

                <label>Package Image</label>
                <div class="img-preview">Current file: <?php echo htmlspecialchars($package['image_url']); ?></div>
                <input type="file" name="image" accept="image/*">

                <button type="submit" class="btn">Save Changes</button>
                <a href="admin_manage_packages.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none; font-size:0.9rem;">Cancel and Go Back</a>
            </form>
        </div>
    </div>

</body>

</html>