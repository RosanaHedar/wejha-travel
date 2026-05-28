<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo-link">
            <img src="assets/img/logo.jpeg" alt="WIJHA Logo" class="nav-logo">
        </a>
        
        <ul class="nav-links">
            <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="bundles.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bundles.php' ? 'active' : ''; ?>">Bundles</a></li>
            <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">My Profile</a></li>
        </ul>

        <div class="nav-auth">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="auth-btn logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="auth-btn login">
                    <i class="fas fa-user-circle"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<style>
:root {
    --primary: #ff6d00;
    --dark: #2d2d2d;
}

.navbar {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 2000;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0,0,0,0.05);
    padding: 5px 0; 
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}


.logo-link {
    display: flex;
    align-items: center;
}

.nav-logo {
    height: 80px; 
    width: auto;
    object-fit: contain; 
    transform: scale(1.4); 
    transform-origin: left center; 
    transition: 0.3s;
    cursor: pointer;
}

.nav-logo:hover {
    transform: scale(1.05);
}

.nav-links {
    display: flex;
    list-style: none;
    gap: 35px;
    margin: 0;
    padding: 0;
}

.nav-links a {
    text-decoration: none;
    color: var(--dark);
    font-weight: 500;
    font-size: 15px;
    transition: 0.3s;
    position: relative;
}

.nav-links a.active, .nav-links a:hover {
    color: var(--primary);
}

.nav-links a.active::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--primary);
}

.auth-btn {
    padding: 10px 22px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}

.login {
    background: var(--primary);
    color: white;
}

.logout {
    background: #fdf0e6;
    color: #e74c3c;
    border: 1px solid #f9d5d2;
}

body {
    padding-top: 85px; 
}
</style>