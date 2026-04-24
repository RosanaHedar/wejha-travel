<?php
session_start();
include 'wegha_db.php'; //

// Get the ID from the URL
$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch package details with category name
$sql = "SELECT p.*, c.name AS category_name 
        FROM packages p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.package_id = $package_id";

$result = $conn->query($sql);
$package = $result->fetch_assoc();

if (!$package) {
    die("Package not found!");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $package['title']; ?> | Wegha</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #fdfaf5;
            color: #333;
            line-height: 1.6;
        }

        /* Navbar Styling */
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

        /* Left Side: Itinerary */
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

        .itinerary-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        /* Right Side: Sticky Booking Form */
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
            <h1 style="font-size: 2.5rem; margin: 10px 0 25px;"><?php echo $package['title']; ?></h1>

            <img src="assets/img/<?php echo $package['image_url']; ?>" class="main-hero">
            <div class="itinerary-content">
                <h3>📜 Full Itinerary</h3>
                <p><?php echo nl2br(htmlspecialchars($package['long_desc'])); ?></p>
            </div>
        </div>

        <div class="booking-sidebar">
            <h3>Book This Trip</h3>
            <p style="color: #888;">Starting from <strong><?php echo number_format($package['price']); ?> EGP</strong> per person</p>

            <form action="booking.php" method="POST"> <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">
                <input type="hidden" name="price" value="<?php echo $package['price']; ?>">

                <label>Select Travel Date</label>
                <input type="date" name="travel_date" required min="<?php echo date('Y-m-d'); ?>">

                <label>Number of Travelers</label>
                <input type="number" name="num_travelers" id="num_travelers" value="1" min="1" required oninput="calculateTotal()">

                <div class="total-price-box">
                    <small>Total Estimated Price</small><br>
                    <span id="display_total"><?php echo number_format($package['price']); ?></span> <strong>EGP</strong>
                </div>

                <button type="submit" class="btn-book">Proceed to Payment</button>
            </form>
        </div>
    </div>

    <script>
        function calculateTotal() {
            const price = <?php echo $package['price']; ?>;
            const travelers = document.getElementById('num_travelers').value;
            const total = price * travelers;
            document.getElementById('display_total').innerText = total.toLocaleString();
        }
    </script>

</body>

</html>