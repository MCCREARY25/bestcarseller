<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

 $method = $_SERVER['REQUEST_METHOD'];
 $database = new Database();
 $db = $database->getConnection();

 $action = $_GET['action'] ?? '';

// Currency conversion rates (base: USD)
 $conversionRates = [
    'USD' => 1,
    'EUR' => 0.92,
    'GBP' => 0.79,
    'JPY' => 149.50,
    'CAD' => 1.36,
    'AUD' => 1.53,
    'CHF' => 0.88,
    'CNY' => 7.24,
    'INR' => 83.12,
    'BTC' => 0.000024
];

 $currencySymbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'JPY' => '¥',
    'CAD' => 'C$',
    'AUD' => 'A$',
    'CHF' => 'CHF',
    'CNY' => '¥',
    'INR' => '₹',
    'BTC' => '₿'
];

switch ($action) {
    case 'types':
        getCarTypes($db);
        break;
    case 'by_type':
        getCarsByType($db, $conversionRates, $currencySymbols);
        break;
    case 'featured':
        getFeaturedCars($db, $conversionRates, $currencySymbols);
        break;
    case 'search':
        searchCars($db, $conversionRates, $currencySymbols);
        break;
    case 'single':
        getSingleCar($db, $conversionRates, $currencySymbols);
        break;
    case 'all':
        getAllCars($db, $conversionRates, $currencySymbols);
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function getCarTypes($db) {
    $query = "SELECT ct.*, COUNT(c.id) as car_count 
              FROM car_types ct 
              LEFT JOIN cars c ON ct.id = c.type_id 
              GROUP BY ct.id 
              ORDER BY ct.name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $types = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'types' => $types]);
}

function getCarsByType($db, $rates, $symbols) {
    $typeId = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 0;
    $currency = isset($_GET['currency']) ? strtoupper($_GET['currency']) : 'USD';
    
    if (!$typeId) {
        jsonResponse(['error' => 'Type ID is required'], 400);
    }
    
    $query = "SELECT c.*, ct.name as type_name, ct.icon as type_icon 
              FROM cars c 
              JOIN car_types ct ON c.type_id = ct.id 
              WHERE c.type_id = ? AND c.stock > 0 
              ORDER BY c.featured DESC, c.price ASC 
              LIMIT 20";
    $stmt = $db->prepare($query);
    $stmt->execute([$typeId]);
    $cars = $stmt->fetchAll();
    
    // Convert prices
    $rate = $rates[$currency] ?? 1;
    $symbol = $symbols[$currency] ?? '$';
    
    foreach ($cars as &$car) {
        $car['original_price'] = $car['price'];
        $car['price'] = round($car['price'] * $rate, 2);
        $car['currency'] = $currency;
        $car['currency_symbol'] = $symbol;
        $car['formatted_price'] = $symbol . number_format($car['price'], 2);
    }
    
    jsonResponse(['success' => true, 'cars' => $cars, 'currency' => $currency]);
}

function getFeaturedCars($db, $rates, $symbols) {
    $currency = isset($_GET['currency']) ? strtoupper($_GET['currency']) : 'USD';
    
    $query = "SELECT c.*, ct.name as type_name 
              FROM cars c 
              JOIN car_types ct ON c.type_id = ct.id 
              WHERE c.featured = 1 AND c.stock > 0 
              ORDER BY RAND() 
              LIMIT 8";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $cars = $stmt->fetchAll();
    
    $rate = $rates[$currency] ?? 1;
    $symbol = $symbols[$currency] ?? '$';
    
    foreach ($cars as &$car) {
        $car['original_price'] = $car['price'];
        $car['price'] = round($car['price'] * $rate, 2);
        $car['currency'] = $currency;
        $car['currency_symbol'] = $symbol;
        $car['formatted_price'] = $symbol . number_format($car['price'], 2);
    }
    
    jsonResponse(['success' => true, 'cars' => $cars]);
}

function searchCars($db, $rates, $symbols) {
    $keyword = isset($_GET['q']) ? sanitize($_GET['q']) : '';
    $currency = isset($_GET['currency']) ? strtoupper($_GET['currency']) : 'USD';
    
    if (strlen($keyword) < 2) {
        jsonResponse(['error' => 'Search term must be at least 2 characters'], 400);
    }
    
    $query = "SELECT c.*, ct.name as type_name 
              FROM cars c 
              JOIN car_types ct ON c.type_id = ct.id 
              WHERE (c.model LIKE ? OR c.brand LIKE ? OR c.description LIKE ?) AND c.stock > 0 
              ORDER BY c.featured DESC, c.price ASC 
              LIMIT 50";
    
    $searchTerm = "%{$keyword}%";
    $stmt = $db->prepare($query);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $cars = $stmt->fetchAll();
    
    $rate = $rates[$currency] ?? 1;
    $symbol = $symbols[$currency] ?? '$';
    
    foreach ($cars as &$car) {
        $car['original_price'] = $car['price'];
        $car['price'] = round($car['price'] * $rate, 2);
        $car['currency'] = $currency;
        $car['currency_symbol'] = $symbol;
        $car['formatted_price'] = $symbol . number_format($car['price'], 2);
    }
    
    jsonResponse(['success' => true, 'cars' => $cars, 'count' => count($cars)]);
}

function getSingleCar($db, $rates, $symbols) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $currency = isset($_GET['currency']) ? strtoupper($_GET['currency']) : 'USD';
    
    if (!$id) {
        jsonResponse(['error' => 'Car ID is required'], 400);
    }
    
    $query = "SELECT c.*, ct.name as type_name 
              FROM cars c 
              JOIN car_types ct ON c.type_id = ct.id 
              WHERE c.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $car = $stmt->fetch();
    
    if (!$car) {
        jsonResponse(['error' => 'Car not found'], 404);
    }
    
    $rate = $rates[$currency] ?? 1;
    $symbol = $symbols[$currency] ?? '$';
    
    $car['original_price'] = $car['price'];
    $car['price'] = round($car['price'] * $rate, 2);
    $car['currency'] = $currency;
    $car['currency_symbol'] = $symbol;
    $car['formatted_price'] = $symbol . number_format($car['price'], 2);
    
    jsonResponse(['success' => true, 'car' => $car]);
}

function getAllCars($db, $rates, $symbols) {
    $currency = isset($_GET['currency']) ? strtoupper($_GET['currency']) : 'USD';
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
    
    $query = "SELECT c.*, ct.name as type_name 
              FROM cars c 
              JOIN car_types ct ON c.type_id = ct.id 
              WHERE c.stock > 0 
              ORDER BY c.featured DESC, c.created_at DESC 
              LIMIT ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$limit]);
    $cars = $stmt->fetchAll();
    
    $rate = $rates[$currency] ?? 1;
    $symbol = $symbols[$currency] ?? '$';
    
    foreach ($cars as &$car) {
        $car['original_price'] = $car['price'];
        $car['price'] = round($car['price'] * $rate, 2);
        $car['currency'] = $currency;
        $car['currency_symbol'] = $symbol;
        $car['formatted_price'] = $symbol . number_format($car['price'], 2);
    }
    
    jsonResponse(['success' => true, 'cars' => $cars]);
}
?>