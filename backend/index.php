<?php
 
session_start(); 
include 'navbar.php'; 


include 'wegha_db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WIJHA | Travel Your Way</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1e5494;
            --accent-orange: #f37021;
            --soft-gray: #f8f9fa;
            --text-dark: #333;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            background: var(--white);
            color: var(--text-dark);
            scroll-behavior: smooth;
        }

        
       

        

        
        .hero {
            height: 90vh;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('assets/img/hero-egypt.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        .hero h1 { font-size: 4rem; margin-bottom: 10px; font-weight: 800; }
        .btn-build {
            background: var(--accent-orange);
            color: white;
            padding: 18px 45px;
            text-decoration: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            transition: 0.4s;
            margin-top: 20px;
        }
        .btn-build:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(243, 112, 33, 0.4); }

       
        section { padding: 80px 10%; }
        .section-header { text-align: center; margin-bottom: 50px; }
        .section-header h2 { font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 10px; }
        .section-header p { color: #777; font-size: 1.1rem; }

        
        .hot-deals { background-color: var(--white); }
        .deals-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); 
            gap: 30px;
        }

        .deal-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            display: flex; 
            box-shadow: 0 4px 25px rgba(0,0,0,0.05);
            transition: 0.3s;
            border: 1px solid #eee;
        }
        .deal-card:hover { transform: scale(1.02); }

        .deal-img { width: 45%; background-size: cover; background-position: center; min-height: 250px; }
        .deal-info { width: 55%; padding: 30px; display: flex; flex-direction: column; justify-content: center; }
        .deal-info h3 { margin: 0 0 10px; font-size: 1.5rem; color: var(--primary-blue); }
        .deal-info p { color: #666; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px; }
        
        .see-more-btn {
            align-self: flex-start;
            color: var(--accent-orange);
            text-decoration: none;
            font-weight: 700;
            border-bottom: 2px solid var(--accent-orange);
            transition: 0.3s;
        }
        .see-more-btn:hover { letter-spacing: 1px; }

        
        .reviews { background-color: var(--soft-gray); }
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .review-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }
        .stars { color: #ffc107; margin-bottom: 15px; font-size: 1.2rem; }
        .review-text { font-style: italic; color: #555; line-height: 1.7; margin-bottom: 20px; }
        .customer-name { font-weight: 700; color: var(--primary-blue); }

        
        @media (max-width: 992px) {
            .deals-grid { grid-template-columns: 1fr; } 
            nav { padding: 15px 30px; }
            .hero h1 { font-size: 2.5rem; }
        }
        
footer {
    background-color: #1a1a1a; 
    color: #ffffff;
    padding: 60px 10% 20px;
    font-size: 0.9rem;
}

.footer-container {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr; 
    gap: 40px;
    margin-bottom: 40px;
}

.footer-logo h2 {
    color: var(--accent-orange);
    margin-bottom: 15px;
    font-size: 1.8rem;
}

.footer-logo p {
    color: #bbb;
    line-height: 1.6;
}

.footer-column h3 {
    color: #fff;
    margin-bottom: 20px;
    font-size: 1.1rem;
    position: relative;
}

.footer-column h3::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -8px;
    width: 30px;
    height: 2px;
    background-color: var(--accent-orange);
}

.footer-column ul {
    list-style: none;
    padding: 0;
}

.footer-column ul li {
    margin-bottom: 12px;
}

.footer-column ul li a {
    color: #bbb;
    text-decoration: none;
    transition: 0.3s;
}

.footer-column ul li a:hover {
    color: var(--accent-orange);
    padding-left: 5px;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-links a {
    color: white;
    font-size: 1.2rem;
    transition: 0.3s;
}

.social-links a:hover {
    color: var(--accent-orange);
    transform: translateY(-3px);
}

.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #333;
    color: #777;
    font-size: 0.8rem;
}


@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        text-align: center;
    }
    .footer-column h3::after {
        left: 50%;
        transform: translateX(-50%);
    }
    .social-links {
        justify-content: center;
    }
}
    </style>
</head>

<body>

    

    <div class="hero">
        <h1>Egypt, Your Way.</h1>
        <p>Where Memories Begin </p>
        <a href="build_package.php" class="btn-build">✨ Build Your Package </a>
    </div>

    <section class="hot-deals">
        <div class="section-header">
            <h2>Hot Deals 🔥</h2>
            <p>Hand-picked exclusive offers for your next adventure</p>
        </div>

        <div class="deals-grid">
            <div class="deal-card">
                <div class="deal-img" style="background-image: url('https://images.unsplash.com/photo-1623124116035-717011d61994?q=80&w=1000');"></div>
                <div class="deal-info">
                    <h3>Siwa Oasis Magic</h3>
                    <p>Experience the serenity of the salt lakes and the ancient Shali fortress. 3 days of pure peace.</p>
                    <a href="offers.php" class="see-more-btn">See More →</a>
                </div>
            </div>

            <div class="deal-card">
                <div class="deal-img" style="background-image: url('https://images.unsplash.com/photo-1539650116574-8efeb43e2750?q=80&w=500');"></div>
                <div class="deal-info">
                    <h3>Full Day Private Luxury Nile cruise </h3>
                    <p>Sail between Luxor and Aswan on a 5-star floating hotel. History meets modern comfort.</p>
                    <a href="offers.php" class="see-more-btn">See More →</a>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews">
        <div class="section-header">
            <h2>What Our Travelers Say</h2>
            <p>Real stories from people who explored Egypt with WIJHA</p>
        </div>

        <div class="reviews-grid">
            <div class="review-card">
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="review-text">"The customization of the package was incredible. Everything was handled professionally, from the airport pickup to the private tours."</p>
                <p class="customer-name">- Sarah Johnson</p>
            </div>

            <div class="review-card">
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="review-text">"I loved the website's ease of use. The Hot Deals section saved me a lot of money on my honeymoon trip to Dahab!"</p>
                <p class="customer-name">- Ahmed Rayan</p>
            </div>

            <div class="review-card">
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <p class="review-text">"A truly unique experience. Wijha helped us discover hidden gems in Cairo that we wouldn't have found alone."</p>
                <p class="customer-name">- Elena Petrov</p>
            </div>
        </div>
    </section>
    
    <footer>
    <div class="footer-container">
        <div class="footer-logo">
            <h2>WIJHA وجهة</h2>
            <p>We believe Egypt is best explored your way. Our mission is to provide personalized, high-quality travel experiences that stay in your memory forever.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <div class="footer-column">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="bundles.php">Travel Bundles</a></li>
                <li><a href="offers.php">Special Offers</a></li>
                <li><a href="build_package.php">Build Your Trip</a></li>
            </ul>
        </div>

        <div class="footer-column">
    <h3>Support</h3>
    <ul>
        <li><a href="support.php#faq">FAQ</a></li>
        <li><a href="support.php#privacy">Privacy Policy</a></li>
        <li><a href="support.php#terms">Terms & Conditions</a></li>
        <li><a href="support.php#contact">Contact Us</a></li>
    </ul>
</div>

        <div class="footer-column">
            <h3>Contact Us</h3>
            <ul>
                <li><i class="fas fa-map-marker-alt" style="margin-right: 10px; color: var(--accent-orange);"></i> Cairo, Egypt</li>
                <li><i class="fas fa-phone" style="margin-right: 10px; color: var(--accent-orange);"></i> +20 123 456 789</li>
                <li><i class="fas fa-envelope" style="margin-right: 10px; color: var(--accent-orange);"></i> info@wijha.com</li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2026 WIJHA Travel. All Rights Reserved. Created for SheConnect.</p>
    </div>
</footer>

</body>
</html>