<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

 $method = $_SERVER['REQUEST_METHOD'];
 $database = new Database();
 $db = $database->getConnection();

// Handle preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

 $action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($db);
        break;
    case 'signup':
        handleSignup($db);
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        checkAuth();
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function handleLogin($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['username']) || empty($data['password'])) {
        jsonResponse(['error' => 'Username and password are required'], 400);
    }
    
    $username = sanitize($data['username']);
    $password = $data['password'];
    
    $query = "SELECT id, username, email, password, full_name, role FROM users WHERE username = ? OR email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse([
            'error' => 'User not found. Please sign up.',
            'redirect' => 'signup.php'
        ], 404);
    }
    
    if (!password_verify($password, $user['password'])) {
        jsonResponse(['error' => 'Invalid password'], 401);
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    $isAdmin = $user['role'] === 'admin';
    
    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ],
        'redirect' => $isAdmin ? 'admin.php' : 'dashboard.php'
    ]);
}

function handleSignup($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['username', 'email', 'password', 'full_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
        }
    }
    
    $username = sanitize($data['username']);
    $email = sanitize($data['email']);
    $password = $data['password'];
    $full_name = sanitize($data['full_name']);
    $phone = sanitize($data['phone'] ?? '');
    $address = sanitize($data['address'] ?? '');
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Invalid email format'], 400);
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        jsonResponse(['error' => 'Password must be at least 8 characters'], 400);
    }
    
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        jsonResponse(['error' => 'Password must contain at least one uppercase letter and one number'], 400);
    }
    
    // Check if username exists
    $query = "SELECT id FROM users WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Username already exists'], 409);
    }
    
    // Check if email exists
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Email already registered'], 409);
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Insert user
    $query = "INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$username, $email, $hashedPassword, $full_name, $phone, $address])) {
        jsonResponse([
            'success' => true,
            'message' => 'Registration successful. Please login.',
            'redirect' => 'login.php'
        ]);
    } else {
        jsonResponse(['error' => 'Registration failed. Please try again.'], 500);
    }
}

function handleLogout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    jsonResponse([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => 'index.php'
    ]);
}

function checkAuth() {
    if (isset($_SESSION['user_id'])) {
        jsonResponse([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        jsonResponse(['authenticated' => false]);
    }
}
?>