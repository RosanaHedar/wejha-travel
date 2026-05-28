<?php
session_start();
include 'wegha_db.php';

// 1. Get the Package ID safely
$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Fetch package details with category name
$sql = "SELECT p.*, c.name AS category_name 
        FROM packages p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.package_id = $package_id";

$result = $conn->query($sql);
$package = $result->fetch_assoc();

if (!$package) {
    die("Package not found!");
}

// Logic to determine current price and discount percentage
$has_discount = !empty($package['discount_price']) && $package['discount_price'] > 0;
$current_price = $has_discount ? $package['discount_price'] : $package['price'];
$discount_percentage = 0;
if ($has_discount && $package['price'] > 0) {
    $discount_percentage = round((($package['price'] - $package['discount_price']) / $package['price']) * 100);
}

// 3. NEW: Fetch approved reviews for this specific package bundle
$reviews_sql = "SELECT r.*, u.full_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.package_id = $package_id AND r.status = 'Approved' 
                ORDER BY r.created_at DESC";
$reviews_result = $conn->query($reviews_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($package['title']); ?> | Wegha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #fdfaf5;
            color: #333;
            line-height: 1.6;
        }

        nav {
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        nav a {
            text-decoration: none;
            color: #1e5494;
            font-weight: bold;
            margin-left: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            padding: 0 20px;
        }

        .main-hero {
            width: 100%;
            height: 450px;
            object-fit: cover;
            border-radius: 20px;
            margin-bottom: 30px;
        }

        .badge {
            background: #f37021;
            color: white;
            padding: 5px 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .discount-badge {
            background: #e74c3c;
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .itinerary-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .booking-sidebar {
            position: sticky;
            top: 40px;
            height: fit-content;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
        }

        .booking-sidebar h3 {
            margin-top: 0;
            color: #1e5494;
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            font-size: 0.9rem;
            color: #666;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 1rem;
        }

        .total-price-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            border: 1px dashed #1e5494;
        }

        .total-price-box span {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1e5494;
        }

        .btn-book {
            background: #1e5494;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-book:hover {
            background: #153d6b;
            transform: scale(1.02);
        }

        .btn-book:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .old-price {
            text-decoration: line-through;
            color: #888;
            font-size: 0.95rem;
            margin-right: 10px;
        }

        .new-price {
            color: #e74c3c;
            font-size: 1.3rem;
            font-weight: bold;
        }

        /* --- NEW REVIEW SYSTEM COMPONENTS STYLES --- */
        .reviews-section {
            margin-top: 40px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .review-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .review-user {
            font-weight: bold;
            color: #1e5494;
        }

        .review-stars {
            color: #f1c40f;
            font-size: 0.9rem;
        }

        .review-date {
            font-size: 0.8rem;
            color: #aaa;
            margin-left: 10px;
        }

        .review-text {
            color: #555;
            margin: 0;
            font-size: 0.95rem;
        }

        .form-review select,
        .form-review textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 1rem;
            margin-bottom: 15px;
            font-family: inherit;
            outline: none;
        }

        .form-review select:focus,
        .form-review textarea:focus {
            border-color: #1e5494;
        }

        .btn-review {
            background: #f37021;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn-review:hover {
            background: #d65d14;
        }
    </style>
</head>

<body>

    <nav>
        <a href="index.php" style="font-size: 1.5rem;">WIJHA وجهة</a>
        <div class="links">
            <a href="index.php">Home</a>
            <a href="bundles.php">Trips</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="itinerary-section">
            <span class="badge"><?php echo $package['category_name']; ?></span>
            <h1 style="font-size: 2.5rem; margin: 10px 0 25px;"><?php echo htmlspecialchars($package['title']); ?></h1>

            <img src="assets/img/<?php echo $package['image_url']; ?>" class="main-hero" alt="Trip Hero">

            <div class="itinerary-content">
                <h3>📜 Full Itinerary</h3>
                <p><?php echo nl2br(htmlspecialchars($package['long_desc'])); ?></p>
            </div>

            <div class="reviews-section">
                <h3><i class="fas fa-comments" style="color:#1e5494; margin-right:5px;"></i> Traveler Reviews</h3>

                <?php if ($reviews_result->num_rows > 0): ?>
                    <?php while ($rev = $reviews_result->fetch_assoc()): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <span class="review-user"><?php echo htmlspecialchars($rev['full_name']); ?></span>
                                    <span class="review-date"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                </div>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?php echo ($i <= $rev['rating']) ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="review-text">"<?php echo htmlspecialchars($rev['comment']); ?>"</p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#999; font-style:italic; padding:10px 0;">No reviews active for this itinerary yet. Be the first to share your experience!</p>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <hr style="margin: 30px 0; border:0; border-top:1px solid #eee;">
                    <h4 style="margin-top:0; color:#f37021;"><i class="fas fa-pen-nib"></i> Write an Adventure Review</h4>

                    <?php if (isset($_GET['review_msg']) && $_GET['review_msg'] == 'submitted'): ?>
                        <p style="color: green; font-weight: 500; background: #f0fff4; padding: 10px; border-radius: 8px; border: 1px solid #c6f6d5;">
                            ✅ Your review was submitted successfully! It is currently in queue awaiting admin approval.
                        </p>
                    <?php endif; ?>

                    <form action="submit_review.php" method="POST" class="form-review">
                        <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">

                        <label>Your Score Rating</label>
                        <select name="rating" required>
                            <option value="5">⭐⭐⭐⭐⭐ 5 - Exceptional Experience</option>
                            <option value="4">⭐⭐⭐⭐ 4 - Great Outing</option>
                            <option value="3">⭐⭐⭐ 3 - Good / Standard</option>
                            <option value="2">⭐⭐ 2 - Below Expectations</option>
                            <option value="1">⭐ 1 - Poor Experience</option>
                        </select>

                        <label>Share Your Testimonial Experience</label>
                        <textarea name="comment" rows="4" placeholder="What did you love about the destination? How was the pacing?" required></textarea>

                        <button type="submit" class="btn-review">Publish Review Request</button>
                    </form>
                <?php else: ?>
                    <p style="margin-top:30px; background:#f8f9fa; padding:12px; border-radius:10px; font-size:0.9rem; text-align:center; color:#666;">
                        🔑 Want to share feedback? Please <a href="login.php" style="color:#1e5494; font-weight:bold;">Log In</a> to submit verified reviews.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="booking-sidebar">
            <h3>Book This Trip</h3>

            <?php if ($has_discount): ?>
                <div class="discount-badge">SAVE <?php echo $discount_percentage; ?>% OFF</div>
                <p style="color: #888; margin-bottom: 5px;">
                    <span class="old-price"><?php echo number_format($package['price']); ?> EGP</span>
                    <span class="new-price"><?php echo number_format($package['discount_price']); ?> EGP</span>
                </p>
            <?php else: ?>
                <p style="color: #888;">Starting from <strong><?php echo number_format($package['price']); ?> EGP</strong> per person</p>
            <?php endif; ?>

            <form action="booking.php" method="POST" onsubmit="handleBooking(this)">
                <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">
                <input type="hidden" name="package_name" value="<?php echo htmlspecialchars($package['title']); ?>">
                <input type="hidden" name="image_url" value="<?php echo $package['image_url']; ?>">
                <input type="hidden" name="price" value="<?php echo $current_price; ?>">

                <label>Select Travel Date</label>
                <input type="date" name="travel_date" required min="<?php echo date('Y-m-d'); ?>">

                <label>Number of Travelers</label>
                <input type="number" name="num_travelers" id="num_travelers" value="1" min="1" required oninput="calculateTotal()">

                <div class="total-price-box">
                    <small>Total Estimated Price</small><br>
                    <span id="display_total"><?php echo number_format($current_price); ?></span> <strong>EGP</strong>
                </div>

                <button type="submit" id="submit-btn" class="btn-book">Confirm My Trip</button>
            </form>
        </div>
    </div>

    <script>
        function calculateTotal() {
            const price = <?php echo $current_price; ?>;
            const travelers = document.getElementById('num_travelers').value;
            const total = price * (travelers > 0 ? travelers : 0);
            document.getElementById('display_total').innerText = total.toLocaleString();
        }

        function handleBooking(form) {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '⌛ Processing Order...';
        }
    </script>

</body>

</html>