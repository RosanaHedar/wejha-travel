<?php
session_start();
include 'wegha_db.php';

/**
 * SQL EXPLANATION: 
 * We use UNION to combine:
 * 1. Data from the 'offers' table
 * 2. Data from 'packages' where a discount_price is present
 * We use IFNULL for categories to ensure the tag never looks empty.
 */
$query = "
    (SELECT 
        package_id, title, description, image_url, 
        original_price, new_price, category, 'special' as offer_type 
     FROM offers 
     WHERE is_active = 1)
    UNION
    (SELECT 
        p.package_id, p.title, p.short_desc as description, 
        CONCAT('assets/img/', p.image_url) as image_url, 
        p.price as original_price, p.discount_price as new_price, 
        IFNULL(c.name, 'Trip') as category, 'discount' as offer_type 
     FROM packages p 
     LEFT JOIN categories c ON p.category_id = c.category_id 
     WHERE p.discount_price IS NOT NULL AND p.discount_price > 0 AND p.is_active = 1)
    ORDER BY new_price ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Deals & Offers | WIJHA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e5494;
            --accent: #f37021;
            --bg: #fdfaf5;
            --red: #e74c3c;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin: 0;
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .offer-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            transition: 0.3s;
            position: relative;
        }

        .offer-card:hover {
            transform: translateY(-5px);
        }

        .image-container {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Badges */
        .promo-tag {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .perc-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--red);
            color: white;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .content h3 {
            margin: 0 0 10px;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .content p {
            font-size: 0.9rem;
            color: #666;
            flex-grow: 1;
            margin-bottom: 20px;
        }

        .price-box {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 15px;
        }

        .old {
            text-decoration: line-through;
            color: #bbb;
            font-size: 0.9rem;
        }

        .new {
            color: var(--red);
            font-weight: bold;
            font-size: 1.4rem;
        }

        .view-btn {
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            transition: 0.3s;
        }

        .view-btn:hover {
            background: #153d6b;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="header">
            <h1>Current Deals</h1>
            <p>Grab these discounted Egyptian trips before they expire!</p>
        </div>

        <div class="offers-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    // Calculate Discount Percentage
                    $perc = 0;
                    if ($row['original_price'] > 0) {
                        $perc = round((($row['original_price'] - $row['new_price']) / $row['original_price']) * 100);
                    }
                ?>
                    <div class="offer-card">
                        <div class="image-container" style="background-image: url('<?php echo $row['image_url']; ?>');">
                            <span class="promo-tag"><?php echo htmlspecialchars($row['category']); ?> Deal</span>
                            <?php if ($perc > 0): ?>
                                <span class="perc-badge">-<?php echo $perc; ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="content">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</p>

                            <div class="price-box">
                                <span class="old"><?php echo number_format($row['original_price']); ?> EGP</span>
                                <span class="new"><?php echo number_format($row['new_price']); ?> EGP</span>
                            </div>

                            <a href="details.php?id=<?php echo $row['package_id']; ?>" class="view-btn">View Full Deal</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%; color: #888;">No active deals at the moment. Check back soon!</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>