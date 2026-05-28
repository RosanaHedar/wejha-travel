<?php
session_start();
include 'navbar.php';
include 'wegha_db.php';

// --- Redirect if not logged in ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Fetch User Data
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// 2. Fetch Dynamic Bookings (FIXED: LEFT JOIN + Subquery to handle custom configurations safely)
$booking_query = "SELECT b.*, 
                         COALESCE(p.title, '🛠️ Custom Tailored Trip') as title, 
                         COALESCE(p.image_url, 'default.jpg') as image_url, 
                         p.package_id,
                         (SELECT GROUP_CONCAT(CONCAT(cps.quantity, 'x ', s.service_name) SEPARATOR '<br>') 
                          FROM customer_package_services cps 
                          JOIN services s ON cps.service_id = s.service_id 
                          WHERE cps.customer_package_id = b.custom_package_id) as custom_details
                  FROM bookings b 
                  LEFT JOIN packages p ON b.package_id = p.package_id 
                  WHERE b.user_id = '$user_id' 
                  ORDER BY b.booking_date DESC";
$booking_result = mysqli_query($conn, $booking_query);

// 3. Fetch Dynamic Wishlist (JOIN with packages)
$wishlist_query = "SELECT w.*, p.title, p.image_url, p.package_id, p.price, p.discount_price 
                   FROM wishlist w 
                   JOIN packages p ON w.package_id = p.package_id 
                   WHERE w.user_id = '$user_id'";
$wishlist_result = mysqli_query($conn, $wishlist_query);

// --- Handle Profile & Image Update ---
if (isset($_POST['update']) || (isset($_FILES['image']) && $_FILES['image']['error'] == 0)) {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? $user['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? $user['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? $user['email']);
    $image_path = $user['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $folder = "uploads/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
        $target = $folder . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = $target;
        }
    }

    $update_sql = "UPDATE users SET full_name='$name', phone='$phone', email='$email', image='$image_path' WHERE user_id='$user_id'";
    if (mysqli_query($conn, $update_sql)) {
        header("Location: profile.php?msg=updated");
        exit;
    }
}

// --- Handle Password Change ---
if (isset($_POST['change_password'])) {
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($new_pass === $confirm_pass && password_verify($current_pass, $user['password_hash'])) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password_hash='$hashed' WHERE user_id='$user_id'");
        $success_msg = "Password updated successfully!";
    } else {
        $error_msg = "Current password incorrect or new passwords don't match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile | WIJHA وجهة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@300;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --main-copper: #c8a97e;
            --soft-bg: #fdfaf7;
            --red: #ff4d4d;
        }

        body {
            font-family: 'Poppins', 'Cairo', sans-serif;
            background: #f5f1eb;
            margin: 0;
            color: #4a4a4a;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
        }

        /* Profile Header Card */
        .profile-card {
            background: white;
            padding: 40px;
            border-radius: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .profile-img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--main-copper);
            background: #eee;
        }

        .btn {
            background: var(--main-copper);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 500;
            margin: 5px;
            font-family: inherit;
        }

        .btn-dark {
            background: #4a4a4a;
        }

        /* Status Badge Styling */
        .status-badge {
            font-size: 0.7rem;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 50px;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 5px;
            border: 1px solid transparent;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        /* Dashboard Sections */
        .sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
        }

        .list-item {
            background: var(--soft-bg);
            margin: 12px 0;
            padding: 15px;
            border-radius: 20px;
            border: 1px solid #f0e6d8;
            transition: 0.2s;
            display: flex;
            flex-direction: column;
        }

        .list-item:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .item-main-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .item-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-grow: 1;
        }

        .item-link img {
            width: 65px;
            height: 65px;
            border-radius: 12px;
            object-fit: cover;
            background: #eee;
            flex-shrink: 0;
        }

        .trash-btn {
            color: #ccc;
            padding: 10px;
            transition: 0.3s;
            cursor: pointer;
            text-decoration: none;
        }

        .trash-btn:hover {
            color: var(--red);
        }

        .price-tag {
            font-size: 0.85rem;
            color: var(--main-copper);
            font-weight: bold;
        }

        .custom-breakdown {
            font-size: 11px;
            color: #777;
            background: #f4eee6;
            padding: 6px 10px;
            border-radius: 8px;
            margin-top: 5px;
            line-height: 1.4;
        }

        /* --- INLINE REVIEW PANEL FORM DESIGN --- */
        .btn-rate-trigger {
            background: none;
            border: none;
            cursor: pointer;
            color: #ccc;
            padding: 10px;
            font-size: 1.15rem;
            transition: 0.2s ease;
        }

        .btn-rate-trigger:hover {
            color: #f1c40f;
        }

        .review-dropdown-box {
            background: white;
            padding: 15px;
            border-radius: 15px;
            border: 1px solid #eef;
            margin-top: 12px;
        }

        .review-dropdown-box h5 {
            margin: 0 0 10px 0;
            color: #4a4a4a;
            font-size: 0.85rem;
        }

        .review-dropdown-box select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            font-family: inherit;
            outline: none;
        }

        .review-dropdown-box textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            font-family: inherit;
            box-sizing: border-box;
            resize: none;
            outline: none;
            font-size: 0.85rem;
        }

        .btn-send-review {
            background: #f37021;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.8rem;
            cursor: pointer;
        }

        /* Form Input Styling */
        input {
            width: 100%;
            max-width: 350px;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-sizing: border-box;
        }

        #form,
        #password-form {
            margin-top: 20px;
            background: #fafafa;
            padding: 25px;
            border-radius: 20px;
            display: none;
            border: 1px solid #eee;
        }
    </style>
