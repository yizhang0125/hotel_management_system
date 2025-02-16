<?php
session_start();
include 'db_connect.php';
include 'functions.php';  // Include the shared functions

// Function to update the best deals
function updateBestDeals($pdo, $threshold) {
    $stmtUpdateBest = $pdo->prepare("UPDATE deals SET is_best = 1 WHERE deal_date = CURRENT_DATE AND discount >= ?");
    $stmtUpdateBest->execute([$threshold]);
}

// Function to fetch today's best deals
function getBestDeals($pdo) {
    $stmtBestDeals = $pdo->query("SELECT * FROM deals WHERE is_best = 1 AND deal_date = CURRENT_DATE");
    return $stmtBestDeals->fetchAll(); 
}

// Function to fetch room options
function getRooms($pdo) {
    $stmtRooms = $pdo->query("SELECT * FROM rooms LIMIT 4");
    return $stmtRooms->fetchAll();
}

// Function to fetch hotel facilities
function getFacilities($pdo) {
    $stmtFacilities = $pdo->query("SELECT * FROM facilities");
    return $stmtFacilities->fetchAll();
}

// Update best deals if necessary
$discountThreshold = 50; // Example threshold
updateBestDeals($pdo, $discountThreshold);

// Fetch data for display
$bestDeals = getBestDeals($pdo);
$rooms = getRooms($pdo);
$facilities = getFacilities($pdo);

// Update the best deals query to exclude expired deals
$current_date = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT * FROM deals 
    WHERE end_date >= ? 
    AND (start_date <= ? OR start_date IS NULL)
    ORDER BY discount DESC, is_best DESC 
    LIMIT 3
");
$stmt->execute([$current_date, $current_date]);
$bestDeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add this for debugging
echo "<!-- Debug: Number of deals found: " . count($bestDeals) . " -->";
// Also check what deals exist
foreach ($bestDeals as $deal) {
    echo "<!-- Debug: Deal ID: " . $deal['id'] . 
         ", Start: " . $deal['start_date'] . 
         ", End: " . $deal['end_date'] . 
         ", Is Best: " . $deal['is_best'] . " -->";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Best Deals</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <style>
.container-fluid {
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Align content to the top */
    padding-top: 20px; /* Reduced padding for less space at the top */
}

.navbar {
    background-color: #343a40;
}

.card-container {
    flex: 1 1 auto;
    max-width: 300px;
    margin: 15px auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
}

.card img {
    height: 250px;
    object-fit: cover;
    border-radius: 15px 15px 0 0;
}

.card-body {
    padding: 20px;
    text-align: center;
}

.center-heading {
    text-align: center;
    margin: 60px 0 40px;
    position: relative;
}

.center-heading h1 {
    font-size: 2.8rem;
    font-weight: 800;
    color: #1e3c72;
    margin-bottom: 20px;
    position: relative;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.center-heading h1::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: 3px;
}

.center-heading i {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-right: 15px;
    vertical-align: middle;
}

h1, h2 {
    color: #333;
    margin-bottom: 20px;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 14px;
}

footer {
    background-color: #343a40;
    color: white;
}
.search-slider {
    text-align: left;
    padding: 15px; 
    background-color: rgb(173, 216, 230);  /* Light Blue */
    border: 1px solid #ccc;
    border-radius: 25px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    max-width: 620px;
    margin: 0 auto;  
    display: flex;
    flex-direction: column;
    color: #555;  /* Soft gray for better contrast */
    margin-top: -68px; 
}

.slider-input-container {
    display: flex;
    flex-wrap: nowrap;
    justify-content: space-between;
    width: 100%;
}

.slider-input label {
    font-weight: bold;
    margin-bottom: 5px;
}

.slider-input input {
    display: none; /* Hide default input */
}

.slider-input span {
    display: inline-block;
    text-align: center;
    font-size: 1rem;
    cursor: pointer;
}

.slider-input span i {
    margin-left: 2px; /* Slight adjustment to move the icon right */
    color: #3399cc;  /* Lighter soft blue for better contrast */
    font-size: 1.5rem;  /* Adjust size for visibility */
}

/* Adjust for responsiveness */
@media (max-width: 768px) {
    .oval-box {
        flex: 1 1 100%;
    }
}

/* Layout adjustments for horizontal button */
.slider-input-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.search-button-container {
    display: flex;
    align-items: center;
}

.search-button {
    padding: 10px 20px;
    font-size: 1rem;
    background-color: #4a90e2;  /* Lighter soft blue */
    color: white;
    border: none;
    border-radius: 15px;
    cursor: pointer;
}
.input-group-addon {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    margin-left: 5px;
    cursor: pointer;
}

.input-group-addon i {
    color: #333;
    text-align: center;
}
.facility-icons div {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.facility-icons div:hover {
    transform: scale(1.07);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
}

.search-container {
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    max-width: 1200px;
    margin: -100px auto 50px;
    position: relative;
    z-index: 1000;
    border: 1px solid rgba(255,255,255,0.8);
}

.search-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    align-items: end;
}

.search-input {
    position: relative;
}

.search-input label {
    display: block;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-weight: 500;
    font-size: 0.95rem;
}

.search-input label i {
    margin-right: 8px;
    color: #3498db;
}

.date-input-wrapper {
    position: relative;
}

.input-field {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 1px solid #e1e8ed;
    border-radius: 10px;
    background: #fff;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    color: #2c3e50;
}

.input-field:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
    outline: none;
}

