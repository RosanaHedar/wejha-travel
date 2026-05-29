<div class="sidebar">
    <h2>Wegha Admin</h2>
    <a href="admin_analytics.php"><i class="fas fa-chart-pie"></i> Business Analytics</a>
    <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard Overview</a>
    <a href="admin_categories.php"><i class="fas fa-folder"></i> Manage Categories</a>
    <a href="admin_add_package.php"><i class="fas fa-plus-circle"></i> Add New Package</a>
    <a href="admin_build_package.php"><i class="fas fa-tools"></i> Setup Custom Options</a>
    <a href="admin_manage_packages.php"><i class="fas fa-boxes"></i> Manage Packages</a>
    <a href="admin_users.php"><i class="fas fa-users"></i> Registered Users</a>
    <a href="admin_bookings.php"><i class="fas fa-plane"></i> Manage Bookings</a>
    <a href="admin_support.php"><i class="fas fa-envelope"></i> Support Tickets</a>
    <a href="admin_reviews.php"><i class="fas fa-comments"></i> Manage Reviews</a>
    <a href="admin_logout.php" class="logout"><i class="fas fa-door-open"></i> Logout</a>
</div>

<style>
    /* --- WEGHA ADMIN SIDEBAR STYLE BLUEPRINT --- */
    :root {
        --primary-blue: #1e5494;
        --accent-orange: #f37021;
        --text-muted-light: rgba(255, 255, 255, 0.8);
    }

    .sidebar {
        width: 260px;
        background: var(--primary-blue);
        color: white;
        height: 100vh;
        padding: 24px 16px;
        position: fixed;
        top: 0;
        left: 0;
        box-sizing: border-box;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        z-index: 999;
    }

    .sidebar h2 {
        text-align: center;
        color: var(--accent-orange);
        margin: 0 0 32px 0;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-muted-light);
        text-decoration: none;
        padding: 12px 16px;
        margin-bottom: 6px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .sidebar a i {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    /* JavaScript-based active class route finder highlighting rule */
    .sidebar a.active-route {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border-left: 4px solid var(--accent-orange);
        font-weight: 600;
        padding-left: 12px;
    }

    .sidebar .logout {
        margin-top: auto;
        color: #f87171;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 16px;
    }

    .sidebar .logout:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
</style>

<!-- Automation script to instantly highlight the correct menu item based on current URL path -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const currentPath = window.location.pathname.split("/").pop();
        const navLinks = document.querySelectorAll(".sidebar a");

        navLinks.forEach(link => {
            if (link.getAttribute("href") === currentPath) {
                link.classList.add("active-route");
            }
        });
    });
</script>