<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - BestCarSeller</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="admin">
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo"><div class="logo-icon">🚗</div>BestCarSeller Admin</a>
                <div class="header-icons">
                    <span style="color: var(--accent); font-weight: 600;">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <button class="btn btn-sm btn-danger" onclick="logout()" style="margin-left: 1rem;">Logout</button>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding-top: 100px;">
        <div class="admin-header">
            <h1>Dashboard Overview</h1>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-card-label">Total Users</div>
                <div class="stat-card-value" id="statUsers">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Total Orders</div>
                <div class="stat-card-value" id="statOrders">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Total Revenue</div>
                <div class="stat-card-value" id="statRevenue">$0</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Cars Listed</div>
                <div class="stat-card-value" id="statCars">0</div>
            </div>
        </div>

        <div class="cart-items">
            <h3 style="margin-bottom: 1rem;">Recent Orders (Approval Required)</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTable">
                    <!-- JS Loaded -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>