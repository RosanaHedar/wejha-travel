<?php
include 'wegha_db.php';

// Get the ID from the URL
$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch only this specific package
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

<div class="details-container">
    <div class="glass-header">
        <h1><?php echo $package['title']; ?></h1>
        <span class="badge"><?php echo $package['category_name']; ?></span>
    </div>

    <img src="assets/img/<?php echo $package['image_url']; ?>" class="main-hero">

    <div class="content">
        <h3>Full Itinerary</h3>
        <p><?php echo nl2br(htmlspecialchars($package['long_desc'])); ?></p>

        <div class="booking-box">
            <span>Price: <?php echo number_format($package['price']); ?> EGP</span>
            <form action="booking.php" method="POST">
                <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">
                <button type="submit" class="btn-confirm">Proceed to Booking</button>
            </form>
        </div>
    </div>
</div>
<form action="booking.php" method="POST">
    <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">
    <input type="hidden" name="price" value="<?php echo $package['price']; ?>">

    <label>Travel Date:</label>
    <input type="date" name="travel_date" required>

    <label>Number of Travelers:</label>
    <input type="number" name="num_travelers" value="1" min="1" required>

    <button type="submit" class="book-now-btn">Confirm My Trip</button>
</form>