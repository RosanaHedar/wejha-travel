<?php
session_start();
include 'wegha_db.php'; //

// 1. Handle Filtering: Get category and search keyword from the URL
$category_filter = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// 2. Fetch Categories for the Dropdown
$cat_sql = "SELECT * FROM categories ORDER BY name ASC";
$cat_result = $conn->query($cat_sql);

// 3. Fetch Packages (Filtered or All)
$sql = "SELECT p.*, c.name AS category_name 
        FROM packages p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_active = 1"; //

// Add Category Filter
if ($category_filter != '') {
    $sql .= " AND p.category_id = " . intval($category_filter);
}

// Add Search Keyword Filter
if ($keyword != '') {
    $safe_keyword = $conn->real_escape_string($keyword);
    $sql .= " AND (p.title LIKE '%$safe_keyword%' OR p.short_desc LIKE '%$safe_keyword%')";
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 20px;
        }

        /* Navbar Styling */
        header {
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: -20px -20px 40px -20px;
        }

        header .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-blue);
            text-decoration: none;
        }

        header nav a {
            text-decoration: none;
            color: var(--primary-blue);
            font-weight: bold;
            margin-left: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .filter-form {
            margin-bottom: 40px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        select,
        .search-input {
            padding: 12px 25px;
            border-radius: 25px;
            border: 1px solid #ddd;
            background: white;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Grid & Card Layout */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .package-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
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
        }
    </style>
</head>

<body>

    <header>
        <a href="index.php" class="logo">WIJHA وجهة</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="bundles.php">Bundles</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <h2 style="text-align:center;">Curated travel packages for you</h2>

        <form action="bundles.php" method="GET" class="filter-form">
            <input type="text" name="keyword" class="search-input" placeholder="Search trips..." value="<?php echo htmlspecialchars($keyword); ?>">

            <select name="category_id" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php while ($cat = $cat_result->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($category_filter == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" style="background:var(--primary-blue); color:white; border:none; padding:10px 20px; border-radius:25px; cursor:pointer;">Filter</button>
            <?php if ($keyword != '' || $category_filter != ''): ?>
                <a href="bundles.php" style="align-self:center; color:#666; text-decoration:none;">Clear</a>
            <?php endif; ?>
        </form>

        <div class="packages-grid">
            <?php if ($pkg_result->num_rows > 0): ?>
                <?php while ($row = $pkg_result->fetch_assoc()): ?>
                    <div class="package-card">
                        <div class="card-header">
                            <span class="badge"><?php echo $row['category_name']; ?></span>
                            <img src="assets/img/<?php echo $row['image_url']; ?>" alt="Package Image">
                        </div>

                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <div class="meta">
                                <span>📅 <?php echo $row['duration_days']; ?> Days</span>
                                <span>👥 <?php echo $row['category_name']; ?></span>
                            </div>

                            <p class="itinerary-preview">
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
                <div style="text-align:center; width:100%; grid-column: 1 / -1; padding: 50px;">
                    <h3>No packages found matching your criteria.</h3>
                    <p>Try searching for something else or clearing your filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>