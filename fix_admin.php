<?php

// Connect to database
 $conn = new mysqli("localhost", "root", "", "bestcarseller");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Delete all existing admins
 $conn->query("DELETE FROM users WHERE role = 'admin'");

// 2. Create the new password hash for "Carseller123"
 $password = "Carseller123";
 $hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 3. Insert the new admin
 $sql = "INSERT INTO users (username, email, password, full_name, role) 
        VALUES ('carseller_admin', 'admin@bestcarseller.com', ?, 'System Admin', 'admin')";

 $stmt = $conn->prepare($sql);
 $stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "<h1>Success!</h1>";
    echo "<p>Admin created successfully.</p>";
    echo "<p><strong>Username:</strong> carseller_admin</p>";
    echo "<p><strong>Password:</strong> Carseller123</p>";
    echo "<p>You can now <a href='login.php'>Login Here</a>.</p>";
} else {
    echo "Error: " . $stmt->error;
}

 $conn->close();
?>