.calendar-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #3498db;
    cursor: pointer;
}

.guest-selector {
    position: relative;
}

.guest-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.8rem 1rem;
    background: #fff;
    border: 1px solid #e1e8ed;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.guest-display:hover {
    border-color: #3498db;
}

.guest-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.control-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #f8f9fa;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #2c3e50;
}

.control-btn:hover {
    background: #e9ecef;
}

.control-btn.minus {
    background: #fff0f0;
    color: #e74c3c;
}

.control-btn.plus {
    background: #f0fff0;
    color: #2ecc71;
}

.search-btn {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    width: 100%;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52,152,219,0.3);
}

.search-btn i {
    margin-right: 8px;
}

/* Custom datepicker styling */
.ui-datepicker {
    background: #fff;
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    padding: 15px;
    width: 300px;
}

.ui-datepicker-header {
    background: none;
    border: none;
    padding: 10px;
}

.ui-datepicker-title {
    color: #2c3e50;
    font-weight: 600;
}

.ui-datepicker-prev, .ui-datepicker-next {
    background: #f8f9fa !important;
    border-radius: 50% !important;
    cursor: pointer;
}

.ui-datepicker-calendar th {
    color: #3498db;
    font-weight: 500;
}

.ui-datepicker-calendar td {
    padding: 5px;
}

.ui-state-default {
    background: none !important;
    border: none !important;
    text-align: center;
    color: #2c3e50 !important;
    border-radius: 5px;
}

.ui-state-highlight {
    background: #f1f8ff !important;
}

.ui-state-active {
    background: #3498db !important;
    color: #fff !important;
}

@media (max-width: 768px) {
    .search-container {
        margin: -50px 1rem 30px;
        padding: 1.5rem;
    }
    
    .search-form {
        grid-template-columns: 1fr;
    }
}

body {
    background: #f8f9fa;
    padding-top: 80px;
    font-family: 'Poppins', sans-serif;
}

.navbar {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    padding: 15px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.navbar-brand {
    font-size: 1.5rem;
    font-weight: 700;
    color: white !important;
    display: flex;
    align-items: center;
    margin-left: 15px;
}

.navbar-brand i {
    font-size: 1.8rem;
    margin-right: 12px;
}

.nav-link {
    font-weight: 500;
    padding: 10px 15px !important;
    transition: all 0.3s;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
    border-radius: 5px;
}

.container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

.main-footer {
    position: relative;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #fff;
    padding: 80px 0 0;
    margin-top: 100px;
    overflow: hidden;
}

.footer-waves {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
    transform: rotate(180deg);
}

.footer-waves .wave {
    position: absolute;
    opacity: 0.4;
    width: 1600px;
    height: 50px;
    transform-origin: center bottom;
    background: url("data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMTYwMCA1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0wIDUwaDgwMFYwTDAgNTB6IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9Ii4zIi8+CiAgICA8cGF0aCBkPSJNMCA1MGg4MDBWMEwwIDUweiIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIuMyIvPgo8L3N2Zz4=") repeat-x;
}

#wave1 {
    z-index: 5;
    opacity: 0.2;
    bottom: 0;
    animation: wave 25s linear infinite;
}

