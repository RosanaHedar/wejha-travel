<?php
session_start();
include 'wegha_db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Wegha | Build Your Trip</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #fdfaf5;
            color: #333;
        }

        nav {
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        nav .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1e5494;
            text-decoration: none;
        }

        nav .links a {
            text-decoration: none;
            color: #1e5494;
            font-weight: bold;
            margin-left: 20px;
        }

        .hero {
            height: 80vh;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('assets/img/hero-egypt.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        /* Build My Package Button */
        .btn-build {
            background: #f37021;
            /* Accent Orange */
            color: white;
            padding: 20px 50px;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 10px 20px rgba(243, 112, 33, 0.3);
            transition: 0.3s;
            margin-bottom: 30px;
        }

        .btn-build:hover {
            transform: translateY(-5px);
            background: #e66010;
            box-shadow: 0 15px 30px rgba(243, 112, 33, 0.5);
        }

        .or-text {
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .btn-secondary {
            color: white;
            text-decoration: underline;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <nav>
        <a href="index.php" class="logo">WIJHA وجهة</a>
        <div class="links">
            <a href="index.php">Home</a>
            <a href="bundles.php">Browse Bundles</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">👤 Profile</a>
                <a href="logout.php" style="color: #ff4d4d;">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero">
        <h1 style="font-size: 3.5rem; margin-bottom: 10px;">Egypt, Your Way.</h1>
        <p style="font-size: 1.2rem; margin-bottom: 40px;">Don't just visit. Create an itinerary that fits your soul.</p>

        <a href="build_package.php" class="btn-build">✨ Build my package</a>

        <div class="or-text">OR</div>

        <a href="bundles.php" class="btn-secondary">Browse curated travel bundles</a>
    </div>

</body>

</html>