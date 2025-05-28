<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Shop Owner') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_sql = "SELECT c.*, p.name, p.price, p.brand_id 
             FROM Cart c 
             JOIN Products p ON c.product_id = p.product_id 
             WHERE c.user_id = $user_id";
$cart_result = mysqli_query($conn, $cart_sql);

$cart_items = [];
$total = 0;
while ($row = mysqli_fetch_assoc($cart_result)) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}

// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cart_items)) {
    $insert_order_sql = "INSERT INTO Orders (shop_owner_id, total_price, status) VALUES ($user_id, $total, 'Pending')";
    mysqli_query($conn, $insert_order_sql);
    $order_id = mysqli_insert_id($conn);

    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $brand_id = $item['brand_id'];

        // Insert into Order_Items
        $insert_item_sql = "INSERT INTO Order_Items (order_id, product_id, quantity, price_per_item) 
                            VALUES ($order_id, $product_id, $quantity, $price)";
        mysqli_query($conn, $insert_item_sql);

        // Insert into Product_Orders
        $po_sql = "INSERT INTO Product_Orders (order_id, product_id, shop_owner_id, brand_id, quantity, status)
                   VALUES ($order_id, $product_id, $user_id, $brand_id, $quantity, 'Pending')";
        mysqli_query($conn, $po_sql);

        // Create notification for brand
        $message = "🛍️ New order from Shop Owner (ID: $user_id) for your product (ID: $product_id)";
        $notification_sql = "INSERT INTO Notifications (user_id, message) VALUES ($brand_id, '$message')";
        mysqli_query($conn, $notification_sql);
    }

    // Clear the cart
    mysqli_query($conn, "DELETE FROM Cart WHERE user_id = $user_id");

    header("Location: orders.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #fdfcfb, #e2d1c3);
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    .container {
        width: 95%;
        max-width: 850px;
        background: #ffffff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease-in-out;
    }

    .page-title {
        text-align: center;
        font-size: 30px;
        font-weight: 600;
        color: #222;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid #eee;
        font-size: 15px;
    }

    th {
        background-color: #6c63ff;
        color: #fff;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    td {
        color: #333;
    }

    .total {
        text-align: right;
        font-size: 20px;
        font-weight: 600;
        margin-top: 20px;
        color: #2c2c2c;
    }

    .btn {
        display: inline-block;
        background: linear-gradient(135deg, #6c63ff, #4834d4);
        color: white;
        padding: 14px 28px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        margin-top: 30px;
        margin-left: auto;
        box-shadow: 0 8px 20px rgba(108, 99, 255, 0.3);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(108, 99, 255, 0.45);
        background: linear-gradient(135deg, #574b90, #6c5ce7);
    }

    a {
        color: #6c63ff;
        font-weight: 600;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h2 class="page-title">🧾 Confirm Your Order</h2>

    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
    <?php else: ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price (Rs.)</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total">Total: Rs. <?php echo number_format($total, 2); ?></div>
            <button type="submit" class="btn">Place Order</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
