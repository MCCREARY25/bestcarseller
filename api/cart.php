<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

 $method = $_SERVER['REQUEST_METHOD'];
 $database = new Database();
 $db = $database->getConnection();

 $action = $_GET['action'] ?? '';

// Payment methods configuration
 $paymentMethods = [
    'credit_card' => ['name' => 'Credit/Debit Card', 'icon' => 'credit-card'],
    'paypal' => ['name' => 'PayPal', 'icon' => 'paypal'],
    'bank_transfer' => ['name' => 'Bank Transfer', 'icon' => 'bank'],
    'bitcoin' => ['name' => 'Bitcoin', 'icon' => 'bitcoin'],
    'mobile_money' => ['name' => 'Mobile Money', 'icon' => 'mobile']
];

 $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'BTC'];

switch ($action) {
    case 'get':
        getCart($db);
        break;
    case 'add':
        addToCart($db);
        break;
    case 'remove':
        removeFromCart($db);
        break;
    case 'clear':
        clearCart($db);
        break;
    case 'checkout':
        processCheckout($db);
        break;
    case 'orders':
        getUserOrders($db);
        break;
    case 'payment_methods':
        jsonResponse(['success' => true, 'methods' => $paymentMethods, 'currencies' => $currencies]);
        break;
    case 'confirm_payment':
        confirmPayment($db);
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function getCart($db) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Please login to view cart'], 401);
    }
    
    $userId = $_SESSION['user_id'];
    $currency = isset($_GET['currency']) ? strtoupper($_GET['currency']) : 'USD';
    
    $query = "SELECT c.*, cars.model, cars.brand, cars.price, cars.image, cars.year 
              FROM cart c 
              JOIN cars ON c.car_id = cars.id 
              WHERE c.user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    
    $total = 0;
    foreach ($items as &$item) {
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
    }
    
    jsonResponse([
        'success' => true,
        'items' => $items,
        'total' => $total,
        'count' => count($items)
    ]);
}

function addToCart($db) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Please login to add items to cart'], 401);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $carId = (int)($data['car_id'] ?? 0);
    $quantity = max(1, (int)($data['quantity'] ?? 1));
    
    if (!$carId) {
        jsonResponse(['error' => 'Car ID is required'], 400);
    }
    
    // Check if car exists and has stock
    $checkQuery = "SELECT id, stock FROM cars WHERE id = ?";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute([$carId]);
    $car = $stmt->fetch();
    
    if (!$car) {
        jsonResponse(['error' => 'Car not found'], 404);
    }
    
    if ($car['stock'] < $quantity) {
        jsonResponse(['error' => 'Insufficient stock'], 400);
    }
    
    // Check if already in cart
    $cartQuery = "SELECT id, quantity FROM cart WHERE user_id = ? AND car_id = ?";
    $stmt = $db->prepare($cartQuery);
    $stmt->execute([$userId, $carId]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + $quantity;
        $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->execute([$newQuantity, $existingItem['id']]);
    } else {
        // Insert new
        $insertQuery = "INSERT INTO cart (user_id, car_id, quantity) VALUES (?, ?, ?)";
        $stmt = $db->prepare($insertQuery);
        $stmt->execute([$userId, $carId, $quantity]);
    }
    
    // Get updated cart count
    $countQuery = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $db->prepare($countQuery);
    $stmt->execute([$userId]);
    $count = $stmt->fetch()['count'];
    
    jsonResponse([
        'success' => true,
        'message' => 'Item added to cart',
        'cart_count' => $count
    ]);
}

function removeFromCart($db) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    
    $userId = $_SESSION['user_id'];
    $carId = (int)($_GET['car_id'] ?? 0);
    
    if (!$carId) {
        jsonResponse(['error' => 'Car ID is required'], 400);
    }
    
    $query = "DELETE FROM cart WHERE user_id = ? AND car_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId, $carId]);
    
    jsonResponse(['success' => true, 'message' => 'Item removed from cart']);
}

