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
    <h2 class="page-title">🧾 My Orders </h2>

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

<style>body {
  margin: 0;
  padding: 0;
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  overflow-x: hidden;
}

.container {
  width: 95%;
  max-width: 900px;
  padding: 30px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
}

/* Page title centered */
.page-title {
  font-size: 26px;
  font-weight: bold;
  text-align: center;
  margin-bottom: 24px;
  color: #ffffff;
  position: relative;
}

.page-title::after {
  content: ' ✨🛒🌙';
  animation: sparkle 1.8s infinite alternate;
  font-size: 20px;
  margin-left: 6px;
}

@keyframes sparkle {
  0% { opacity: 0.4; transform: translateY(0); }
  50% { opacity: 1; transform: translateY(-2px); }
  100% { opacity: 0.4; transform: translateY(0); }
}

/* Table */
.styled-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
  background-color: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.3);
}

.styled-table thead {
  background-color: rgba(255, 255, 255, 0.1);
  color: #fcd34d;
  text-align: left;
}

.styled-table th, .styled-table td {
  padding: 14px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.styled-table tr:hover {
  background-color: rgba(255, 255, 255, 0.07);
}

/* Status badges */
.status-pending {
  color: #facc15;
  background-color: rgba(250, 204, 21, 0.15);
  padding: 6px 14px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
}



.status-arrived {
  color: #34d399;
  background-color: rgba(52, 211, 153, 0.15);
  padding: 6px 14px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
}

/* Responsive */
@media (max-width: 768px) {
  .container {
    padding: 20px;
  }

  .styled-table th,
  .styled-table td {
    font-size: 13px;
    padding: 10px;
  }

  .page-title {
    font-size: 20px;
  }
}

/* Action Button - Mark as Arrived */
.btn {
  font-size: 14px;
  padding: 8px 18px;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  backdrop-filter: blur(8px);
  border: 1px solid rgba(16, 185, 129, 0.3);
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
  transition: all 0.3s ease;
}

.btn:hover {
  background: rgba(16, 185, 129, 0.3);
  color: #10b981;
  transform: scale(1.05);
  box-shadow: 0 0 18px rgba(16, 185, 129, 0.5);
}

@keyframes animeGlow {
  0%, 100% {
    box-shadow: 0 0 10px rgba(34, 211, 238, 0.3);
  }
  50% {
    box-shadow: 0 0 18px rgba(34, 211, 238, 0.6);
  }
}

.btn:hover {
  animation: animeGlow 1.4s ease-in-out infinite;
}




</style>

</body>
</html>
