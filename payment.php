<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';
 $database = new Database();
 $db = $database->getConnection();

// Get pending/approved orders for this user
 $query = "SELECT * FROM orders WHERE user_id = ? AND status = 'approved' ORDER BY created_at DESC";
 $stmt = $db->prepare($query);
 $stmt->execute([$_SESSION['user_id']]);
 $orders = $stmt->fetchAll();

if (!$orders) {
    // If no approved orders, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment - BestCarSeller</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="payment">
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo"><div class="logo-icon">🚗</div>BestCarSeller</a>
                <div class="header-icons">
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding-top: 100px; max-width: 800px;">
        <h1 style="margin-bottom: 0.5rem;">Secure Payment</h1>
        <p style="color: var(--fg-muted); margin-bottom: 2rem;">Complete your payment to finalize the order.</p>

        <div class="cart-container" style="grid-template-columns: 1fr;">
            <?php foreach ($orders as $order): ?>
                <div class="cart-summary" style="width: 100%;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3>Order #<?= $order['id'] ?></h3>
                        <span class="status-badge status-approved">Approved</span>
                    </div>
                    
                    <div class="summary-row total" style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1rem;">
                        <span>Amount to Pay</span>
                        <span style="font-size: 1.5rem; color: var(--accent);">
                            $<?= number_format($order['total_amount'], 2) ?> <small style="font-size: 0.8rem; color: var(--fg-muted);">(<?= $order['currency'] ?>)</small>
                        </span>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="payment-methods">
                        <h4 class="payment-methods-title">Select Payment Method</h4>
                        <div class="payment-options" style="grid-template-columns: 1fr 1fr;">
                            <div class="payment-option selected" data-method="bank_transfer" onclick="selectMethod(this, 'bank_transfer', <?= $order['id'] ?>)">
                                🏦 Bank Transfer
                            </div>
                            <div class="payment-option" data-method="mobile_money" onclick="selectMethod(this, 'mobile_money', <?= $order['id'] ?>)">
                                📱 Mobile Money
                            </div>
                            <div class="payment-option" data-method="credit_card" onclick="selectMethod(this, 'credit_card', <?= $order['id'] ?>)">
                                💳 Credit Card
                            </div>
                            <div class="payment-option" data-method="bitcoin" onclick="selectMethod(this, 'bitcoin', <?= $order['id'] ?>)">
                                ₿ Bitcoin
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Payment Instructions -->
                    <div id="payment-details-<?= $order['id'] ?>" style="margin-top: 1.5rem; display: none;">
                        <!-- Content loaded by JS -->
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Payment Modal for Card -->
    <div class="modal-overlay" id="cardModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Enter Card Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="cardForm">
                <div class="form-group">
                    <label class="form-label">Card Number</label>
                    <input type="text" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Expiry</label>
                        <input type="text" class="form-input" placeholder="MM/YY">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVV</label>
                        <input type="text" class="form-input" placeholder="123">
                    </div>
                </div>
                <button type="button" class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="processFakeCard()">Pay Now</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Specific Payment Page Logic
        let currentOrderId = null;

        function selectMethod(element, method, orderId) {
            // Highlight selection
            const parent = element.parentElement;
            parent.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            
            currentOrderId = orderId;
            const detailsContainer = document.getElementById(`payment-details-${orderId}`);
            
            let html = '';
            
            if (method === 'bank_transfer') {
                html = `
                    <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h4 style="margin-bottom: 1rem; color: var(--accent);">Bank Transfer Instructions</h4>
                        <p style="margin-bottom: 0.5rem;"><strong>Bank Name:</strong> Global Auto Finance Bank</p>
                        <p style="margin-bottom: 0.5rem;"><strong>Account Name:</strong> BestCarSeller Inc.</p>
                        <p style="margin-bottom: 0.5rem;"><strong>Account Number:</strong> 0123456789</p>
                        <p style="margin-bottom: 0.5rem;"><strong>Routing Number:</strong> 021000021</p>
                        <p style="margin-bottom: 1rem;"><strong>Swift Code:</strong> GAFBUS33</p>
                        <hr style="border-color: var(--border); margin: 1rem 0;">
                        <p style="font-size: 0.85rem; color: var(--fg-muted);">Reference: Order #${orderId}</p>
                        
                        <div class="form-group" style="margin-top: 1rem;">
                            <label class="form-label">Upload Proof of Payment</label>
                            <input type="file" class="form-input" style="padding: 0.5rem;">
                        </div>
                        <button class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;" onclick="confirmPayment('bank_transfer', ${orderId})">
                            I have made the transfer
                        </button>
                    </div>
                `;
            } else if (method === 'mobile_money') {
                html = `
                    <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h4 style="margin-bottom: 1rem; color: var(--accent);">Mobile Money Payment</h4>
                        <p style="margin-bottom: 1rem;">Send the exact amount to the number below:</p>
                        
                        <div style="text-align: center; padding: 1rem; background: var(--bg); border-radius: 8px; margin-bottom: 1rem;">
                            <p style="font-size: 0.8rem; color: var(--fg-muted);">Send To</p>
                            <p style="font-size: 2rem; font-weight: 700; color: var(--accent);">+1 (555) 123-4567</p>
                            <p style="font-size: 0.9rem; color: var(--fg-muted);">Provider: M-Pesa / Venmo / CashApp</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Enter Transaction ID / Reference</label>
                            <input type="text" id="mobileRef${orderId}" class="form-input" placeholder="e.g. TXN123456789">
                        </div>
                        
                        <button class="btn btn-primary" style="width: 100%;" onclick="confirmPayment('mobile_money', ${orderId})">
                            Confirm Payment
                        </button>
                    </div>
                `;
            } else if (method === 'credit_card') {
                html = `
                    <div style="text-align: center; padding: 2rem;">
                        <p style="margin-bottom: 1rem; color: var(--fg-muted);">Secure payment powered by Stripe</p>
                        <button class="btn btn-primary" style="padding: 1rem 2rem;" onclick="openCardModal(${orderId})">
                            Pay with Card
                        </button>
                    </div>
                `;
            } else if (method === 'bitcoin') {
                html = `
                    <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h4 style="margin-bottom: 1rem; color: var(--accent);">Bitcoin Payment</h4>
                        <p style="margin-bottom: 1rem;">Send the BTC equivalent to the wallet address below:</p>
                        
                        <div style="background: var(--bg); padding: 1rem; border-radius: 8px; word-break: break-all; font-family: monospace; margin-bottom: 1rem; text-align: center; border: 1px dashed var(--accent);">
                            bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Enter Transaction Hash (TXID)</label>
                            <input type="text" id="btcRef${orderId}" class="form-input" placeholder="Paste TXID here">
                        </div>
                        
                        <button class="btn btn-primary" style="width: 100%;" onclick="confirmPayment('bitcoin', ${orderId})">
                            Confirm Payment
                        </button>
                    </div>
                `;
            }
            
            detailsContainer.innerHTML = html;
            detailsContainer.style.display = 'block';
        }

        function openCardModal(orderId) {
            currentOrderId = orderId;
            document.getElementById('cardModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('cardModal').classList.remove('active');
        }

        async function processFakeCard() {
            if (!currentOrderId) return;
            await confirmPayment('credit_card', currentOrderId);
            closeModal();
        }

        async function confirmPayment(method, orderId) {
            let ref = '';
            if (method === 'mobile_money') {
                ref = document.getElementById(`mobileRef${orderId}`).value || 'N/A';
            } else if (method === 'bitcoin') {
                ref = document.getElementById(`btcRef${orderId}`).value || 'N/A';
            } else if (method === 'bank_transfer') {
                ref = 'Bank Receipt Uploaded';
            }

            const result = await api('cart.php?action=confirm_payment', 'POST', {
                order_id: orderId,
                method: method,
                reference: ref
            });

            if (result.success) {
                alert('Payment submitted successfully! Your order is now being processed.');
                window.location.href = 'dashboard.php';
            } else {
                alert(result.error || 'Payment failed. Please try again.');
            }
        }
    </script>
</body>
</html>