-- setup.sql - Run this first to set up the database

CREATE DATABASE IF NOT EXISTS bestcarseller;
USE bestcarseller;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Car types/categories
CREATE TABLE car_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_id INT NOT NULL,
    model VARCHAR(100) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    description TEXT,
    durability VARCHAR(100),
    warranty VARCHAR(100),
    mileage VARCHAR(50),
    fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid'),
    transmission ENUM('Automatic', 'Manual', 'CVT', 'Dual-Clutch'),
    engine VARCHAR(50),
    horsepower INT,
    color VARCHAR(30),
    stock INT DEFAULT 10,
    image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES car_types(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_car (user_id, car_id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    payment_method VARCHAR(50) NOT NULL,
    payment_details TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    car_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Subscribers table
CREATE TABLE subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin_bestcarseller', 'admin@bestcarseller.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'admin');

-- Insert car types
INSERT INTO car_types (name, description, icon) VALUES 
('Sedan', 'Perfect for daily commuting and family trips', 'sedan'),
('SUV', 'Spacious vehicles for all terrains', 'suv'),
('Sports Car', 'High performance and speed', 'sports'),
('Electric', 'Zero emission vehicles', 'electric'),
('Truck', 'Heavy duty hauling capability', 'truck'),
('Coupe', 'Stylish two-door vehicles', 'coupe'),
('Hatchback', 'Compact and practical', 'hatchback'),
('Convertible', 'Open-top driving experience', 'convertible'),
('Luxury', 'Premium comfort and features', 'luxury'),
('Hybrid', 'Best of both worlds', 'hybrid');

-- Insert sample cars (20+ models per type would be extensive, here's a representative sample)
INSERT INTO cars (type_id, model, brand, year, price, description, durability, warranty, mileage, fuel_type, transmission, engine, horsepower, color, stock, featured) VALUES
-- Sedans
(1, 'Camry XSE', 'Toyota', 2024, 32990.00, 'The 2024 Toyota Camry XSE combines reliability with sporty styling. Features premium leather interior and advanced safety systems.', 'Excellent - Built to last 200,000+ miles', '5 years/60,000 miles', '28 MPG City / 39 MPG Highway', 'Petrol', 'Automatic', '3.5L V6', 301, 'Celestial Silver', 15, TRUE),
(1, 'Accord Sport', 'Honda', 2024, 31895.00, 'Honda Accord Sport offers dynamic performance with a turbocharged engine and modern tech features.', 'Excellent - Honda reliability', '5 years/60,000 miles', '30 MPG City / 38 MPG Highway', 'Petrol', 'Automatic', '2.0L Turbo', 252, 'Platinum White', 12, FALSE),
(1, 'Sonata SEL Plus', 'Hyundai', 2024, 28995.00, 'Stylish sedan with cutting-edge technology and exceptional value.', 'Very Good', '5 years/60,000 miles', '27 MPG City / 37 MPG Highway', 'Petrol', 'Automatic', '2.5L 4-Cylinder', 191, 'Shale Gray', 20, FALSE),
(1, 'Altima SR', 'Nissan', 2024, 29990.00, 'Sporty sedan with available all-wheel drive and premium interior.', 'Very Good', '3 years/36,000 miles', '28 MPG City / 39 MPG Highway', 'Petrol', 'CVT', '2.0L VC-Turbo', 248, 'Storm Blue', 18, FALSE),
(1, 'Mazda6 Signature', 'Mazda', 2024, 35990.00, 'Premium sedan with luxury features and engaging driving dynamics.', 'Excellent', '3 years/36,000 miles', '26 MPG City / 35 MPG Highway', 'Petrol', 'Automatic', '2.5L Turbo', 250, 'Soul Red Crystal', 8, TRUE),
(1, 'Optima EX', 'Kia', 2024, 27990.00, 'Value-packed sedan with bold styling and comprehensive warranty.', 'Very Good', '10 years/100,000 miles', '27 MPG City / 37 MPG Highway', 'Petrol', 'Automatic', '2.5L 4-Cylinder', 191, 'Wolf Gray', 25, FALSE),
(1, 'Legacy Touring XT', 'Subaru', 2024, 35995.00, 'Standard all-wheel drive sedan with turbocharged power.', 'Excellent', '3 years/36,000 miles', '24 MPG City / 32 MPG Highway', 'Petrol', 'CVT', '2.4L Turbo', 260, 'Crystal Black', 10, FALSE),
(1, 'Passat R-Line', 'Volkswagen', 2024, 29995.00, 'German engineering with spacious interior and smooth ride.', 'Very Good', '4 years/50,000 miles', '28 MPG City / 38 MPG Highway', 'Petrol', 'Automatic', '2.0L Turbo', 174, 'Oryx White', 14, FALSE),
(1, 'Malibu LT', 'Chevrolet', 2024, 27995.00, 'American sedan with comfortable ride and modern connectivity.', 'Good', '3 years/36,000 miles', '29 MPG City / 39 MPG Highway', 'Petrol', 'CVT', '1.5L Turbo', 160, 'Cajun Red', 22, FALSE),
(1, 'Fusion Titanium', 'Ford', 2023, 32995.00, 'Elegant sedan with hybrid option and advanced driver aids.', 'Very Good', '3 years/36,000 miles', '27 MPG City / 38 MPG Highway', 'Petrol', 'Automatic', '2.0L EcoBoost', 245, 'Velocity Blue', 16, FALSE),
(1, 'Corolla Apex', 'Toyota', 2024, 26995.00, 'Sport-tuned compact sedan with aggressive styling.', 'Excellent', '5 years/60,000 miles', '31 MPG City / 41 MPG Highway', 'Petrol', 'CVT', '2.0L 4-Cylinder', 169, 'Ice Cap White', 30, FALSE),
(1, 'Civic Touring', 'Honda', 2024, 29995.00, 'Premium compact sedan with upscale features.', 'Excellent', '5 years/60,000 miles', '33 MPG City / 42 MPG Highway', 'Petrol', 'CVT', '1.5L Turbo', 180, 'Aegean Blue', 25, TRUE),
(1, 'Sentra SR', 'Nissan', 2024, 24995.00, 'Value-oriented compact with surprising premium touches.', 'Very Good', '3 years/36,000 miles', '30 MPG City / 40 MPG Highway', 'Petrol', 'CVT', '2.0L 4-Cylinder', 149, 'Gun Metallic', 28, FALSE),
(1, 'Elantra N Line', 'Hyundai', 2024, 26995.00, 'Sport-tuned compact with turbocharged performance.', 'Very Good', '5 years/60,000 miles', '28 MPG City / 36 MPG Highway', 'Petrol', 'Dual-Clutch', '1.6L Turbo', 201, 'Cyber Gray', 20, FALSE),
(1, 'Fortro GT', 'Kia', 2024, 25995.00, 'Stylish compact with sporty handling and tech features.', 'Very Good', '10 years/100,000 miles', '29 MPG City / 39 MPG Highway', 'Petrol', 'CVT', '2.0L 4-Cylinder', 147, 'Aurora Black', 35, FALSE),
(1, 'Impreza Sport', 'Subaru', 2024, 26995.00, 'Compact sedan with standard all-wheel drive.', 'Excellent', '3 years/36,000 miles', '28 MPG City / 36 MPG Highway', 'Petrol', 'CVT', '2.0L 4-Cylinder', 152, 'Plasma Blue', 18, FALSE),
(1, 'Jetta SEL', 'Volkswagen', 2024, 27995.00, 'European driving dynamics with spacious interior.', 'Very Good', '4 years/50,000 miles', '31 MPG City / 41 MPG Highway', 'Petrol', 'Automatic', '1.5L Turbo', 158, 'Deep Black Pearl', 24, FALSE),
(1, 'Mirage G4 SE', 'Mitsubishi', 2024, 18995.00, 'Budget-friendly sedan with excellent fuel economy.', 'Good', '5 years/60,000 miles', '36 MPG City / 43 MPG Highway', 'Petrol', 'CVT', '1.2L 3-Cylinder', 78, 'Wine Red', 40, FALSE),
(1, 'Versa SR', 'Nissan', 2024, 20995.00, 'Affordable sedan with available premium features.', 'Good', '3 years/36,000 miles', '32 MPG City / 40 MPG Highway', 'Petrol', 'CVT', '1.6L 4-Cylinder', 122, 'Storm Blue', 45, FALSE),
(1, 'A3 Premium', 'Audi', 2024, 38995.00, 'Luxury compact sedan with Quattro all-wheel drive.', 'Excellent', '4 years/50,000 miles', '29 MPG City / 38 MPG Highway', 'Petrol', 'Dual-Clutch', '2.0L Turbo', 201, 'Mythos Black', 12, FALSE),

-- SUVs
(2, 'RAV4 Prime', 'Toyota', 2024, 42995.00, 'Plug-in hybrid SUV with impressive electric range and performance.', 'Excellent', '5 years/60,000 miles', '94 MPGe Combined', 'Plug-in Hybrid', 'Automatic', '2.5L 4-Cylinder Hybrid', 302, 'Supersonic Red', 8, TRUE),
(2, 'CR-V Hybrid', 'Honda', 2024, 36995.00, 'Spacious hybrid SUV with excellent fuel economy.', 'Excellent', '5 years/60,000 miles', '38 MPG Combined', 'Hybrid', 'Automatic', '2.0L Hybrid', 204, 'Platinum White', 20, FALSE),
(2, 'Tucson Hybrid', 'Hyundai', 2024, 33995.00, 'Bold design with efficient hybrid powertrain.', 'Very Good', '5 years/60,000 miles', '38 MPG Combined', 'Hybrid', 'Automatic', '1.6L Turbo Hybrid', 226, 'Shale Gray', 15, FALSE),
(2, 'Sportage X-Pro', 'Kia', 2024, 34995.00, 'Adventure-ready SUV with off-road capability.', 'Very Good', '10 years/100,000 miles', '28 MPG Combined', 'Petrol', 'Automatic', '2.5L 4-Cylinder', 187, 'Jungle Wood', 18, FALSE),
(2, 'CX-5 Turbo', 'Mazda', 2024, 38995.00, 'Premium compact SUV with engaging driving dynamics.', 'Excellent', '3 years/36,000 miles', '24 MPG Combined', 'Petrol', 'Automatic', '2.5L Turbo', 256, 'Soul Red Crystal', 12, TRUE),
(2, 'Equinox Premier', 'Chevrolet', 2024, 33995.00, 'Family-friendly SUV with advanced safety features.', 'Very Good', '3 years/36,000 miles', '28 MPG Combined', 'Petrol', 'Automatic', '1.5L Turbo', 175, 'Cajun Red', 25, FALSE),
(2, 'Escape PHEV', 'Ford', 2024, 39995.00, 'Plug-in hybrid with excellent electric range.', 'Very Good', '3 years/36,000 miles', '100 MPGe Combined', 'Plug-in Hybrid', 'Automatic', '2.5L Hybrid', 210, 'Carbonized Gray', 10, FALSE),
(2, 'Rogue Platinum', 'Nissan', 2024, 37995.00, 'Premium compact SUV with innovative features.', 'Very Good', '3 years/36,000 miles', '30 MPG Combined', 'Petrol', 'CVT', '1.5L VC-Turbo', 201, 'Boulder Gray', 22, FALSE),
(2, 'Forester Wilderness', 'Subaru', 2024, 35995.00, 'Off-road oriented SUV with standard AWD.', 'Excellent', '3 years/36,000 miles', '28 MPG Combined', 'Petrol', 'CVT', '2.5L 4-Cylinder', 182, 'Geyser Blue', 14, FALSE),
(2, 'Tiguan SEL', 'Volkswagen', 2024, 39995.00, 'German SUV with optional third row seating.', 'Very Good', '4 years/50,000 miles', '26 MPG Combined', 'Petrol', 'Automatic', '2.0L Turbo', 184, 'Deep Black Pearl', 16, FALSE),
(2, 'CX-9 Signature', 'Mazda', 2024, 48995.00, 'Three-row luxury SUV with premium interior.', 'Excellent', '3 years/36,000 miles', '24 MPG Combined', 'Petrol', 'Automatic', '2.5L Turbo', 250, 'Snowflake White', 8, FALSE),
(2, 'Palisade Limited', 'Hyundai', 2024, 48995.00, 'Premium three-row SUV with luxury features.', 'Very Good', '5 years/60,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '3.8L V6', 291, 'Moonlight Cloud', 12, TRUE),
(2, 'Telluride SX', 'Kia', 2024, 46995.00, 'Award-winning three-row SUV with bold styling.', 'Very Good', '10 years/100,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '3.8L V6', 291, 'Wolf Gray', 15, FALSE),
(2, 'Highlander Platinum', 'Toyota', 2024, 52995.00, 'Premium three-row with hybrid efficiency.', 'Excellent', '5 years/60,000 miles', '36 MPG Combined', 'Hybrid', 'Automatic', '2.5L Hybrid', 243, 'Wind Chill Pearl', 6, FALSE),
(2, 'Pilot Elite', 'Honda', 2024, 50995.00, 'New generation three-row with improved space.', 'Excellent', '5 years/60,000 miles', '21 MPG Combined', 'Petrol', 'Automatic', '3.5L V6', 285, 'Radiant Red', 10, FALSE),
(2, 'Explorer ST', 'Ford', 2024, 54995.00, 'Performance-oriented SUV with sport-tuned suspension.', 'Very Good', '3 years/36,000 miles', '20 MPG Combined', 'Petrol', 'Automatic', '3.0L V6 Turbo', 400, 'Star White', 8, FALSE),
(2, 'Traverse High Country', 'Chevrolet', 2024, 52995.00, 'Spacious three-row with premium features.', 'Very Good', '3 years/36,000 miles', '21 MPG Combined', 'Petrol', 'Automatic', '3.6L V6', 310, 'Northsky Blue', 12, FALSE),
(2, 'Q7 Premium Plus', 'Audi', 2024, 62995.00, 'Luxury three-row with Quattro all-wheel drive.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '2.0L Turbo', 261, 'Navarra Blue', 6, FALSE),
(2, 'X5 xDrive40i', 'BMW', 2024, 65995.00, 'Luxury midsize SUV with advanced technology.', 'Excellent', '4 years/50,000 miles', '23 MPG Combined', 'Petrol', 'Automatic', '3.0L Turbo I6', 335, 'Phytonic Blue', 5, FALSE),
(2, 'GLE 450', 'Mercedes-Benz', 2024, 67995.00, 'Premium luxury SUV with cutting-edge features.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '3.0L Turbo I6', 362, 'Selenite Gray', 4, FALSE),

-- Sports Cars
(3, 'Mustang GT', 'Ford', 2024, 42995.00, 'Iconic American muscle car with powerful V8 engine.', 'Very Good', '3 years/36,000 miles', '16 MPG Combined', 'Petrol', 'Manual', '5.0L V8', 480, 'Race Red', 20, TRUE),
(3, 'Camaro SS', 'Chevrolet', 2024, 40995.00, 'Aggressive sports car with track-ready performance.', 'Very Good', '3 years/36,000 miles', '17 MPG Combined', 'Petrol', 'Manual', '6.2L V8', 455, 'Summit White', 18, FALSE),
(3, 'Challenger R/T', 'Dodge', 2024, 41995.00, 'Classic muscle car styling with modern power.', 'Very Good', '3 years/36,000 miles', '16 MPG Combined', 'Petrol', 'Manual', '5.7L V8', 375, 'F8 Green', 15, FALSE),
(3, 'GR Supra', 'Toyota', 2024, 55995.00, 'Legendary sports car with turbocharged power.', 'Excellent', '3 years/36,000 miles', '26 MPG Combined', 'Petrol', 'Automatic', '3.0L Turbo I6', 382, 'Nitro Yellow', 10, TRUE),
(3, 'GR86 Premium', 'Toyota', 2024, 31995.00, 'Lightweight sports car with perfect balance.', 'Excellent', '3 years/36,000 miles', '24 MPG Combined', 'Petrol', 'Manual', '2.4L 4-Cylinder', 228, 'Trueno Blue', 25, FALSE),
(3, 'BRZ Limited', 'Subaru', 2024, 32995.00, 'Driver-focused sports car with rear-wheel drive.', 'Excellent', '3 years/36,000 miles', '24 MPG Combined', 'Petrol', 'Manual', '2.4L 4-Cylinder', 228, 'Ice Silver', 20, FALSE),
(3, 'Miata RF', 'Mazda', 2024, 37995.00, 'Iconic roadster with retractable fastback roof.', 'Excellent', '3 years/36,000 miles', '30 MPG Combined', 'Petrol', 'Manual', '2.0L 4-Cylinder', 181, 'Soul Red Crystal', 15, FALSE),
(3, 'Z Performance', 'Nissan', 2024, 49995.00, 'Modern sports car with twin-turbo power.', 'Very Good', '3 years/36,000 miles', '22 MPG Combined', 'Petrol', 'Manual', '3.0L Twin-Turbo V6', 400, 'Seiran Blue', 12, FALSE),
(3, 'Corvette Stingray', 'Chevrolet', 2024, 68995.00, 'Mid-engine supercar performance at attainable price.', 'Excellent', '3 years/36,000 miles', '19 MPG Combined', 'Petrol', 'Automatic', '6.2L V8', 495, 'Torch Red', 8, TRUE),
(3, '911 Carrera', 'Porsche', 2024, 119995.00, 'Iconic German sports car with legendary handling.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Manual', '3.0L Twin-Turbo', 379, 'Guards Red', 5, FALSE),
(3, 'M4 Competition', 'BMW', 2024, 82995.00, 'High-performance coupe with twin-turbo power.', 'Excellent', '4 years/50,000 miles', '21 MPG Combined', 'Petrol', 'Automatic', '3.0L Twin-Turbo I6', 503, 'Isle of Man Green', 6, FALSE),
(3, 'RS5 Sportback', 'Audi', 2024, 79995.00, 'Luxury sports coupe with Quattro all-wheel drive.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '2.9L Twin-Turbo V6', 444, 'Tango Red', 4, FALSE),
(3, 'C63 AMG', 'Mercedes-Benz', 2024, 84995.00, 'High-performance luxury sports sedan.', 'Excellent', '4 years/50,000 miles', '21 MPG Combined', 'Petrol', 'Automatic', '4.0L V8', 469, 'Obsidian Black', 5, FALSE),
(3, 'F-Type R', 'Jaguar', 2024, 108995.00, 'British sports car with stunning design.', 'Very Good', '5 years/60,000 miles', '19 MPG Combined', 'Petrol', 'Automatic', '5.0L Supercharged V8', 575, 'British Racing Green', 3, FALSE),
(3, 'Vantage', 'Aston Martin', 2024, 149995.00, 'Exotic British sports car with stunning presence.', 'Very Good', '3 years/unlimited miles', '19 MPG Combined', 'Petrol', 'Automatic', '4.0L Twin-Turbo V8', 503, 'Aston Martin Racing Green', 2, FALSE),

-- Electric
(4, 'Model 3 Performance', 'Tesla', 2024, 54990.00, 'High-performance electric sedan with incredible acceleration.', 'Excellent - Battery 8 years', '4 years/50,000 miles', '113 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 513, 'Pearl White', 25, TRUE),
(4, 'Model Y Long Range', 'Tesla', 2024, 49990.00, 'Popular electric SUV with excellent range.', 'Excellent - Battery 8 years', '4 years/50,000 miles', '122 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 425, 'Midnight Silver', 30, FALSE),
(4, 'Ioniq 5 Limited', 'Hyundai', 2024, 53995.00, 'Award-winning EV with ultra-fast charging.', 'Very Good - Battery 10 years', '5 years/60,000 miles', '114 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 320, 'Digital Teal', 18, TRUE),
(4, 'EV6 GT-Line', 'Kia', 2024, 52995.00, 'Stylish electric crossover with fast charging.', 'Very Good - Battery 10 years', '10 years/100,000 miles', '117 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 320, 'Deep Forest Green', 20, FALSE),
(4, 'Mustang Mach-E GT', 'Ford', 2024, 59995.00, 'Electric SUV with Mustang heritage.', 'Very Good - Battery 8 years', '3 years/36,000 miles', '92 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 480, 'Grabber Blue', 15, FALSE),
(4, 'ID.4 Pro S', 'Volkswagen', 2024, 49995.00, 'Practical electric SUV for everyday use.', 'Very Good - Battery 8 years', '4 years/50,000 miles', '107 MPGe Combined', 'Electric', 'Automatic', 'Single Motor RWD', 201, 'Moonlight Blue', 22, FALSE),
(4, 'Rivian R1S', 'Rivian', 2024, 89900.00, 'Adventure-ready electric SUV with off-road capability.', 'Good - Battery 8 years', '5 years/60,000 miles', '84 MPGe Combined', 'Electric', 'Automatic', 'Quad Motor AWD', 835, 'Forest Green', 8, FALSE),
(4, 'Rivian R1T', 'Rivian', 2024, 84900.00, 'Revolutionary electric adventure truck.', 'Good - Battery 8 years', '5 years/60,000 miles', '82 MPGe Combined', 'Electric', 'Automatic', 'Quad Motor AWD', 835, 'Compass Yellow', 6, FALSE),
(4, 'e-tron GT', 'Audi', 2024, 108995.00, 'Luxury electric sports sedan.', 'Excellent - Battery 8 years', '4 years/50,000 miles', '85 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 522, 'Daytona Gray', 4, FALSE),
(4, 'iX xDrive50', 'BMW', 2024, 89995.00, 'Luxury electric SUV with futuristic design.', 'Excellent - Battery 8 years', '4 years/50,000 miles', '95 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 516, 'Phytonic Blue', 5, FALSE),
(4, 'EQS 450+', 'Mercedes-Benz', 2024, 104995.00, 'Ultra-luxury electric sedan with incredible range.', 'Excellent - Battery 10 years', '4 years/50,000 miles', '97 MPGe Combined', 'Electric', 'Automatic', 'Single Motor RWD', 329, 'Obsidian Black', 3, FALSE),
(4, 'Taycan 4S', 'Porsche', 2024, 114995.00, 'High-performance electric sports sedan.', 'Excellent - Battery 8 years', '4 years/50,000 miles', '83 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 482, 'Chalk', 4, FALSE),
(4, 'Lyriq', 'Cadillac', 2024, 64995.00, 'Luxury electric SUV with dramatic styling.', 'Very Good - Battery 8 years', '4 years/50,000 miles', '96 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 500, 'Stellar Black', 10, FALSE),

-- Trucks
(5, 'F-150 XLT', 'Ford', 2024, 47995.00, 'America\'s best-selling truck with proven capability.', 'Excellent', '5 years/60,000 miles', '20 MPG Combined', 'Petrol', 'Automatic', '3.5L V6', 400, 'Oxford White', 30, TRUE),
(5, 'Silverado LT', 'Chevrolet', 2024, 45995.00, 'Dependable full-size truck with advanced technology.', 'Very Good', '3 years/36,000 miles', '19 MPG Combined', 'Petrol', 'Automatic', '5.3L V8', 355, 'Summit White', 28, FALSE),
(5, 'Ram 1500 Big Horn', 'RAM', 2024, 46995.00, 'Luxurious interior meets rugged capability.', 'Very Good', '5 years/60,000 miles', '20 MPG Combined', 'Petrol', 'Automatic', '5.7L V8', 395, 'Bright White', 25, FALSE),
(5, 'Sierra SLE', 'GMC', 2024, 48995.00, 'Professional-grade truck with premium touches.', 'Very Good', '3 years/36,000 miles', '19 MPG Combined', 'Petrol', 'Automatic', '5.3L V8', 355, 'Onyx Black', 20, FALSE),
(5, 'Tundra SR5', 'Toyota', 2024, 45995.00, 'Reliable truck with powerful twin-turbo V6.', 'Excellent', '5 years/60,000 miles', '18 MPG Combined', 'Petrol', 'Automatic', '3.5L Twin-Turbo V6', 389, 'Ice Cap White', 22, FALSE),
(5, 'Titan SV', 'Nissan', 2024, 42995.00, 'Value-oriented full-size truck.', 'Good', '5 years/100,000 miles', '17 MPG Combined', 'Petrol', 'Automatic', '5.6L V8', 400, 'Gun Metallic', 15, FALSE),
(5, 'Lightning XLT', 'Ford', 2024, 62995.00, 'Electric F-150 with incredible power and utility.', 'Excellent - Battery 8 years', '5 years/60,000 miles', '70 MPGe Combined', 'Electric', 'Automatic', 'Dual Motor AWD', 580, 'Ice White', 12, TRUE),

-- Coupe
(6, 'M4 Competition', 'BMW', 2024, 82995.00, 'High-performance luxury coupe.', 'Excellent', '4 years/50,000 miles', '21 MPG Combined', 'Petrol', 'Automatic', '3.0L Twin-Turbo', 503, 'Isle of Man Green', 8, FALSE),
(6, 'RC F', 'Lexus', 2024, 68995.00, 'Japanese luxury coupe with V8 power.', 'Excellent', '4 years/50,000 miles', '19 MPG Combined', 'Petrol', 'Automatic', '5.0L V8', 472, 'Infrared', 6, FALSE),
(6, 'RS5', 'Audi', 2024, 79995.00, 'Sporty luxury coupe with Quattro AWD.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '2.9L Twin-Turbo V6', 444, 'Tango Red', 5, FALSE),
(6, 'C300 Coupe', 'Mercedes-Benz', 2024, 52995.00, 'Elegant luxury coupe with modern style.', 'Excellent', '4 years/50,000 miles', '27 MPG Combined', 'Petrol', 'Automatic', '2.0L Turbo', 255, 'Obsidian Black', 10, FALSE),
(6, 'Q60 Red Sport', 'Infiniti', 2024, 58995.00, 'Sport luxury coupe with twin-turbo power.', 'Very Good', '4 years/60,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '3.0L Twin-Turbo V6', 400, 'Dynamic Sunstone Red', 8, FALSE),

-- Hatchback
(7, 'Golf GTI', 'Volkswagen', 2024, 32995.00, 'Iconic hot hatch with perfect balance.', 'Very Good', '4 years/50,000 miles', '26 MPG Combined', 'Petrol', 'Manual', '2.0L Turbo', 241, 'Tornado Red', 20, TRUE),
(7, 'Civic Sport', 'Honda', 2024, 25995.00, 'Practical hatchback with sporty touches.', 'Excellent', '5 years/60,000 miles', '31 MPG Combined', 'Petrol', 'CVT', '2.0L 4-Cylinder', 158, 'Aegean Blue', 30, FALSE),
(7, 'Corolla Hatchback', 'Toyota', 2024, 24995.00, 'Reliable compact hatchback.', 'Excellent', '5 years/60,000 miles', '32 MPG Combined', 'Petrol', 'CVT', '2.0L 4-Cylinder', 169, 'Ice Cap White', 25, FALSE),
(7, 'Mazda3 Hatchback', 'Mazda', 2024, 27995.00, 'Premium compact with beautiful design.', 'Excellent', '3 years/36,000 miles', '28 MPG Combined', 'Petrol', 'Automatic', '2.5L 4-Cylinder', 191, 'Soul Red Crystal', 18, FALSE),
(7, 'Impreza Sport', 'Subaru', 2024, 26995.00, 'Hatchback with standard AWD.', 'Excellent', '3 years/36,000 miles', '28 MPG Combined', 'Petrol', 'CVT', '2.0L 4-Cylinder', 152, 'Plasma Yellow', 22, FALSE),

-- Convertible
(8, 'Miata RF', 'Mazda', 2024, 37995.00, 'Iconic roadster with retractable roof.', 'Excellent', '3 years/36,000 miles', '30 MPG Combined', 'Petrol', 'Manual', '2.0L 4-Cylinder', 181, 'Soul Red Crystal', 12, TRUE),
(8, 'Mustang Convertible', 'Ford', 2024, 38995.00, 'Open-top American muscle.', 'Very Good', '3 years/36,000 miles', '22 MPG Combined', 'Petrol', 'Manual', '5.0L V8', 480, 'Race Red', 15, FALSE),
(8, 'Camaro Convertible', 'Chevrolet', 2024, 39995.00, 'Performance convertible with style.', 'Very Good', '3 years/36,000 miles', '20 MPG Combined', 'Petrol', 'Manual', '3.6L V6', 335, 'Summit White', 10, FALSE),
(8, '911 Cabriolet', 'Porsche', 2024, 129995.00, 'Ultimate convertible sports car.', 'Excellent', '4 years/50,000 miles', '21 MPG Combined', 'Petrol', 'Manual', '3.0L Twin-Turbo', 379, 'Guards Red', 4, FALSE),
(8, 'BMW M4 Convertible', 'BMW', 2024, 92995.00, 'High-performance luxury convertible.', 'Excellent', '4 years/50,000 miles', '20 MPG Combined', 'Petrol', 'Automatic', '3.0L Twin-Turbo', 503, 'Portimao Blue', 3, FALSE),

-- Luxury
(9, 'S-Class S580', 'Mercedes-Benz', 2024, 118995.00, 'The pinnacle of luxury sedans.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '4.0L V8 Hybrid', 496, 'Obsidian Black', 5, TRUE),
(9, '7 Series 760i', 'BMW', 2024, 114995.00, 'Ultimate luxury sedan with cutting-edge tech.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '4.4L V8', 536, 'Mineral White', 4, FALSE),
(9, 'A8 L', 'Audi', 2024, 92995.00, 'Luxury flagship with Quattro AWD.', 'Excellent', '4 years/50,000 miles', '22 MPG Combined', 'Petrol', 'Automatic', '3.0L Turbo V6', 335, 'Mythos Black', 6, FALSE),
(9, 'LS 500', 'Lexus', 2024, 80995.00, 'Japanese luxury with legendary reliability.', 'Excellent', '4 years/50,000 miles', '25 MPG Combined', 'Petrol', 'Automatic', '3.5L V6 Hybrid', 354, 'Nebula Gray', 8, FALSE),
(9, 'Panamera GTS', 'Porsche', 2024, 119995.00, 'Sports car performance with luxury sedan comfort.', 'Excellent', '4 years/50,000 miles', '20 MPG Combined', 'Petrol', 'Automatic', '4.0L V8', 473, 'Carrara White', 3, FALSE),
(9, 'Ghost', 'Rolls-Royce', 2024, 349995.00, 'Ultra-luxury with unparalleled comfort.', 'Excellent', '4 years/unlimited miles', '16 MPG Combined', 'Petrol', 'Automatic', '6.75L V12', 563, 'Black Badge', 1, FALSE),

-- Hybrid
(10, 'Prius Prime', 'Toyota', 2024, 32995.00, 'Efficient plug-in hybrid with excellent range.', 'Excellent', '5 years/60,000 miles', '127 MPGe Combined', 'Plug-in Hybrid', 'CVT', '1.8L Hybrid', 121, 'Supersonic Red', 30, TRUE),
(10, 'Rav4 Hybrid', 'Toyota', 2024, 36995.00, 'Popular hybrid SUV with proven reliability.', 'Excellent', '5 years/60,000 miles', '40 MPG Combined', 'Hybrid', 'CVT', '2.5L Hybrid', 219, 'Ice Cap White', 25, FALSE),
(10, 'Highlander Hybrid', 'Toyota', 2024, 42995.00, 'Three-row hybrid SUV for the whole family.', 'Excellent', '5 years/60,000 miles', '36 MPG Combined', 'Hybrid', 'CVT', '2.5L Hybrid', 243, 'Moon Dust', 15, FALSE),
(10, 'CR-V Hybrid', 'Honda', 2024, 36995.00, 'Efficient hybrid SUV with Honda reliability.', 'Excellent', '5 years/60,000 miles', '38 MPG Combined', 'Hybrid', 'Automatic', '2.0L Hybrid', 204, 'Platinum White', 20, FALSE),
(10, 'Accord Hybrid', 'Honda', 2024, 32995.00, 'Stylish hybrid sedan with excellent economy.', 'Excellent', '5 years/60,000 miles', '44 MPG Combined', 'Hybrid', 'Automatic', '2.0L Hybrid', 204, 'Meteorite Gray', 22, FALSE),
(10, 'Ioniq Hybrid', 'Hyundai', 2024, 26995.00, 'Most efficient hybrid in its class.', 'Very Good', '5 years/60,000 miles', '59 MPG Combined', 'Hybrid', 'CVT', '1.6L Hybrid', 139, 'Symphony Silver', 35, FALSE);

-- Create indexes for performance
CREATE INDEX idx_cars_type ON cars(type_id);
CREATE INDEX idx_cars_featured ON cars(featured);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_cart_user ON cart(user_id);