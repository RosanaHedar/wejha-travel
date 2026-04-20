<?php
session_start();
include 'wegha_db.php';

// 1. Handle Filtering: Get the selected category from the URL
$category_filter = isset($_GET['category_id']) ? $_GET['category_id'] : '';

// 2. Fetch Categories for the Dropdown
$cat_sql = "SELECT * FROM categories";
$cat_result = $conn->query($cat_sql);

// 3. Fetch Packages (Filtered or All)
$sql = "SELECT p.*, c.name AS category_name 
        FROM packages p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_active = 1";

if ($category_filter != '') {
    $sql .= " AND p.category_id = " . intval($category_filter);
}

$pkg_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Explore Packages | Wijha</title>
    <style>
        :root {
            --primary-blue: #1e5494;
            --accent-orange: #f37021;
            --glass-bg: rgba(255, 255, 255, 0.4);
            --text-dark: #333;
        }

        body {
            background: #fdfaf5;
            /* Creamy background from your mockup */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Dropdown Styling */
        .filter-form {
            margin-bottom: 40px;
            text-align: center;
        }

        select {
            padding: 12px 25px;
            border-radius: 25px;
            border: 1px solid #ddd;
            background: white;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Grid Layout */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        /* Glassmorphism Card */
        .package-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            overflow: hidden;
            transition: transform 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .package-card:hover {
            transform: translateY(-10px);
        }

        .card-header {
            position: relative;
            height: 200px;
        }

        .card-header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent-orange);
            color: white;
            padding: 5px 15px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .card-content {
            padding: 25px;
        }

        .card-content h3 {
            margin: 0 0 10px 0;
            color: var(--primary-blue);
        }

        .meta {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
        }

        .itinerary-preview {
            font-size: 0.85rem;
            line-height: 1.6;
            color: #555;
            margin-bottom: 20px;
        }

        .pricing {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding-top: 20px;
        }

        .pricing strong {
            font-size: 1.4rem;
            color: var(--primary-blue);
        }

        .book-btn {
            background: var(--primary-blue);
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 12px;
            font-weight: bold;
            transition: 0.3s;
        }

        .book-btn:hover {
            background: #153d6b;
            box-shadow: 0 5px 15px rgba(30, 84, 148, 0.3);
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">WIJHA وجهة</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="explore.php" class="active">Explore</a>
            <a href="bundles.php">Bundles</a>
        </nav>
    </header>

    <main class="container">
        <h2>Curated travel packages for every type of traveler</h2>

        <form action="explore.php" method="GET" class="filter-form">
            <select name="category_id" onchange="this.form.submit()">
                <option value="">All Packages</option>
                <?php while ($cat = $cat_result->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <div class="packages-grid">
            <?php if ($pkg_result->num_rows > 0): ?>
                <?php while ($row = $pkg_result->fetch_assoc()): ?>
                    <div class="package-card">
                        <div class="card-header">
                            <span class="badge"><?php echo $row['category_name']; ?></span>
                            <button class="wishlist-btn">❤</button>
                            <img src="assets/img/<?php echo $row['image_url']; ?>" alt="Package Image">
                        </div>

                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <div class="meta">
                                <span>📅 <?php echo $row['duration_days']; ?> Days / <?php echo $row['duration_days'] - 1; ?> Nights</span>
                                <span>👥 <?php echo $row['category_name']; ?></span>
                            </div>

                            <p class="itinerary-preview">
                                <strong>Itinerary Preview:</strong><br>
                                <?php echo nl2br(htmlspecialchars($row['short_desc'])); ?>
                            </p>

                            <div class="pricing">
                                <small>Starting from</small>
                                <strong><?php echo number_format($row['price']); ?> EGP</strong>
                                <a href="details.php?id=<?php echo $row['package_id']; ?>" class="book-btn">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No packages found in this category.</p>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>