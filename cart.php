<?php
session_start();
include 'includes/db.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ADD TO CART - from POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Check if product is already in user's cart
    $check_sql = "SELECT * FROM Cart WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // Update quantity
        $update_sql = "UPDATE Cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
        mysqli_query($conn, $update_sql);
    } else {
        // Insert new item
        $insert_sql = "INSERT INTO Cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
        mysqli_query($conn, $insert_sql);
    }

    header("Location: cart.php");
    exit();
}

// REMOVE ITEM
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $delete_sql = "DELETE FROM Cart WHERE user_id = $user_id AND product_id = $remove_id";
    mysqli_query($conn, $delete_sql);
}

// FETCH ITEMS
$cart_items_sql = "SELECT p.*, c.quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = $user_id ";
$cart_result = mysqli_query($conn, $cart_items_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="css/dashboard.css">
    
</head>
<style>
/* Page Title */
.page-title {
    font-size: 30px;
    margin: 30px 0 20px;
    text-align: center;
    font-weight: 600;
    color:rgb(8, 40, 71);
}

/* Main Container */
.container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 30px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
}

/* Cart Table */
.cart-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    overflow: hidden;
    border-radius: 10px;
}

.cart-table th {
    background:linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    padding: 16px;
    font-weight: 600;
    color:rgb(194, 195, 196);
    font-size: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cart-table td {
    padding: 14px;
    border-bottom: 1px solid #e0e0e0;
    color: #555;
    font-size: 15px;
    text-align: center;
    background-color:solidrgba(36, 22, 78, 0.16);
}

.cart-table tr:last-child td {
    border-bottom: none;
}

/* Buttons */
.btn, .btn-danger {
    padding: 10px 18px;
    font-size: 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s ease, transform 0.2s ease;
    font-weight: 500;
}

.btn {
    background-color: #3498db;
    color: #fff;
}

.btn:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
}

.btn-danger {
    background-color: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background-color: #c0392b;
    transform: scale(1.03);
}

/* Cart Total */
.cart-total {
    text-align: right;
    margin-top: 25px;
    font-size: 20px;
    color: #2d3436;
    font-weight: 600;
}

/* Empty Cart Message */
p {
    font-size: 17px;
    text-align: center;
    margin: 30px 0;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .cart-table th, .cart-table td {
        padding: 10px;
        font-size: 14px;
    }

    .cart-total {
        font-size: 18px;
        text-align: center;
    }

    .btn, .btn-danger {
        width: 100%;
        margin-top: 10px;
        text-align: center;
    }

    .container {
        padding: 20px 15px;
    }
}
</style>

<body>
<?php 
include 'includes/header.php'; 
?>
<div class="container">
    <h2 class="page-title"> SHOPPING CART</h2>

    <?php if (mysqli_num_rows($cart_result) === 0): ?>
        <p>Your cart is empty 💔</p>
        <a href="products.php" class="btn">Continue Shopping</a>
    <?php else:
            $total = 0;
if (mysqli_num_rows($cart_result) === 0) {
    echo "<p>Your cart is empty 💔</p><a href='products.php' class='btn'>Continue Shopping</a>";
} else {
    echo "<table class='cart-table'>
            <thead>
                <tr>
                    <th>Product</th><th>Qty</th><th>Price (Rs.)</th><th>Subtotal</th><th>Action</th>
                </tr>
            </thead><tbody>";
    while ($product = mysqli_fetch_assoc($cart_result)) {
        $subtotal = $product['price'] * $product['quantity'];
        $total += $subtotal;

        echo "<tr>
                <td>" . htmlspecialchars($product['name']) . "</td>
                <td>" . $product['quantity'] . "</td>
                <td>" . number_format($product['price'], 2) . "</td>
                <td>" . number_format($subtotal, 2) . "</td>
                <td><a href='cart.php?remove={$product['product_id']}' class='btn-danger'>Remove</a></td>
              </tr>";
    }
    echo "</tbody></table>
          <div class='cart-total'>
              <h3>Total: Rs. " . number_format($total, 2) . "</h3>
              <a href='checkout.php' class='btn'>Proceed to Checkout</a>
          </div>";
}
?>
    <?php endif; ?>
</div>
</body>
</html>
