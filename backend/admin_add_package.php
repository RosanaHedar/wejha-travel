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
    // NEW: Capture the discount price (handles empty values as NULL)
    $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : NULL;
    $duration = $_POST['duration'];

    // --- IMAGE UPLOAD LOGIC ---
    $target_dir = "assets/img/";
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
            $message = "<p style='color: green; font-weight: bold;'>Package added successfully! It will appear in Offers if a discount was set.</p>";
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
            font-family: 'Segoe UI', sans-serif;
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
            width: 100%;
            max-width: 800px;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
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
            font-size: 1rem;
        }

        .price-row {
            display: flex;
            gap: 20px;
        }

        .price-row div {
            flex: 1;
        }

        button {
            background: #1e5494;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            font-size: 1.1rem;
            margin-top: 20px;
            transition: 0.3s;
        }

        button:hover {
            background: #153d6b;
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
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <div class="price-row">
                    <div>
                        <label>Base Price (EGP)</label>
                        <input type="number" name="price" step="0.01" placeholder="5000" required>
                    </div>
                    <div>
                        <label>Discount Price (EGP) <small style="color: #e74c3c;">(Optional)</small></label>
                        <input type="number" name="discount_price" step="0.01" placeholder="Leave empty for no deal">
                    </div>
                </div>

                <label>Duration (Days)</label>
                <input type="number" name="duration" placeholder="3" required>

                <label>Short Description (Marketing Teaser)</label>
                <input type="text" name="short_desc" placeholder="Brief summary for the trip card..." required>

                <label>Full Itinerary (Day-by-Day)</label>
                <textarea name="long_desc" rows="5" placeholder="Day 1: Arrival... Day 2: Desert Safari..."></textarea>

                <label>Upload Hero Image</label>
                <input type="file" name="image" accept="image/*" required>

                <button type="submit">Publish Package</button>
            </form>
        </div>
    </div>
</body>

</html>