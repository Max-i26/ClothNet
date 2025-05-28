<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];
$role = $user['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $role ?> Dashboard - ClothNet</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
/* Reset and Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background: radial-gradient(circle at top left, #0f172a, #1e293b);
    color: #f8fafc;
    line-height: 1.6;
}

a {
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease-in-out;
}

a:hover {
    color: #38bdf8;
}

/* Header */
header {
    background: #0f172a;
    padding: 20px 0;
    box-shadow: 0 2px 10px rgba(0, 229, 255, 0.08);
}

header div {
    max-width: 1100px;
    margin: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #f8fafc;
}

header h1 {
    font-size: 24px;
    color: #22d3ee;
    font-weight: 700;
}

header nav a {
    margin-right: 25px;
    color: #94a3b8;
    font-weight: 500;
    text-decoration: none;
}

header nav a:hover {
    color: #38bdf8;
}

/* Main Container */
main {
    max-width: 1100px;
    margin: 80px auto;
    padding: 40px;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    box-shadow: 0 0 25px rgba(0, 229, 255, 0.12);
    animation: fadeIn 1s ease-out;
}
main h2 {
    margin-bottom: 20px;
    color:rgb(0, 166, 255);
}   

h2 {
    font-size: 32px;
    margin-bottom: 20px;
    color:rgb(34, 211, 238);
    font-weight: 600;
}

p {
    color: #cbd5e1;
    font-size: 16px;
}

/* Dashboard Cards */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.dashboard-card {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(2, 132, 199, 0.3));
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.dashboard-card:hover {
    transform: scale(1.03);
    box-shadow: 0 12px 32px rgba(34, 211, 238, 0.2);
}

.dashboard-card h3 {
    font-size: 22px;
    margin-bottom: 12px;
    color: #38bdf8;
}

.dashboard-card p {
    font-size: 15px;
    color: #94a3b8;
}

/* Reviews Styling */
.review-box {
    background: linear-gradient(135deg, rgba(30,41,59,0.6), rgba(2,132,199,0.2));
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.review-box h3 {
    margin: 0;
    color: #38bdf8;
}

.review-box .meta {
    font-size: 13px;
    color: #94a3b8;
    margin-bottom: 10px;
}

.review-box .stars {
    color: #facc15;
    font-size: 16px;
    margin-bottom: 8px;
}

.review-box p {
    font-size: 15px;
    color: #cbd5e1;
}

/* Footer */
footer {
    text-align: center;
    color: #64748b;
    font-size: 13px;
    margin: 40px 0 20px;
}

/* Fade In */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <h2>🚀 Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
        <p>You are logged in as <strong><?= htmlspecialchars($role) ?></strong>. Here’s what you can do:</p>

        <div class="dashboard-grid">

            <?php if ($role === 'Brand'): ?>
                <div class="dashboard-card">
                    <h3><a href="add_product.php"> Manage Products</a></h3>
                    <p>Add products to your store. Upload high-quality images and track inventory.</p>
                </div>
                <div class="dashboard-card">
                    <h3> <a href="product_orders.php"> View Orders</a></h3>
                    <p>Check incoming orders from shop owners, generate invoices, and mark deliveries.</p>
                </div>
                <div class="dashboard-card">
                    <h3> <a href="brand_dashboard.php"> Brand Analytics</a></h3>
                    <p>Track your best selling items, view reviews, and manage your growth metrics.</p>
                </div>
                <div class="dashboard-card">
                    <h3><a href="chat.php"> Chat with Brands</a></h3>
                    <p>Get in touch with shop owners, ask for details, or negotiate deals with real-time messaging.</p>
                </div>
            <?php elseif ($role === 'Shop Owner'): ?>
                <div class="dashboard-card">
                    <h3><a href="products.php"> Browse Products</a></h3>
                    <p>Explore trending fashion items from startup brands and place bulk orders easily.</p>
                </div>
                <div class="dashboard-card">
                    <h3><a href="orders.php"> My Orders</a></h3>
                    <p>Track your order history, estimated delivery, and download invoices.</p>
                </div>
                <div class="dashboard-card">
                    <h3><a href="chat.php"> Chat with Brands</a></h3>
                    <p>Get in touch with brands, ask for details, or negotiate deals with real-time messaging.</p>
                </div>
                <div class="dashboard-card">
                    <h3><a href="favourites.php"> Your Favourites</a></h3>
                    <p>View your favourite products and shop more.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        &copy; <?= date('Y') ?> ClothNet | Powered by <span style="color:#22d3ee;">Maxi</span> ⚡
    </footer>
</body>
</html>