function clearCart($db) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    
    $userId = $_SESSION['user_id'];
    
    $query = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    
    jsonResponse(['success' => true, 'message' => 'Cart cleared']);
}

function processCheckout($db) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Please login to checkout'], 401);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $paymentMethod = sanitize($data['payment_method'] ?? '');
    $currency = strtoupper($data['currency'] ?? 'USD');
    $paymentDetails = $data['payment_details'] ?? [];
    
    if (empty($paymentMethod)) {
        jsonResponse(['error' => 'Payment method is required'], 400);
    }
    
    // Get cart items
    $query = "SELECT c.*, cars.price, cars.stock FROM cart c 
              JOIN cars ON c.car_id = cars.id WHERE c.user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    
    if (empty($items)) {
        jsonResponse(['error' => 'Cart is empty'], 400);
    }
    
    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Currency conversion
    $rates = [
        'USD' => 1, 'EUR' => 0.92, 'GBP' => 0.79, 'JPY' => 149.50,
        'CAD' => 1.36, 'AUD' => 1.53, 'CHF' => 0.88, 'CNY' => 7.24,
        'INR' => 83.12, 'BTC' => 0.000024
    ];
    
    $rate = $rates[$currency] ?? 1;
    $convertedTotal = round($total * $rate, 2);
    
    try {
        $db->beginTransaction();
        
        // Create order (Status: pending)
        $orderQuery = "INSERT INTO orders (user_id, total_amount, currency, payment_method, payment_details, status) 
                       VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $db->prepare($orderQuery);
        $stmt->execute([
            $userId, 
            $convertedTotal, 
            $currency, 
            $paymentMethod, 
            json_encode($paymentDetails)
        ]);
        
        $orderId = $db->lastInsertId();
        
        // Add order items
        $itemQuery = "INSERT INTO order_items (order_id, car_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($itemQuery);
        
        foreach ($items as $item) {
            $stmt->execute([$orderId, $item['car_id'], $item['quantity'], $item['price']]);
            
            // Update stock
            $stockQuery = "UPDATE cars SET stock = stock - ? WHERE id = ?";
            $stockStmt = $db->prepare($stockQuery);
            $stockStmt->execute([$item['quantity'], $item['car_id']]);
        }
        
        // Clear cart
        $clearQuery = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($clearQuery);
        $stmt->execute([$userId]);
        
        $db->commit();
        
        jsonResponse([
            'success' => true,
            'message' => 'Order placed successfully. Waiting for admin approval.',
            'order_id' => $orderId,
            'status' => 'pending'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['error' => 'Order processing failed: ' . $e->getMessage()], 500);
    }
}

function getUserOrders($db) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    
    $userId = $_SESSION['user_id'];
    
    $query = "SELECT o.* 
              FROM orders o
              WHERE o.user_id = ?
              ORDER BY o.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
    
    // Get items for each order
    foreach ($orders as &$order) {
        $itemsQuery = "SELECT oi.*, cars.brand, cars.model, cars.year 
                       FROM order_items oi 
                       JOIN cars ON oi.car_id = cars.id 
                       WHERE oi.order_id = ?";
        $stmt = $db->prepare($itemsQuery);
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
    }
    
    jsonResponse(['success' => true, 'orders' => $orders]);
}

function confirmPayment($db) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $orderId = (int)($data['order_id'] ?? 0);
    $method = sanitize($data['method'] ?? '');
    $reference = sanitize($data['reference'] ?? '');
    
    if (!$orderId) {
        jsonResponse(['error' => 'Order ID required'], 400);
    }
    
    // Verify order belongs to user and is approved
    $checkQuery = "SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'approved'";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute([$orderId, $userId]);
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'Order not found or not approved for payment'], 404);
    }
    
    // Update order status to completed
    $updateQuery = "UPDATE orders SET status = 'completed', payment_method = ?, payment_details = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $db->prepare($updateQuery);
    $stmt->execute([$method, json_encode(['reference' => $reference, 'paid_at' => date('Y-m-d H:i:s')]), $orderId]);
    
    jsonResponse(['success' => true, 'message' => 'Payment recorded successfully']);
}
?>