#wave2 {
    z-index: 4;
    opacity: 0.15;
    bottom: 10px;
    animation: wave 20s linear infinite reverse;
}

#wave3 {
    z-index: 3;
    opacity: 0.1;
    bottom: 15px;
    animation: wave 30s linear infinite;
}

#wave4 {
    z-index: 2;
    opacity: 0.05;
    bottom: 20px;
    animation: wave 15s linear infinite reverse;
}

@keyframes wave {
    0% { transform: translateX(0); }
    50% { transform: translateX(-50%); }
    100% { transform: translateX(-100%); }
}

.footer-content {
    position: relative;
    z-index: 10;
    padding: 30px 0;
}

.footer-brand {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
}

.footer-brand i {
    margin-right: 10px;
}

.footer-desc {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 25px;
    line-height: 1.8;
}

.contact-info .contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
}

.contact-item i {
    width: 30px;
    height: 30px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 14px;
}

.contact-item p {
    margin: 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
}

.footer-navigation h4 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 25px;
    position: relative;
}

.footer-navigation h4::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -10px;
    width: 30px;
    height: 2px;
    background: #fff;
}

.footer-navigation ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-navigation ul li {
    margin-bottom: 12px;
}

.footer-navigation ul li a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s;
    font-size: 14px;
}

.footer-navigation ul li a:hover {
    color: #fff;
    padding-left: 5px;
}

.footer-connect h4 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 25px;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.social-links a {
    width: 35px;
    height: 35px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    font-size: 14px;
    transition: all 0.3s;
}

.social-links a:hover {
    background: #fff;
    color: #1e3c72;
    transform: translateY(-3px);
}

.newsletter h5 {
    font-size: 16px;
    margin-bottom: 15px;
}

.newsletter-form .input-group {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 30px;
    padding: 5px;
}

.newsletter-form input {
    background: transparent;
    border: none;
    color: #fff;
    padding: 10px 20px;
}

.newsletter-form input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.btn-subscribe {
    background: #fff;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    color: #1e3c72;
    transition: all 0.3s;
}

.btn-subscribe:hover {
    background: #e3f2fd;
    transform: rotate(30deg);
}

.footer-bottom {
    background: rgba(0, 0, 0, 0.2);
    padding: 20px 0;
    margin-top: 50px;
}

.copyright {
    font-size: 14px;
}

.footer-bottom-links {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
}

.footer-bottom-links a:hover {
    color: #fff;
}

@media (max-width: 768px) {
    .main-footer {
        padding-top: 60px;
    }

    .footer-navigation {
        margin: 40px 0;
    }

    .footer-bottom-links {
        justify-content: center;
        margin-top: 15px;
    }

    .copyright {
        text-align: center;
    }
}

.card-deck {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
}

.card-container {
    background: white;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    margin: 15px;
}

.card-container:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(30,60,114,0.15);
}

.card-img-top {
    height: 300px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.card-container:hover .card-img-top {
    transform: scale(1.05);
}

.discount-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 12px 25px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 1.2rem;
    box-shadow: 0 5px 20px rgba(30,60,114,0.2);
    z-index: 2;
}

.card-body {
    padding: 30px;
    text-align: center;
    background: white;
}

.card-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e3c72;
    margin-bottom: 15px;
    line-height: 1.3;
}

