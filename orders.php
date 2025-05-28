<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Shop Owner') {
    header('Location: login.php');
    exit();
}

$shop_owner_id = $_SESSION['user_id'];

// Handle arrival update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arrived_order_id'])) {
    $order_id = intval($_POST['arrived_order_id']);

    // Update status in Orders table
    $update1 = "UPDATE Orders SET status = 'Arrived' WHERE order_id = $order_id AND shop_owner_id = $shop_owner_id";
    mysqli_query($conn, $update1);

    // Update status in Product_Orders table
    $update2 = "UPDATE Product_Orders SET status = 'Arrived' WHERE order_id = $order_id";
    mysqli_query($conn, $update2);

    header("Location: orders.php?arrived=1");
    exit();
}

// Fetch orders
$sql = "
    SELECT 
        o.order_id,
        o.order_date,
        o.total_price,
        o.status,
        oi.quantity,
        p.name AS product_name,
        u.name AS brand_name
    FROM Orders o
    JOIN Order_Items oi ON o.order_id = oi.order_id
    JOIN Products p ON oi.product_id = p.product_id
    JOIN Users u ON p.brand_id = u.user_id
    WHERE o.shop_owner_id = $shop_owner_id
    ORDER BY o.order_date DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h2 class="page-title">🧾 My Orders (Shop Owner View)</h2>

    <?php if (isset($_GET['arrived'])): ?>
        <p style="color: green; font-weight: bold;">Order marked as arrived ✅</p>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Brand</th>
                    <th>Qty</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>Rs. <?php echo number_format($row['total_price'], 2); ?></td>
                    <td>
                        <?php 
                        $status = strtolower(trim($row['status']));
                        $display_status = !empty($status) ? ucfirst($status) : 'Arrived'; 
                        $class_status = !empty($status) ? "status-$status" : "status-pending";
                        ?>
                        <span class="<?php echo $class_status; ?>">
                        <?php echo $display_status; ?>
                        </span>

                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                    <td>
                        <?php if (in_array($row['status'], ['Pending', 'Shipped'])): ?>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="arrived_order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-success">Mark as Arrived</button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No orders placed yet, start shopping 🛒</p>
    <?php endif; ?>
</div>

<style>
/* Main container */
body {
    margin: 0;
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
  color: #1e293b;
  min-height: 100vh;
  padding-top: 120px;
  box-sizing: border-box;
  animation: bgMove 15s ease infinite;
    
}
.container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    font-family: 'Segoe UI', sans-serif;
}

/* Page title */
.page-title {
    font-size: 26px;
    margin-bottom: 30px;
    color: #1f2937;
    font-weight: 700;
}

/* Styled table */
.styled-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 15px;
    border-radius: 10px;
    overflow: hidden;
    background-color: #f9fafb;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.styled-table thead {
    background-color: #f1f5f9;
    text-align: left;
    color: #374151;
}

.styled-table th, 
.styled-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #e5e7eb;
}

.styled-table tr:hover {
    background-color: #f3f4f6;
}

/* Status badge styles */
.status-pending {
    color: #d97706;
    background-color: #fef3c7;
    padding: 5px 12px;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 13px;
}

.status-shipped {
    color: #2563eb;
    background-color: #dbeafe;
    padding: 5px 12px;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 13px;
}

.status-arrived {
    color: #059669;
    background-color: #d1fae5;
    padding: 5px 12px;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 13px;
}


/* Action Button */
.btn {
    font-size: 14px;
    padding: 8px 16px;
    border-radius: 30px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease-in-out;
    background-color: #10b981;
    color: white;
}

.btn:hover {
    background-color: #059669;
    transform: scale(1.03);
}

/* Message after marking order arrived */
.container p {
    padding: 12px 16px;
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #10b981;
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 20px;
}

</style>

</body>
</html>
