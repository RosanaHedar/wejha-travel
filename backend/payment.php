<?php
session_start();
include 'wegha_db.php';

// Turn off warnings for a cleaner UI if testing
error_reporting(E_ALL ^ E_WARNING);

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 2; // Testing fallback
}
$user_id = $_SESSION['user_id'];

// Fetch user data for the cardholder name field
$user_query = "SELECT full_name, IFNULL(card_number, '**** **** **** 3456') as card_number FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
$current_user_name = $user_data['full_name'] ?? 'Guest';

// --- CHECK FOR CUSTOM PACKAGE ROUTE ---
$is_custom = (isset($_GET['type']) && $_GET['type'] === 'custom' && isset($_GET['id']));
$custom_id = $is_custom ? intval($_GET['id']) : 0;

$services = [];
$total_payment = 0;

if ($is_custom) {
    // 1A. CUSTOM DATA LOGIC: Pull from custom package lookup tables
    $pkg_res = $conn->query("SELECT * FROM customer_packages WHERE customer_package_id = $custom_id AND user_id = '$user_id'");

    if ($pkg = $pkg_res->fetch_assoc()) {
        $total_payment = $pkg['total_price'];

        // Construct an itemized description string from chosen services
        $items_res = $conn->query("
            SELECT s.service_name, cps.quantity 
            FROM customer_package_services cps
            JOIN services s ON cps.service_id = s.service_id
            WHERE cps.customer_package_id = $custom_id
        ");

        $details_str = "";
        while ($item = $items_res->fetch_assoc()) {
            $details_str .= "• " . $item['quantity'] . "x " . $item['service_name'] . "<br>";
        }

        $services[] = [
            'name'     => 'Custom Tailored Adventure',
            'image'    => 'default.jpg',
            'desc'     => !empty($details_str) ? $details_str : 'Custom itinerary configuration',
            'price'    => $total_payment,
            'savings'  => 0
        ];
    } else {
        $services[] = ['name' => 'Custom Trip Error', 'image' => 'default.jpg', 'desc' => 'Could not load custom package details.', 'price' => 0, 'savings' => 0];
    }
} else {
    // 1B. STANDARD BUNDLE LOGIC: Your original session structure
    if (isset($_SESSION['last_booking'])) {
        $b = $_SESSION['last_booking'];

        $p_unit = isset($b['price_unit']) ? $b['price_unit'] : $b['total'];
        $o_unit = isset($b['original_unit']) ? $b['original_unit'] : $p_unit;

        $perc_saved = ($o_unit > $p_unit) ? round((($o_unit - $p_unit) / $o_unit) * 100) : 0;

        $services[] = [
            'name'     => $b['name'],
            'image'    => $b['image'],
            'desc'     => 'Date: ' . ($b['date'] ?? 'TBD') . ' | Travelers: ' . ($b['travelers'] ?? 1),
            'price'    => $b['total'],
            'savings'  => $perc_saved
        ];
    } else {
        $services[] = ['name' => 'Custom Trip', 'image' => 'default.jpg', 'desc' => 'Selected Package', 'price' => 0, 'savings' => 0];
    }
    $total_payment = $services[0]['price'];
}

$step = isset($_POST['next_step']) ? intval($_POST['next_step']) : 1;

// 2. FINAL ORDER LOGIC
if (isset($_POST['confirm_order'])) {
    $method = $_POST['payment_method'];

    // Log payment details to historical records
    $sql = "INSERT INTO payments (user_id, card_name, amount, payment_method) 
            VALUES ('$user_id', '$current_user_name', '$total_payment', '$method')";

    if (mysqli_query($conn, $sql)) {
        // Create the booking entry so it shows up in your Admin Analytics Panel
        if ($is_custom) {
            $booking_sql = "INSERT INTO bookings (user_id, package_id, custom_package_id, total_price, status, booking_date) 
                            VALUES ('$user_id', NULL, '$custom_id', '$total_payment', 'Pending', NOW())";
            mysqli_query($conn, $booking_sql);
        }
        $step = 3;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment | WIJHA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e5494;
            --accent: #e74c3c;
            --bg: #fdfaf7;
            --text: #2d2d2d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f9f5f0;
            margin: 0;
            color: var(--text);
        }

        .iphone-container {
            max-width: 400px;
            margin: 40px auto;
            background: white;
            min-height: 800px;
            border-radius: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 10px;
        }

        .back-btn {
            background: #f5f5f5;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            cursor: pointer;
        }

        .item-card {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 20px;
            border: 1px solid #eee;
            position: relative;
        }

        .item-img {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            background: #eee;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }

        .item-info {
            flex-grow: 1;
        }

        .item-info h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
        }

        .billing {
            background: #fff;
            border-radius: 25px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
        }

        .bill-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }

        .total {
            font-weight: 600;
            color: #000;
            font-size: 18px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .btn-primary {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        .method-box {
            border: 2px solid #f0f0f0;
            border-radius: 20px;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .method-box.active {
            border-color: var(--primary);
            background: #f0f4f8;
        }

        .visa-details {
            display: none;
            padding: 15px;
            background: #fdfdfd;
            border: 2px solid #f0f0f0;
            border-top: none;
            border-radius: 0 0 20px 20px;
            margin-top: -15px;
            margin-bottom: 15px;
        }

        .visa-details.show {
            display: block;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            font-size: 11px;
            color: #888;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .input-group input {
            width: 100%;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 10px;
            font-size: 14px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="iphone-container">
        <form method="POST" id="paymentForm" action="payment.php?<?php echo $_SERVER['QUERY_STRING']; ?>">

            <?php if ($step == 1): ?>
                <div class="header">
                    <button type="button" class="back-btn" onclick="history.back()"><i class="fas fa-chevron-left"></i></button>
                    <h3>Trip Summary</h3>
                    <div style="width:40px"></div>
                </div>

                <?php foreach ($services as $s): ?>
                    <div class="item-card">
                        <div class="item-img" style="background-image: url('assets/img/<?php echo $s['image']; ?>');"></div>
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($s['name']); ?></h4>
                            <p style="font-size: 13px; color: #666; line-height: 1.5; margin: 5px 0 10px 0;"><?php echo $s['desc']; ?></p>
                            <strong><?php echo number_format($s['price'], 0); ?> EGP</strong>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="billing">
                    <div class="bill-row"><span>Subtotal</span><span><?php echo number_format($total_payment, 0); ?> EGP</span></div>
                    <div class="bill-row total"><span>Total Payment</span><span><?php echo number_format($total_payment, 0); ?> EGP</span></div>
                </div>
                <input type="hidden" name="next_step" value="2">
                <button class="btn-primary">Next: Payment Method</button>

            <?php elseif ($step == 2): ?>
                <div class="header">
                    <button type="button" class="back-btn" onclick="window.location.href='payment.php?<?php echo $_SERVER['QUERY_STRING']; ?>'"><i class="fas fa-chevron-left"></i></button>
                    <h3>Payment Method</h3>
                    <div style="width:40px"></div>
                </div>

                <div class="method-box active" id="visaBox" onclick="togglePayment('visa')">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <i class="fab fa-cc-visa" style="color:#1A1F71; font-size: 20px;"></i>
                        <span>Visa / Master Card</span>
                    </div>
                    <input type="radio" name="payment_method" value="visa" id="radioVisa" checked>
                </div>

                <div class="visa-details show" id="visaDetails">
                    <div class="input-group">
                        <label>Card Number</label>
                        <input type="text" placeholder="**** **** **** ****">
                    </div>
                    <div class="input-group">
                        <label>Cardholder Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($current_user_name); ?>">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <div class="input-group" style="flex: 2;"><label>Expiry Date</label><input type="text" placeholder="MM/YY"></div>
                        <div class="input-group" style="flex: 1;"><label>CVV</label><input type="text" placeholder="123"></div>
                    </div>
                </div>

                <div class="method-box" id="cashBox" onclick="togglePayment('cash')">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <i class="fas fa-money-bill-wave" style="color:#2ecc71; font-size: 20px;"></i>
                        <span>Cash at Office</span>
                    </div>
                    <input type="radio" name="payment_method" value="cash" id="radioCash">
                </div>

                <div class="billing">
                    <div class="bill-row total"><span>Total</span><span><?php echo number_format($total_payment, 0); ?> EGP</span></div>
                </div>
                <button name="confirm_order" class="btn-primary">Pay & Confirm Trip</button>

            <?php elseif ($step == 3): ?>
                <div style="text-align: center; margin-top: 80px;">
                    <i class="fas fa-check-circle" style="font-size: 60px; color: #2ecc71;"></i>
                    <h2 style="color: var(--primary);">Success!</h2>
                    <p>Pack your bags! Your trip is confirmed.</p>
                    <button type="button" class="btn-primary" onclick="window.location.href='index.php'">Home</button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        function togglePayment(method) {
            const visaDetails = document.getElementById('visaDetails');
            const radioVisa = document.getElementById('radioVisa');
            const radioCash = document.getElementById('radioCash');
            const visaBox = document.getElementById('visaBox');
            const cashBox = document.getElementById('cashBox');

            if (method === 'visa') {
                visaDetails.classList.add('show');
                radioVisa.checked = true;
                visaBox.classList.add('active');
                cashBox.classList.remove('active');
            } else {
                visaDetails.classList.remove('show');
                radioCash.checked = true;
                cashBox.classList.add('active');
                visaBox.classList.remove('active');
            }
        }
    </script>
</body>

</html>