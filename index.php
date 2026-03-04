<?php
session_start();
 $isLoggedIn = isset($_SESSION['user_id']);
 $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BestCarSeller - Premium Cars Marketplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="home">
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-inner">
                <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
                <a href="index.php" class="logo">
                    <div class="logo-icon">🚗</div>
                    BestCarSeller
                </a>
                
                <div class="search-container">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search cars...">
                    <button class="search-btn" id="searchBtn">→</button>
                </div>

                <div class="header-icons">
                    <?php if ($isLoggedIn): ?>
                        <a href="cart.php" class="icon-btn">🛒 <span class="badge" id="cartCount">0</span></a>
                        <div class="user-menu">
                            <button class="user-btn">👤 <?= htmlspecialchars($_SESSION['username']) ?></button>
                            <div class="user-dropdown">
                                <a href="dashboard.php" class="dropdown-item">Dashboard</a>
                                <?php if ($isAdmin): ?>
                                    <a href="admin.php" class="dropdown-item">Admin Panel</a>
                                <?php endif; ?>
                                <button class="dropdown-item danger" onclick="logout()">Logout</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-sm">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="main-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">Categories</h3>
                <nav id="sidebarTypes">
                    <!-- Populated by JS -->
                </nav>
            </div>
            <div class="sidebar-section">
                <h3 class="sidebar-title">Currency</h3>
                <div class="currency-selector">
                    <button class="currency-btn active" data-currency="USD" onclick="changeCurrency('USD')">USD</button>
                    <button class="currency-btn" data-currency="EUR" onclick="changeCurrency('EUR')">EUR</button>
                    <button class="currency-btn" data-currency="GBP" onclick="changeCurrency('GBP')">GBP</button>
                    <button class="currency-btn" data-currency="BTC" onclick="changeCurrency('BTC')">BTC</button>
                </div>
            </div>
        </aside>

        <!-- Content -->
        <main class="main-content">
            <section class="hero">
                <div class="hero-content">
                    <h1>Find Your Perfect Drive</h1>
                    <p>Premium selection of vehicles from trusted sellers worldwide.</p>
                </div>
            </section>

            <section>
                <div class="section-header">
                    <h2 class="section-title" id="sectionTitle">Featured Vehicles</h2>
                </div>
                <div class="car-grid" id="featuredGrid">
                    <!-- Populated by JS -->
                </div>
                <div class="car-grid" id="mainGrid" style="margin-top: 1rem;"></div>
            </section>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>