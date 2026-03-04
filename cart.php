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
    <title>Shopping Cart - BestCarSeller</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="cart">
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo"><div class="logo-icon">🚗</div>BestCarSeller</a>
                <div class="header-icons">
                    <button class="dropdown-item danger" onclick="logout()">Logout</button>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding-top: 100px;">
        <h1 style="margin-bottom: 2rem;">Shopping Cart</h1>
        
        <div class="cart-container">
            <div class="cart-items" id="cartItems">
                <!-- JS Loaded -->
            </div>

            <div class="cart-summary">
                <h3 style="margin-bottom: 1rem;">Order Summary</h3>
                
                <div class="currency-selector" style="margin-bottom: 1rem;">
                    <button class="currency-btn active" onclick="changeCurrency('USD')">USD</button>
                    <button class="currency-btn" onclick="changeCurrency('EUR')">EUR</button>
                    <button class="currency-btn" onclick="changeCurrency('BTC')">BTC</button>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="summarySubtotal">$0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="summaryTotal">$0.00</span>
                </div>

                <div class="payment-methods">
                    <h4 class="payment-methods-title">Payment Method</h4>
                    <div class="payment-options">
                        <div class="payment-option selected" data-method="credit_card" onclick="selectPaymentMethod('credit_card')">💳 Card</div>
                        <div class="payment-option" data-method="paypal" onclick="selectPaymentMethod('paypal')">🅿️ PayPal</div>
                        <div class="payment-option" data-method="bitcoin" onclick="selectPaymentMethod('bitcoin')">₿ Bitcoin</div>
                        <div class="payment-option" data-method="bank_transfer" onclick="selectPaymentMethod('bank_transfer')">🏦 Bank</div>
                    </div>
                </div>

                <textarea id="checkoutNotes" class="form-input" placeholder="Add notes (optional)" rows="2" style="margin-bottom: 1rem;"></textarea>

                <button id="checkoutBtn" class="btn btn-primary" style="width: 100%;" onclick="processCheckout()">
                    Place Order
                </button>
                <p style="font-size: 0.75rem; color: var(--fg-muted); margin-top: 0.5rem; text-align: center;">
                    * Orders require admin approval before processing.
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>