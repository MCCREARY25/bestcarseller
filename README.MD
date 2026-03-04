### BestCarSeller - Premium Car Marketplace


![alt text](image.png)


A full-stack web application for buying and selling cars, built with PHP, MySQL, and vanilla JavaScript. It features a modern, responsive UI and a complete admin dashboard for managing orders and users.

### BestCarSeller Banner


### Features

# User Features
Authentication: Secure Sign Up & Login system with password hashing.
Dynamic Car Catalog: Browse cars by 10 different categories (Sedan, SUV, Sports, Electric, etc.).
Advanced Filtering: Sidebar navigation to filter cars by type.
Detailed Listings: View specs (Year, Transmission, Fuel Type), Durability, and Warranty info.
Multi-Currency Support: Prices available in USD, EUR, GBP, JPY, and even Bitcoin.
Shopping Cart: Add items to cart and proceed to checkout.
Payment Gateway: Supports Bank Transfer, Mobile Money, Credit Card, and Bitcoin payments.
Order Tracking: Users can track their order status (Pending, Approved, Completed).

# Admin Features
Admin Dashboard: Overview of total users, orders, revenue, and stock.
Order Management: Approve or Reject pending customer orders.
Stock Management: Cars are automatically deducted from stock upon order approval.
Tech Stack
Frontend: HTML5, CSS3 (Custom CSS Variables), JavaScript (ES6+)
Backend: PHP 8+ (Vanilla)
Database: MySQL
Server: Apache (XAMPP/WAMP/LAMP)
Icons: SVG Icons

## Installation Guide

# Prerequisites
Install XAMPP (or any local server stack).
A web browser (Chrome, Firefox, etc.).

# Setup Steps
Clone or Download the RepositoryPlace the project folder inside your htdocs directory.
C:\xampp\htdocs\bestcarseller

Start XAMPPOpen the XAMPP Control Panel and start Apache and MySQL.

# 
Create the Database
Open your browser and go to http://localhost/phpmyadmin.
Create a new database named bestcarseller.
Select the database and go to the Import tab.
Import the setup.sql file provided in the project root.
Configure Database Connection
Open config/database.php.
Ensure the credentials match your local environment (default XAMPP settings shown below):
private $host = 'localhost';private $db_name = 'bestcarseller';private $username = 'root';private $password = '';

## Run the Application
Open your browser and navigate to: http://localhost/bestcarseller/


### Usage

# User Account
Click Sign In in the header.
Click Sign Up to create a new account.
Login to browse cars, add to cart, and place orders.

# Admin Account
Use the pre-configured admin account to access the dashboard:

Username: carseller_admin
Password: Carseller123
After logging in, you will be redirected to the Admin Dashboard where you can manage orders.

### Folder Structure
bestcarseller/
├── api/ # Backend API endpoints
│ ├── auth.php # Login/Signup logic
│ ├── cars.php # Car data retrieval
│ ├── cart.php # Cart & Order logic
│ └── admin.php # Admin logic
├── assets/
│ ├── css/
│ │ └── style.css # Main stylesheet
│ └── js/
│ └── main.js # Main frontend logic
├── config/
│ └── database.php # Database connection
├── uploads/ # Car images (if local)
├── index.php # Homepage
├── login.php # Login page
├── signup.php # Registration page
├── dashboard.php # User dashboard
├── cart.php # Shopping cart
├── payment.php # Payment processing
├── admin.php # Admin panel
└── setup.sql # Database schema & seed data

text


## License
This project is open-source and available under the MIT License.
