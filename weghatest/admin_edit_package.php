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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $cat_id = $_POST['category_id'];
    $short = $_POST['short_desc'];
    $long = $_POST['long_desc'];
    $price = $_POST['price'];
    $days = $_POST['duration'];
    $active = isset($_POST['is_active']) ? 1 : 0;
    $image_name = $package['image_url'];

    if (!empty($_FILES["image"]["name"])) {
        $image_name = basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "assets/img/" . $image_name);
    }

    $sql = "UPDATE packages SET 
            title=?, category_id=?, short_desc=?, long_desc=?, 
            price=?, duration_days=?, image_url=?, is_active=? 
            WHERE package_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissdisii", $title, $cat_id, $short, $long, $price, $days, $image_name, $active, $id);

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
    <title>Edit Package</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f7f6;
            padding: 40px;
        }

        .form-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
        }

        .checkbox-group {
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            background: #1e5494;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Edit Package</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" value="<?php echo $package['title']; ?>" required>

            <select name="category_id">
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php if ($cat['category_id'] == $package['category_id']) echo 'selected'; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="price" value="<?php echo $package['price']; ?>">
            <input type="number" name="duration" value="<?php echo $package['duration_days']; ?>">
            <textarea name="short_desc"><?php echo $package['short_desc']; ?></textarea>
            <textarea name="long_desc"><?php echo $package['long_description']; ?></textarea>

            <div class="checkbox-group">
                <input type="checkbox" name="is_active" style="width:auto;" <?php if ($package['is_active']) echo 'checked'; ?>>
                <label>Package is Active (Visible to Public)</label>
            </div>

            <p>Current Image: <?php echo $package['image_url']; ?></p>
            <input type="file" name="image">

            <button type="submit" class="btn">Update Changes</button>
        </form>
    </div>
</body>

</html>