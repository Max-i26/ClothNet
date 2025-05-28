<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Brand') {
    header('Location: login.php');
    exit();
}

$brand_id = $_SESSION['user_id'];

$sql = "
SELECT 
    po.order_id,
    o.order_date,
    o.total_price,
    po.status,
    po.estimated_delivery,
    p.name AS product_name,
    p.image_url,
    po.quantity,
    u.name AS buyer_name
FROM Product_Orders po
JOIN Orders o ON po.order_id = o.order_id
JOIN Products p ON po.product_id = p.product_id
JOIN Users u ON po.shop_owner_id = u.user_id
WHERE p.brand_id = ?
ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $brand_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Orders</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        }

        window.onload = function () {
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        };
    </script>
    <style>
        /* Dark mode */
        .dark-mode {
            background-color: #1e1e2f !important;
            color: #f1f5f9 !important;
            transition: background-color 0.3s, color 0.3s;
        }
        .dark-mode .container {
            background-color: #2a2a3d !important;
            box-shadow: none !important;
        }
        .dark-mode .styled-table {
            background-color: #35364a !important;
            color: #f8fafc !important;
        }
        .dark-mode .search-input {
            background-color: #44445c !important;
            color: #f8fafc !important;
            border: 1px solid #64748b !important;
        }
        .dark-mode .progress-bar {
            background: #475569 !important;
        }
        .dark-mode .progress-fill {
            background-color: #22d3ee !important;
        }
        .dark-mode .feedback-btn {
            background-color: #2563eb !important;
            color: white !important;
        }
        .dark-mode .invoice-icon {
            color: #60a5fa !important;
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: 80px auto 40px;
            background: #fff;
            padding: 24px 32px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
            transition: background-color 0.3s ease;
        }

        /* Page Title */
        .page-title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 24px;
            color: #1f2937;
        }

        /* Logout link */
        .logout-link {
            margin-bottom: 20px;
            text-align: right;
        }
        .logout-link a {
            color: #ef4444;
            font-weight: 600;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.3s ease;
        }
        .logout-link a:hover {
            color: #b91c1c;
            text-decoration: underline;
        }

        /* Dark Mode Button */
        button {
            background-color: #3b82f6;
            border: none;
            padding: 10px 18px;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }
        button:hover {
            background-color: #2563eb;
        }

        /* Search input */
        .search-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-size: 16px;
            margin-bottom: 24px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 8px rgba(59,130,246,0.5);
        }

        /* Table */
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 15px;
            color: #334155;
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .styled-table thead tr {
            background-color: #2563eb;
            color: #ffffff;
            text-align: left;
            font-weight: 700;
            font-size: 16px;
        }
        .styled-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.3s ease;
        }
        .styled-table tbody tr:hover {
            background-color: #f1f5f9;
        }
        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #2563eb;
        }
        .styled-table td, .styled-table th {
            padding: 14px 18px;
            vertical-align: middle;
        }

        /* Product image */
        .styled-table img {
            display: block;
            max-width: 50px;
            max-height: 50px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.15);
        }

        /* Status styles */
        .status-pending {
            color: #f59e0b;
            font-weight: 600;
        }
        .status-shipped {
            color: #3b82f6;
            font-weight: 600;
        }
        .status-delivered {
            color: #10b981;
            font-weight: 600;
        }

        /* Progress bar */
        .progress-bar {
            width: 100%;
            background: #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            height: 12px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .progress-fill {
            height: 100%;
            background-color: #10b981;
            transition: width 0.4s ease;
        }

        /* Feedback button */
        .feedback-btn {
            padding: 8px 14px;
            background-color: #3b82f6;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgb(59 130 246 / 0.4);
            transition: background-color 0.3s ease;
            display: inline-block;
            text-align: center;
            min-width: 90px;
        }
        .feedback-btn:hover {
            background-color: #2563eb;
            box-shadow: 0 4px 10px rgb(37 99 235 / 0.6);
        }

        /* Invoice icon */
        .invoice-icon {
            font-size: 22px;
            cursor: pointer;
            color: #2563eb;
            transition: color 0.3s ease;
            display: inline-block;
        }
        .invoice-icon:hover {
            color: #1d4ed8;
        }

        /* No orders message */
        p {
            font-size: 18px;
            color: #64748b;
            text-align: center;
            margin-top: 60px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .container {
                padding: 20px 24px;
                margin: 70px 15px 30px;
            }
            .styled-table td, .styled-table th {
                padding: 12px 10px;
                font-size: 14px;
            }
            .page-title {
                font-size: 24px;
            }
        }

        @media (max-width: 768px) {
            .styled-table thead {
                display: none;
            }
            .styled-table, .styled-table tbody, .styled-table tr, .styled-table td {
                display: block;
                width: 100%;
            }
            .styled-table tr {
                margin-bottom: 18px;
                border-bottom: 2px solid #2563eb;
                border-radius: 12px;
                box-shadow: 0 2px 12px rgb(0 0 0 / 0.05);
            }
            .styled-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                font-size: 14px;
                border-bottom: 1px solid #e2e8f0;
                background-color: #fff;
            }
            .styled-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 16px;
                top: 12px;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 12px;
                color: #334155;
                white-space: nowrap;
            }
            .styled-table td:last-child {
                border-bottom: 0;
            }
            .logout-link {
                text-align: center;
                margin-bottom: 16px;
            }
            button {
                width: 100%;
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1 class="page-title">Product Orders</h1>

    <div class="logout-link">
        <a href="logout.php">Logout</a>
    </div>

    <button onclick="toggleDarkMode()">Toggle Dark Mode</button>

    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by Buyer, Product, or Status" class="search-input" aria-label="Search orders">

    <?php if ($result->num_rows > 0): ?>
        <table class="styled-table" id="ordersTable" aria-live="polite" aria-label="List of product orders">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Buyer Name</th>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Estimated Delivery</th>
                    <th>Progress</th>
                    
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    // Calculate progress percentage based on status
                    switch (strtolower($row['status'])) {
                        case 'pending': $progress = 50; break;
                        
                        case 'arrived': $progress = 100; break;
                        default: $progress = 0;
                    }
                    $status_class = 'status-' . strtolower($row['status']);
                ?>
                <tr>
                    <td data-label="Order ID"><?php echo htmlspecialchars($row['order_id']); ?></td>
                    <td data-label="Order Date"><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['order_date']))); ?></td>
                    <td data-label="Buyer Name"><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                    <td data-label="Product"><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td data-label="Image"><img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Image of <?php echo htmlspecialchars($row['product_name']); ?>"></td>
                    <td data-label="Quantity"><?php echo intval($row['quantity']); ?></td>
                    <td data-label="Status" class="<?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                    <td data-label="Estimated Delivery">
    <?php 
        echo $row['estimated_delivery'] 
            ? htmlspecialchars(date('M d, Y', strtotime($row['estimated_delivery']))) 
            : 'N/A'; 
    ?>
</td>

                    <td data-label="Progress">
                        <div class="progress-bar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo $progress; ?>" role="progressbar" aria-label="Order progress">
                            <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                        </div>
                    </td>
                   
                    <td data-label="Invoice">
                    <a href="invoice.php?order_id=<?php echo $row['order_id']; ?>&download=1" title="Download Invoice">
    🧾 Invoice
</a>


                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

<script>
    function filterTable() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#ordersTable tbody tr");

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? "" : "none";
        });
    }
</script>

</body>
</html>
