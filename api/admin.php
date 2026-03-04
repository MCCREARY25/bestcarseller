<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

 $database = new Database();
 $db = $database->getConnection();
 $action = $_GET['action'] ?? '';

// Check admin access for all actions
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(['error' => 'Admin access required'], 403);
}

switch ($action) {
    case 'dashboard':
        getDashboardStats($db);
        break;
    case 'orders':
        getOrders($db);
        break;
    case 'order_action':
        handleOrderAction($db);
        break;
    case 'users':
        getUsers($db);
        break;
    case 'cars':
        manageCars($db);
        break;
    case 'subscribers':
        getSubscribers($db);
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function getDashboardStats($db) {
    $stats = [];
    
    // Total users
    $query = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total cars
    $query = "SELECT COUNT(*) as count FROM cars";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_cars'] = $stmt->fetch()['count'];
    
    // Total orders
    $query = "SELECT COUNT(*) as count FROM orders";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_orders'] = $stmt->fetch()['count'];
    
    // Orders by status
    $query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['orders_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Total revenue (approved/completed orders)
    $query = "SELECT SUM(total_amount) as total FROM orders WHERE status IN ('approved', 'completed')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
    
    // Recent orders
    $query = "SELECT o.*, u.username, u.email 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['recent_orders'] = $stmt->fetchAll();
    
    // Low stock cars
    $query = "SELECT * FROM cars WHERE stock < 5 ORDER BY stock ASC LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['low_stock'] = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'stats' => $stats]);
}

function getOrders($db) {
    $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
    
    $query = "SELECT o.*, u.username, u.email, u.full_name,
              GROUP_CONCAT(CONCAT(cars.brand, ' ', cars.model, ' (', oi.quantity, ')') SEPARATOR ', ') as items
              FROM orders o
              JOIN users u ON o.user_id = u.id
              JOIN order_items oi ON o.id = oi.order_id
              JOIN cars ON oi.car_id = cars.id";
    
    if ($status) {
        $query .= " WHERE o.status = ?";
    }
    
    $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($status ? [$status] : []);
    $orders = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'orders' => $orders]);
}

function handleOrderAction($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = (int)($data['order_id'] ?? 0);
    $action = sanitize($data['action'] ?? '');
    $notes = sanitize($data['notes'] ?? '');
    
    if (!$orderId || !in_array($action, ['approve', 'reject', 'complete'])) {
        jsonResponse(['error' => 'Invalid request'], 400);
    }
    
    $status = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : 'completed');
    
    $query = "UPDATE orders SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$status, $notes, $orderId]);
    
    // If rejected, restore stock
    if ($status === 'rejected') {
        $itemsQuery = "SELECT car_id, quantity FROM order_items WHERE order_id = ?";
        $stmt = $db->prepare($itemsQuery);
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        
        foreach ($items as $item) {
            $updateStock = "UPDATE cars SET stock = stock + ? WHERE id = ?";
            $stmt = $db->prepare($updateStock);
            $stmt->execute([$item['quantity'], $item['car_id']]);
        }
    }
    
    jsonResponse([
        'success' => true,
        'message' => "Order {$status} successfully",
        'new_status' => $status
    ]);
}

function getUsers($db) {
    $query = "SELECT id, username, email, full_name, phone, role, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'users' => $users]);
}

function manageCars($db) {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $query = "SELECT c.*, ct.name as type_name FROM cars c 
                  JOIN car_types ct ON c.type_id = ct.id 
                  ORDER BY c.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $cars = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'cars' => $cars]);
    }
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "INSERT INTO cars (type_id, model, brand, year, price, description, durability, warranty, 
                  mileage, fuel_type, transmission, engine, horsepower, color, stock, featured) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['type_id'],
            sanitize($data['model']),
            sanitize($data['brand']),
            (int)$data['year'],
            (float)$data['price'],
            sanitize($data['description']),
            sanitize($data['durability']),
            sanitize($data['warranty']),
            sanitize($data['mileage']),
            sanitize($data['fuel_type']),
            sanitize($data['transmission']),
            sanitize($data['engine']),
            (int)$data['horsepower'],
            sanitize($data['color']),
            (int)$data['stock'],
            isset($data['featured']) ? 1 : 0
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Car added successfully', 'id' => $db->lastInsertId()]);
    }
    
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        
        $query = "UPDATE cars SET type_id=?, model=?, brand=?, year=?, price=?, description=?, 
                  durability=?, warranty=?, mileage=?, fuel_type=?, transmission=?, engine=?, 
                  horsepower=?, color=?, stock=?, featured=? WHERE id=?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['type_id'],
            sanitize($data['model']),
            sanitize($data['brand']),
            (int)$data['year'],
            (float)$data['price'],
            sanitize($data['description']),
            sanitize($data['durability']),
            sanitize($data['warranty']),
            sanitize($data['mileage']),
            sanitize($data['fuel_type']),
            sanitize($data['transmission']),
            sanitize($data['engine']),
            (int)$data['horsepower'],
            sanitize($data['color']),
            (int)$data['stock'],
            isset($data['featured']) ? 1 : 0,
            $id
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Car updated successfully']);
    }
    
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            jsonResponse(['error' => 'Car ID is required'], 400);
        }
        
        $query = "DELETE FROM cars WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        jsonResponse(['success' => true, 'message' => 'Car deleted successfully']);
    }
}

function getSubscribers($db) {
    $query = "SELECT * FROM subscribers ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $subscribers = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'subscribers' => $subscribers]);
}
?>