<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - BestCarSeller</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="dashboard">
    <header class="header" id="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo"><div class="logo-icon">🚗</div>BestCarSeller</a>
                <div class="header-icons">
                    <a href="index.php" class="btn btn-secondary btn-sm">Continue Shopping</a>
                    
                    <!-- Payment Notification Icon -->
                    <a href="payment.php" class="icon-btn" title="Pay Now" id="paymentNotif" style="display:none;">
                        💳
                    </a>

                    <button class="dropdown-item danger" onclick="logout()" style="margin-left: 10px; background: transparent; border: 1px solid var(--danger); color: var(--danger); padding: 8px 15px; border-radius: 20px; cursor: pointer;">Logout</button>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding-top: 100px;">
        <h1 style="margin-bottom: 2rem;">My Orders</h1>
        
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-card-label">Total Orders</div>
                <div class="stat-card-value" id="userTotalOrders">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Pending Approval</div>
                <div class="stat-card-value" id="userPendingOrders">0</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--success);">
                <div class="stat-card-label">Awaiting Payment</div>
                <div class="stat-card-value" id="userApprovedOrders">0</div>
            </div>
        </div>

        <div class="cart-items">
            <div id="userOrdersList"></div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Load User Orders
        async function loadUserOrders() {
            const result = await api('cart.php?action=orders');
            const container = document.getElementById('userOrdersList');
            let pendingCount = 0;
            let approvedCount = 0;
            
            if (result.success && result.orders.length > 0) {
                container.innerHTML = result.orders.map(order => {
                    if (order.status === 'pending') pendingCount++;
                    if (order.status === 'approved') approvedCount++;
                    
                    let actionBtn = '';
                    if (order.status === 'approved') {
                        actionBtn = `<a href="payment.php" class="btn btn-primary" style="margin-top: 1rem;">Pay Now</a>`;
                    }

                    return `
                        <div class="cart-item" style="flex-direction: column; align-items: flex-start;">
                            <div style="width: 100%; display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span><strong>Order #${order.id}</strong></span>
                                <span class="status-badge status-${order.status}">${order.status}</span>
                            </div>
                            <div style="font-size: 0.9rem; color: #8a8a96; margin-bottom: 0.5rem;">
                                Date: ${new Date(order.created_at).toLocaleString()}<br>
                                Total: $${parseFloat(order.total_amount).toFixed(2)} ${order.currency}
                            </div>
                            <div style="font-size: 0.85rem;">
                                Items: ${order.items.map(i => `${i.brand} ${i.model}`).join(', ')}
                            </div>
                            ${order.status === 'pending' ? '<div style="margin-top:0.5rem; font-size:0.8rem; color:var(--warning);">⏳ Waiting for admin approval</div>' : ''}
                            ${order.status === 'completed' ? '<div style="margin-top:0.5rem; font-size:0.8rem; color:var(--success);">✅ Payment Complete</div>' : ''}
                            ${actionBtn}
                        </div>
                    `;
                }).join('');
                
                document.getElementById('userTotalOrders').textContent = result.orders.length;
                document.getElementById('userPendingOrders').textContent = pendingCount;
                document.getElementById('userApprovedOrders').textContent = approvedCount;
            } else {
                container.innerHTML = '<div class="empty-state"><h3>No orders yet</h3><a href="index.php" class="btn btn-primary" style="margin-top:1rem;">Start Shopping</a></div>';
            }
        }
        
        // Check for approved orders to show payment notification in header
        async function checkPaymentStatus() {
            if (!document.getElementById('paymentNotif')) return;
            const result = await api('cart.php?action=orders');
            if (result.success) {
                const hasApproved = result.orders.some(o => o.status === 'approved');
                const notifBtn = document.getElementById('paymentNotif');
                if (hasApproved) {
                    notifBtn.style.display = 'flex';
                    notifBtn.innerHTML = '💳 <span class="badge" style="background:var(--success);">!</span>';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadUserOrders();
            checkPaymentStatus();
        });
    </script>
</body>
</html>