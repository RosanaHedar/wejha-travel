<?php
session_start();
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support & Policies | WIJHA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e5494;
            --accent: #f37021;
            --bg: #fdfaf5;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
            background: var(--bg);
            margin: 0;
            scroll-behavior: smooth;
        }

        .support-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
        }

        section {
            padding: 40px 0;
            border-bottom: 1px solid #eee;
        }

        section:last-child {
            border-bottom: none;
        }

        h2 {
            color: var(--primary);
            border-left: 5px solid var(--accent);
            padding-left: 15px;
            margin-bottom: 25px;
        }

        h3 {
            color: #444;
            margin-top: 20px;
        }

        p {
            color: #666;
            margin-bottom: 15px;
        }

        /* FAQ Styling */
        .faq-item {
            margin-bottom: 20px;
        }

        .faq-question {
            font-weight: bold;
            color: var(--primary);
            cursor: pointer;
            display: block;
            margin-bottom: 5px;
        }

        /* Contact Form inside Support */
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .submit-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="support-container">
        <h1 style="text-align:center; color: var(--primary);">WIJHA Support Center</h1>

        <section id="faq">
            <h2><i class="fas fa-question-circle"></i> FAQ</h2>
            <div class="faq-item">
                <span class="faq-question">How can I book a customized trip?</span>
                <p>You can use our "Build My Package" feature to select your destinations, dates, and preferences manually.</p>
            </div>
            <div class="faq-item">
                <span class="faq-question">What are the available payment methods?</span>
                <p>We accept Credit Cards (Visa/Mastercard) and Cash on delivery for certain bookings.</p>
            </div>
        </section>

        <section id="privacy">
            <h2><i class="fas fa-user-shield"></i> Privacy Policy</h2>
            <p>At WIJHA, we take your privacy seriously. We only collect information necessary to provide you with the best travel experience.</p>
            <p>Your personal data and payment information are encrypted and never shared with third parties without your consent.</p>
        </section>

        <section id="terms">
            <h2><i class="fas fa-file-contract"></i> Terms & Conditions</h2>
            <p>By using our services, you agree to our booking and cancellation policies.</p>
            <p>Cancellations made 48 hours before the trip are eligible for a full refund. Later cancellations may incur a fee.</p>
        </section>

        <section id="contact">
            <h2><i class="fas fa-envelope-open-text"></i> Contact Us</h2>
            <div id="contact-area">
                <p>Have a specific question? Send us a message and our team will get back to you within 24 hours.</p>
                <form class="contact-form" id="supportForm">
                    <form class="contact-form" id="supportForm">
                        <input type="text" name="name" placeholder="Your Name" required>
                        <input type="email" name="email" placeholder="Your Email" required>
                        <input type="text" name="phone" placeholder="Phone Number" required> <textarea name="message" rows="5" placeholder="How can we help you?" required></textarea>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>

            </div>
        </section>

        <script>
            document.getElementById('supportForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const contactArea = document.getElementById('contact-area');


                fetch('send_contact.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            contactArea.innerHTML = `
                <div style="text-align: center; padding: 40px; animation: fadeIn 0.5s;">
                    <i class="fas fa-check-circle" style="font-size: 50px; color: #28a745; margin-bottom: 20px;"></i>
                    <h3 style="color: var(--primary);">Thank You!</h3>
                    <p>Your message has been sent successfully. Our team will contact you very soon.</p>
                </div>
            `;
                        } else {
                            alert("Oops! Something went wrong. Please try again.");
                        }
                    });
            });
        </script>

        <style>
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </div>

</body>

</html>