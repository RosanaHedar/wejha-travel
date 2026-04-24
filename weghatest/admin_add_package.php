<?php
session_start();
include 'wegha_db.php';

// --- ADMIN SHIELD ---
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
    $duration = $_POST['duration'];

    // --- IMAGE UPLOAD LOGIC ---
    $target_dir = "assets/img/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    $upload_ok = true;

    // Move file to assets/img/ folder
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // 3. Insert into Database
        $stmt = $conn->prepare("INSERT INTO packages (title, category_id, short_desc, long_desc, price, duration_days, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissdis", $title, $category_id, $short_desc, $long_desc, $price, $duration, $image_name);

        if ($stmt->execute()) {
            $message = "<p style='color: green;'>Package added successfully!</p>";
        } else {
            $message = "<p style='color: red;'>Database error: " . $conn->error . "</p>";
        }
    } else {
        $message = "<p style='color: red;'>Error uploading image.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Package | Wegha Admin</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #1e5494;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 5px;
        }

        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: 100%;
            max-width: 800px;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background: #1e5494;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <h1>Add New Travel Package</h1>
        <?php echo $message; ?>

        <div class="form-card">
            <form action="admin_add_package.php" method="POST" enctype="multipart/form-data">

                <label>Package Title</label>
                <input type="text" name="title" placeholder="e.g. Siwa Oasis Magic" required>

                <label>Category</label>
                <select name="category_id">
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Price (EGP)</label>
                <input type="number" name="price" placeholder="5000" required>

                <label>Duration (Days)</label>
                <input type="number" name="duration" placeholder="3" required>

                <label>Short Description (For Cards)</label>
                <input type="text" name="short_desc" placeholder="Brief summary of the trip..." required>

                <label>Full Itinerary (Detailed)</label>
                <textarea name="long_desc" rows="5" placeholder="Day 1, Day 2, Day 3..."></textarea>

                <label>Package Image</label>
                <input type="file" name="image" accept="image/*" required>

                <button type="submit">Publish Package</button>
            </form>
        </div>
    </div>

</body>

</html>