.card-text {
    color: #64748b;
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 25px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.btn-info {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border: none;
    padding: 12px 30px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    color: white;
}

.btn-info:hover {
    background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(30,60,114,0.2);
    color: white;
}

@media (max-width: 992px) {
    .center-heading h1 {
        font-size: 2.3rem;
    }
    
    .card-title {
        font-size: 1.4rem;
    }
}

@media (max-width: 768px) {
    .center-heading h1 {
        font-size: 2rem;
    }
    
    .card-img-top {
        height: 250px;
    }
    
    .card-body {
        padding: 20px;
    }
}

.center-heading {
    text-align: center;
    margin: 60px 0 40px;
    position: relative;
}

.center-heading h2 {
    font-size: 2.8rem;
    font-weight: 800;
    color: #1e3c72;
    margin-bottom: 20px;
    position: relative;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.center-heading h2::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: 3px;
}

.center-heading i {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-right: 15px;
    vertical-align: middle;
}

@media (max-width: 992px) {
    .center-heading h2 {
        font-size: 2.3rem;
    }
}

@media (max-width: 768px) {
    .center-heading h2 {
        font-size: 2rem;
    }
}

/* Enhanced Mobile Navbar Styles */
@media (max-width: 991px) {
    .navbar {
        padding: 10px 0;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    }

    .navbar > .container {
        padding-right: 15px;
        padding-left: 15px;
    }

    .navbar-brand {
        font-size: 1.4rem;
        margin-left: 10px;
    }

    .navbar-brand i {
        font-size: 1.6rem;
        margin-right: 10px;
    }

    .navbar-toggler {
        margin-right: 10px;
    }
}

/* Extra Small Devices */
@media (max-width: 576px) {
    .navbar-brand {
        font-size: 1.2rem;
        margin-left: 8px;
    }

    .navbar-brand i {
        font-size: 1.4rem;
        margin-right: 8px;
    }

    .navbar-toggler {
        margin-right: 8px;
        transform: scale(0.9);
    }
}

.price-info {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    margin: 10px 0;
}

.original-price {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9rem;
}

.discounted-price {
    color: #1e3c72;
    font-size: 1.2rem;
    font-weight: bold;
}

.discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #e74c3c;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 500;
    z-index: 1;
}

.card-container {
    position: relative;
    transition: transform 0.3s ease;
    margin: 15px;
    max-width: 350px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-radius: 15px;
    overflow: hidden;
}

.card-container:hover {
    transform: translateY(-5px);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}

.card-body {
    padding: 20px;
}

.card-title {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: #2c3e50;
}

.card-text {
    color: #666;
    margin-bottom: 15px;
}

.btn-info {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border: none;
    padding: 10px 20px;
    transition: transform 0.3s ease;
}

.btn-info:hover {
    transform: translateY(-2px);
}
    </style>

<script>
    // Enhanced navbar functionality
    document.addEventListener('DOMContentLoaded', function() {
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');

        // Close navbar when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInside = navbarToggler.contains(event.target) || navbarCollapse.contains(event.target);
            if (!isClickInside && navbarCollapse.classList.contains('show')) {
                $('.navbar-collapse').collapse('hide');
            }
        });

        // Close navbar when clicking a nav link
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (navbarCollapse.classList.contains('show')) {
                    $('.navbar-collapse').collapse('hide');
                }
            });
        });

        // Prevent scrolling when mobile menu is open
        navbarCollapse.addEventListener('show.bs.collapse', function () {
            document.body.style.overflow = 'hidden';
        });

        navbarCollapse.addEventListener('hide.bs.collapse', function () {
            document.body.style.overflow = '';
        });
    });
