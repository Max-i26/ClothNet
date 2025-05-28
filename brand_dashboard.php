<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Brand') {
    header("Location: login.php");
    exit();
}

$brand_id = $_SESSION['user_id'];

// Total Revenue
$revenue_result = mysqli_query($conn, "
    SELECT SUM(o.total_price) AS revenue 
    FROM Orders o 
    JOIN Order_Items oi ON o.order_id = oi.order_id
    JOIN Products p ON oi.product_id = p.product_id
    WHERE p.brand_id = $brand_id
");
$revenue = mysqli_fetch_assoc($revenue_result)['revenue'] ?? 0;

// Best Selling Products
$bestsellers = mysqli_query($conn, "
    SELECT p.name, SUM(oi.quantity) as total_sold 
    FROM Order_Items oi
    JOIN Products p ON oi.product_id = p.product_id
    WHERE p.brand_id = $brand_id
    GROUP BY p.name
    ORDER BY total_sold DESC
    LIMIT 5
");

// Monthly Orders Chart
$monthly_result = mysqli_query($conn, "
    SELECT DATE_FORMAT(o.order_date, '%b') AS month, SUM(o.total_price) AS total
    FROM Orders o
    JOIN Order_Items oi ON o.order_id = oi.order_id
    JOIN Products p ON oi.product_id = p.product_id
    WHERE p.brand_id = $brand_id
    GROUP BY MONTH(o.order_date)
    ORDER BY MONTH(o.order_date)
");
$monthly_data = [];
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_data[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Brand Analytics Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            font-family: 'Segoe UI', sans-serif;
        }
        h2 {
            font-size: 26px;
            color: #1f2937;
            font-weight: 700;
        }
        .card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }
        .card h4 {
            margin-bottom: 10px;
            font-size: 18px;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h2>📈 Brand Analytics Dashboard</h2>

    <div class="card">
        <h4>Total Revenue</h4>
        <p><strong>Rs. <?php echo number_format($revenue, 2); ?></strong></p>
    </div>

    <div class="card">
        <h4>📦 Best Selling Products</h4>
        <ul>
            <?php while ($row = mysqli_fetch_assoc($bestsellers)): ?>
                <li><?php echo htmlspecialchars($row['name']); ?> - <?php echo $row['total_sold']; ?> sold</li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="card">
        <h4>📊 Monthly Revenue</h4>
        <canvas id="monthlyChart"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
            datasets: [{
                label: 'Monthly Revenue (Rs.)',
                data: <?php echo json_encode(array_map('intval', array_column($monthly_data, 'total'))); ?>,
                backgroundColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
