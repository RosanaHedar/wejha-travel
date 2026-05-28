<?php
session_start();
include 'navbar.php';
include 'wegha_db.php';

$category_filter = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// --- NEW: Fetch current user's wishlist IDs to keep hearts red ---
$user_id = $_SESSION['user_id'] ?? 0;
$user_wishlist = [];
if ($user_id > 0) {
    $wish_sql = "SELECT package_id FROM wishlist WHERE user_id = $user_id";
    $wish_res = $conn->query($wish_sql);
    if ($wish_res) {
        while ($w = $wish_res->fetch_assoc()) {
            $user_wishlist[] = $w['package_id'];
        }
    }
}

// 1. Fetch Categories for the dropdown
$cat_sql = "SELECT * FROM categories ORDER BY name ASC";
$cat_result = $conn->query($cat_sql);

// 2. Fetch Packages
$sql = "SELECT p.*, IFNULL(c.name, 'General') AS category_name 
        FROM packages p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_active = 1";

if ($category_filter != '') {
    $sql .= " AND p.category_id = " . intval($category_filter);
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1e5494;
            --accent-orange: #f37021;
            --text-dark: #333;
            --heart-red: #e74c3c;
        }

        body {
            background: #fdfaf5;
            font-family: 'Segoe UI', sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .offers-banner {
            background: var(--primary-blue);
            color: white;
            padding: 30px;
            border-radius: 24px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-view-offers {
            background: var(--accent-orange);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: bold;
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

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .package-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            transition: 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            position: relative;
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

        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            color: #ccc;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
        }

        .wishlist-btn:hover,
        .wishlist-btn.active {
            color: var(--heart-red) !important;
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

    <main class="container">
        <div class="offers-banner">
            <div>
                <h2 style="margin:0;">🔥 Flash Deals & Special Offers</h2>
                <p style="margin: 5px 0 0;">Exclusive discounted Egyptian adventures!</p>
            </div>
            <a href="offers.php" class="btn-view-offers">View All Offers</a>
        </div>

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
            <button type="submit" style="background:var(--primary-blue); color:white; border:none; padding:10px 25px; border-radius:25px; cursor:pointer; font-weight:bold;">Filter</button>
        </form>

        <div class="packages-grid">
            <?php if ($pkg_result->num_rows > 0): ?>
                <?php while ($row = $pkg_result->fetch_assoc()): ?>
                    <?php
                    // Check if this specific package is in the user's wishlist
                    $is_liked = in_array($row['package_id'], $user_wishlist);
                    ?>
                    <div class="package-card">
                        <div class="card-header">
                            <span class="badge"><?php echo $row['category_name']; ?></span>

                            <a href="toggle_wishlist.php?package_id=<?php echo $row['package_id']; ?>"
                                class="wishlist-btn <?php echo $is_liked ? 'active' : ''; ?>">
                                <i class="<?php echo $is_liked ? 'fas' : 'far'; ?> fa-heart"></i>
                            </a>

                            <img src="assets/img/<?php echo !empty($row['image_url']) ? $row['image_url'] : 'placeholder.jpg'; ?>" alt="Package Image">
                        </div>

                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <div class="meta">
                                <span><i class="far fa-calendar-alt"></i> <?php echo $row['duration_days']; ?> Days</span>
                                <span><i class="fas fa-tag"></i> <?php echo $row['category_name']; ?></span>
                            </div>

                            <p style="color:#777; font-size: 0.9rem; height: 40px; overflow: hidden;">
                                <?php echo htmlspecialchars(substr($row['short_desc'], 0, 80)) . '...'; ?>
                            </p>

                            <div class="pricing">
                                <div>
                                    <small style="display:block; color:#aaa;">Starting from</small>
                                    <?php if (!empty($row['discount_price'])): ?>
                                        <span style="text-decoration: line-through; color: #bbb; font-size: 0.9rem;">
                                            <?php echo number_format($row['price']); ?>
                                        </span>
                                        <strong style="color: #e74c3c;"><?php echo number_format($row['discount_price']); ?> EGP</strong>
                                    <?php else: ?>
                                        <strong><?php echo number_format($row['price']); ?> EGP</strong>
                                    <?php endif; ?>
                                </div>
                                <a href="details.php?id=<?php echo $row['package_id']; ?>" class="book-btn">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>