</script>
</head>
<body>
    <?php include 'user/navbar.php'; ?>
    
    <div class="container-fluid">
        <main class="d-flex flex-column justify-content-between">
            <div>
                <div class="text-center position-relative">
                    <!-- Background Image -->
                    <img src="uploads/background1.jpg" alt="Best Deals Banner" class="img-fluid" style="width: 100%; height: 750px; position: relative; z-index: -1; ">

                    <!-- Welcome Text and More Content -->
                    <div class="position-absolute text-white" 
                         style="top: 50%; left: 10%; transform: translateY(-50%); text-align: left;">
                        <!-- Main Welcome Message -->
                        <div style="font-size: 3rem; font-weight: bold;">
                            <?php if (isset($_SESSION['username'])) : ?>
                                Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!
                            <?php else : ?>
                                Welcome to our platform!
                            <?php endif; ?>
                        </div>

                        <!-- Additional Text Below Welcome -->
                        <div style="font-size: 1.5rem; margin-top: 10px;">
                            Discover the best deals and exclusive offers waiting for you today!
                        </div>

                        <!-- Buttons or Call-to-Action -->
                        <div style="margin-top: 20px;">
                            <a href="#deals" class="btn btn-primary" style="padding: 10px 20px; font-size: 1.2rem;">View Deals</a>
                            <a href="#rooms" class="btn btn-secondary" style="padding: 10px 20px; font-size: 1.2rem;">Explore Rooms</a>
                        </div>
                    </div>
                </div>

                <br>

                <div class="search-container">
                    <form action="search_results.php" method="GET" class="search-form">
                        <!-- Date Selection -->
                        <div class="search-input">
                            <label><i class="fas fa-calendar-alt"></i> Check-in Date</label>
                            <div class="date-input-wrapper">
                                <input type="text" id="checkin" name="checkin" class="input-field datepicker" required readonly>
                                <span class="calendar-icon" onclick="$('#checkin').focus()">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                        </div>
                    </div>

                    <div class="search-input">
                        <label><i class="fas fa-calendar-check"></i> Check-out Date</label>
                        <div class="date-input-wrapper">
                            <input type="text" id="checkout" name="checkout" class="input-field datepicker" required readonly>
                            <span class="calendar-icon" onclick="$('#checkout').focus()">
                                <i class="fas fa-calendar-alt"></i>
                    </span>
                </div>
            </div>

                    <!-- Guest Selection -->
                    <div class="search-input">
                        <label><i class="fas fa-user"></i> Adults</label>
                        <div class="guest-selector">
                            <input type="hidden" name="adults" id="adults-input" value="1">
                            <div class="guest-display">
                                <span id="adults-count">1 Adult</span>
                                <div class="guest-controls">
                                    <button type="button" class="control-btn minus" onclick="updateGuests('adults', -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="control-btn plus" onclick="updateGuests('adults', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                </div>
            </div>
                </div>
            </div>

                    <div class="search-input">
                        <label><i class="fas fa-child"></i> Children</label>
                        <div class="guest-selector">
                            <input type="hidden" name="children" id="children-input" value="0">
                            <div class="guest-display">
                                <span id="children-count">0 Children</span>
                                <div class="guest-controls">
                                    <button type="button" class="control-btn minus" onclick="updateGuests('children', -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="control-btn plus" onclick="updateGuests('children', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
            </div>
        </div>
            </div>
        </div>

                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                        Check Availability
                    </button>
    </form>
</div>

<?php if (!empty($bestDeals)): ?>
<div class="center-heading text-center">
    <h1><i class="fas fa-tags"></i> Today's Best Deals</h1>
</div>

<div class="card-deck justify-content-center">
    <?php foreach ($bestDeals as $deal): ?>
        <div class="card card-container">
            <div class="discount-badge">
                <?= $deal['discount'] ?>% OFF
            </div>
            <?php if (!empty($deal['image_path'])): ?>
                <img src="uploads/<?= htmlspecialchars($deal['image_path']) ?>" 
                     alt="<?= htmlspecialchars($deal['deal_name']) ?>" 
                     class="card-img-top">
            <?php else: ?>
                <img src="images/default-deal.jpg" 
                     alt="Default Image" 
                     class="card-img-top">
            <?php endif; ?>
            <div class="card-body text-center">
                <h5 class="card-title"><?= htmlspecialchars($deal['deal_name']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($deal['description']) ?></p>
                
                <!-- Add price information -->
                <div class="price-info mb-3">
                    <div class="original-price">
                        Original Price: $<?= number_format($deal['price'], 2) ?>
                    </div>
                    <div class="discounted-price">
                        Now: $<?= number_format($deal['price'] * (1 - $deal['discount']/100), 2) ?>
                    </div>
                </div>
                
                <a href="deal_details.php?id=<?= $deal['id'] ?>" class="btn btn-info">
                    <i class="fas fa-info-circle"></i> View Details
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <div class="center-heading text-center">
        <h2>No active deals at the moment</h2>
        <p class="text-muted">Check back later for exciting offers!</p>
    </div>
<?php endif; ?>

<hr>

<div class="center-heading text-center">
    <h2><i class="fas fa-bed"></i> Choose the Perfect Accommodation</h2>
</div>

<div class="card-deck justify-content-center">
    <!-- PHP Code to Fetch Rooms -->
    <?php foreach ($rooms as $room) : ?>
        <div class="card card-container">
            <img src="<?= $room['image_path'] ?>" alt="<?= $room['room_type'] ?>" class="card-img-top">
            <div class="card-body text-center">
                <h5 class="card-title"><?= $room['room_type'] ?></h5>
                <p class="card-text">Price: $<?= number_format($room['price'], 2) ?> / Night</p>
                <a href="room_details.php?id=<?= $room['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<hr>

<div class="center-heading text-center">
    <h2><i class="fas fa-concierge-bell"></i> Hotel Facilities</h2>
</div>
<div class="facility-icons text-center" style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
    <!-- Facility Box -->
    <div style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); padding: 20px; text-align: center; border-radius: 15px; width: 220px; background: linear-gradient(145deg, #f5f7fa, #e4e7eb); border: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fas fa-wifi" style="font-size: 4rem; color: #5e72e4;"></i>
        <p style="margin: 15px 0 0; font-size: 1.3rem; font-weight: bold; color: #2d3436;">Free Wi-Fi</p>
    </div>

    <!-- Parking -->
    <div style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); padding: 20px; text-align: center; border-radius: 15px; width: 220px; background: linear-gradient(145deg, #f5f7fa, #e4e7eb); border: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fas fa-car" style="font-size: 4rem; color: #f5365c;"></i>
        <p style="margin: 15px 0 0; font-size: 1.3rem; font-weight: bold; color: #2d3436;">Parking</p>
    </div>

    <!-- 24-Hour Reception -->
    <div style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); padding: 20px; text-align: center; border-radius: 15px; width: 220px; background: linear-gradient(145deg, #f5f7fa, #e4e7eb); border: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fas fa-clock" style="font-size: 4rem; color: #11cdef;"></i>
        <p style="margin: 15px 0 0; font-size: 1.3rem; font-weight: bold; color: #2d3436;">24-Hour Reception</p>
    </div>

    <!-- Laundry -->
    <div style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); padding: 20px; text-align: center; border-radius: 15px; width: 220px; background: linear-gradient(145deg, #f5f7fa, #e4e7eb); border: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fas fa-tshirt" style="font-size: 4rem; color: #2dce89;"></i>
        <p style="margin: 15px 0 0; font-size: 1.3rem; font-weight: bold; color: #2d3436;">Laundry Services</p>
    </div>

    <!-- Swimming Pools -->
    <div style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); padding: 20px; text-align: center; border-radius: 15px; width: 220px; background: linear-gradient(145deg, #f5f7fa, #e4e7eb); border: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fas fa-swimming-pool" style="font-size: 4rem; color: #5e72e4;"></i>
        <p style="margin: 15px 0 0; font-size: 1.3rem; font-weight: bold; color: #2d3436;">Swimming Pools</p>
    </div>

    <!-- Gym -->
    <div style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); padding: 20px; text-align: center; border-radius: 15px; width: 220px; background: linear-gradient(145deg, #f5f7fa, #e4e7eb); border: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fas fa-dumbbell" style="font-size: 4rem; color: #f5365c;"></i>
        <p style="margin: 15px 0 0; font-size: 1.3rem; font-weight: bold; color: #2d3436;">Gym</p>
    </div>

    <!-- Spa -->
    <div style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); padding: 20px; text-align: center; border-radius: 15px; width: 220px; background: linear-gradient(145deg, #f5f7fa, #e4e7eb); border: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fas fa-spa" style="font-size: 4rem; color: #11cdef;"></i>
        <p style="margin: 15px 0 0; font-size: 1.3rem; font-weight: bold; color: #2d3436;">Spa</p>
    </div>
</div>


<hr>

<div class="card-deck justify-content-center">
    <!-- PHP Code to Fetch Facilities -->
    <?php foreach ($facilities as $facility) : ?>
        <div class="card card-container">
            <img src="<?= $facility['image_path'] ?>" alt="<?= $facility['name'] ?>" class="card-img-top">
            <div class="card-body text-center">
                <h5 class="card-title"><?= $facility['name'] ?></h5>
                <a href="facilities_details.php?id=<?= $facility['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-info-circle"></i> More Info</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>



            <footer class="main-footer">
                <div class="footer-waves">
                    <div class="wave" id="wave1"></div>
                    <div class="wave" id="wave2"></div>
                    <div class="wave" id="wave3"></div>
                    <div class="wave" id="wave4"></div>
                </div>

                <div class="footer-content">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 footer-contact">
                                <div class="footer-brand">
                                    <i class="fas fa-hotel"></i>
                                    <span>Luxury Hotel</span>
                                </div>
                                <p class="footer-desc">
                                    Where luxury meets comfort. Experience world-class hospitality and unforgettable stays.
                                </p>
                                <div class="contact-info">
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <p>123 Luxury Avenue, Metro Manila, Philippines</p>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-phone-alt"></i>
                                        <p>+63 123 456 7890</p>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <p>info@luxuryhotel.com</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 footer-navigation">
                                <div class="row">
                                    <div class="col-6">
                                        <h4>Navigation</h4>
                                        <ul>
                                            <li><a href="index.php">Home</a></li>
                                            <li><a href="view_rooms.php">Rooms</a></li>
                                            <li><a href="#">About Us</a></li>
                                            <li><a href="#">Contact</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-6">
                                        <h4>Services</h4>
                                        <ul>
                                            <li><a href="#">Restaurant</a></li>
                                            <li><a href="#">Spa & Wellness</a></li>
                                            <li><a href="#">Gym</a></li>
                                            <li><a href="#">Events</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-12 footer-connect">
                                <h4>Stay Connected</h4>
                                <p>Follow us on social media for updates and exclusive offers</p>
                                <div class="social-links">
                                    <a href="#" class="facebook"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="instagram"><i class="fab fa-instagram"></i></a>
                                    <a href="#" class="twitter"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="linkedin"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                                <div class="newsletter">
                                    <h5>Newsletter</h5>
                                    <form class="newsletter-form">
                                        <div class="input-group">
                                            <input type="email" class="form-control" placeholder="Enter your email">
                                            <button type="submit" class="btn-subscribe">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-bottom">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="copyright">
                                    © <?= date('Y') ?> <strong>Luxury Hotel</strong>. All rights reserved.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="footer-bottom-links">
                                    <a href="#">Privacy Policy</a>
                                    <a href="#">Terms & Conditions</a>
                                    <a href="#">Sitemap</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </footer>
        </div>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function () {
    // Increment and Decrement Buttons
    $('.increment').click(function () {
        let target = $(this).data('target');
        let display = $('#' + target.replace('-hidden', '-display'));
        let value = parseInt(display.text()) + 1;
        display.text(value);
        $('#' + target).val(value); // Update hidden input
    });

    $('.decrement').click(function () {
        let target = $(this).data('target');
        let display = $('#' + target.replace('-hidden', '-display'));
        let value = parseInt(display.text());
        if (value > 0) {
            value -= 1;
            display.text(value);
            $('#' + target).val(value); // Update hidden input
        }
    });

    // Datepicker Initialization
    $(".datepicker").datepicker({ dateFormat: "yy-mm-dd" });
    $('#checkin-icon').click(function () { $('#checkin').focus(); });
    $('#checkout-icon').click(function () { $('#checkout').focus(); });
});

function updateGuests(type, change) {
    const input = document.getElementById(`${type}-input`);
    const display = document.getElementById(`${type}-count`);
    let value = parseInt(input.value);
    const maxValues = {
        'adults': 4,  // Maximum adults from your database
        'children': 3 // Maximum children from your database
    };
    const minValues = {
        'adults': 1,
        'children': 0
    };
    
    value = Math.min(Math.max(value + change, minValues[type]), maxValues[type]);
    input.value = value;
    
    const label = type === 'adults' ? 
        (value === 1 ? 'Adult' : 'Adults') : 
        (value === 1 ? 'Child' : 'Children');
    display.textContent = `${value} ${label}`;
}

$(document).ready(function() {
    // Enhanced datepicker initialization
    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        maxDate: '+1Y',
        changeMonth: true,
        changeYear: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        numberOfMonths: window.innerWidth > 768 ? 1 : 1,
        beforeShow: function(input, inst) {
            inst.dpDiv.addClass('custom-datepicker');
        }
    });

    // Prevent keyboard input on date fields
    $(".datepicker").on('keydown', function(e) {
        e.preventDefault();
    });
});
</script>
</body>
</html>