</head>

<body>

    <div class="container">
        <?php if (isset($success_msg)) echo "<p style='color:green; text-align:center;'>$success_msg</p>"; ?>
        <?php if (isset($error_msg)) echo "<p style='color:red; text-align:center;'>$error_msg</p>"; ?>

        <?php if (isset($_GET['review_msg']) && $_GET['review_msg'] == 'submitted'): ?>
            <p style='color:green; text-align:center; font-weight:bold; background:#e6fffa; padding:12px; border-radius:10px; border:1px solid #b2f5ea;'>
                ✅ Review log received! Your feedback will go live once verified by administrators.
            </p>
        <?php endif; ?>

        <div class="profile-card">
            <form method="POST" enctype="multipart/form-data">
                <div style="position: relative; width: 130px; margin: 0 auto 20px;">
                    <img src="<?php echo !empty($user['image']) ? $user['image'] . '?t=' . time() : 'uploads/avatar.png'; ?>" class="profile-img">
                    <label for="file-input" style="position:absolute; bottom:5px; right:5px; background:var(--main-copper); color:white; width:35px; height:35px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; border:2px solid white;">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="file-input" name="image" style="display:none;" onchange="this.form.submit()">
                </div>

                <h2 style="margin:0;"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p style="color: #999; margin-bottom: 20px;"><?php echo htmlspecialchars($user['email']); ?></p>

                <button type="button" class="btn" onclick="toggleView('form')">Edit Details</button>
                <button type="button" class="btn btn-dark" onclick="toggleView('password-form')">Security</button>

                <div id="form">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Full Name">
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="Phone Number">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email">
                    <br><button name="update" class="btn">Update Profile</button>
                </div>

                <div id="password-form">
                    <h4 style="margin-top:0;">Reset Account Password</h4>
                    <input type="password" name="current_pass" placeholder="Current Password">
                    <input type="password" name="new_pass" placeholder="New Password">
                    <input type="password" name="confirm_pass" placeholder="Confirm New Password">
                    <br><button name="change_password" class="btn btn-dark">Confirm New Password</button>
                </div>
            </form>
        </div>

        <div class="sections">
            <div class="card">
                <h3><i class="fas fa-suitcase" style="color:var(--main-copper); margin-right:10px;"></i> My Bookings</h3>
                <?php if (mysqli_num_rows($booking_result) > 0): ?>
                    <?php while ($b = mysqli_fetch_assoc($booking_result)):
                        $status = strtolower($b['status']);
                        $status_class = 'status-pending';
                        if ($status == 'confirmed') $status_class = 'status-confirmed';
                        if ($status == 'cancelled') $status_class = 'status-cancelled';

                        $link_target = (!empty($b['package_id'])) ? "details.php?id=" . $b['package_id'] : "#";
                        $cursor_style = (empty($b['package_id'])) ? "style='cursor: default;'" : "";
                    ?>
                        <div class="list-item">
                            <div class="item-main-row">
                                <a href="<?php echo $link_target; ?>" <?php echo $cursor_style; ?> class="item-link">
                                    <img src="assets/img/<?php echo $b['image_url']; ?>">
                                    <div style="width: 100%;">
                                        <h4 style="margin:0;"><?php echo htmlspecialchars($b['title']); ?></h4>
                                        <span class="price-tag"><?php echo number_format($b['total_price']); ?> EGP</span>

                                        <?php if (!empty($b['custom_details'])): ?>
                                            <div class="custom-breakdown">
                                                <?php echo $b['custom_details']; ?>
                                            </div>
                                        <?php endif; ?>

                                        <br>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($b['status']); ?>
                                        </span>
                                    </div>
                                </a>

                                <?php if ($status == 'confirmed'): ?>
                                    <button type="button" class="btn-rate-trigger" onclick="toggleReviewField(<?php echo $b['booking_id']; ?>)" title="Rate this adventure">
                                        <i class="fas fa-star"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if ($status == 'confirmed'): ?>
                                <div id="review-panel-<?php echo $b['booking_id']; ?>" class="review-dropdown-box" style="display: none;">
                                    <h5><i class="far fa-edit"></i> How was your experience?</h5>
                                    <form action="submit_review.php" method="POST">
                                        <input type="hidden" name="package_id" value="<?php echo !empty($b['package_id']) ? $b['package_id'] : ''; ?>">
                                        <input type="hidden" name="redirect" value="profile.php">

                                        <select name="rating" required>
                                            <option value="5">⭐⭐⭐⭐⭐ 5 - Stellar Trip</option>
                                            <option value="4">⭐⭐⭐⭐ 4 - Good Outing</option>
                                            <option value="3">⭐⭐⭐ 3 - Satisfactory</option>
                                            <option value="2">⭐⭐ 2 - Subpar</option>
                                            <option value="1">⭐ 1 - Unsatisfactory</option>
                                        </select>

                                        <textarea name="comment" rows="3" placeholder="Leave your feedback text here..." required></textarea>
                                        <button type="submit" class="btn-send-review">Submit Feedback</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align:center; color:#ccc; padding:20px;">No adventures booked yet.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3><i class="fas fa-heart" style="color:var(--red); margin-right:10px;"></i> My Wishlist</h3>
                <?php if (mysqli_num_rows($wishlist_result) > 0): ?>
                    <?php while ($w = mysqli_fetch_assoc($wishlist_result)): ?>
                        <div class="list-item">
                            <div class="item-main-row">
                                <a href="details.php?id=<?php echo $w['package_id']; ?>" class="item-link">
                                    <img src="assets/img/<?php echo $w['image_url']; ?>">
                                    <div>
                                        <h4 style="margin:0;"><?php echo htmlspecialchars($w['title']); ?></h4>
                                        <span class="price-tag"><?php echo number_format($w['discount_price'] > 0 ? $w['discount_price'] : $w['price']); ?> EGP</span>
                                    </div>
                                </a>
                                <a href="toggle_wishlist.php?package_id=<?php echo $w['package_id']; ?>" class="trash-btn" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align:center; color:#ccc; padding:20px;">Your wishlist is empty.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleView(id) {
            const form = document.getElementById('form');
            const pass = document.getElementById('password-form');

            if (id === 'form') {
                form.style.display = (form.style.display === 'block') ? 'none' : 'block';
                pass.style.display = 'none';
            } else {
                pass.style.display = (pass.style.display === 'block') ? 'none' : 'block';
                form.style.display = 'none';
            }
        }

        // Handles opening and closing the nested feedback panels
        function toggleReviewField(bookingId) {
            const panel = document.getElementById('review-panel-' + bookingId);
            if (panel) {
                panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
            }
        }
    </script>
</body>

</html>