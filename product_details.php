<?php
session_start();
include 'includes/db.php';

// Only allow access for logged-in brands
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Brand') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// DEBUG
echo "<pre>";
echo "Logged in User ID: " . $user_id . "\n";
echo "Product ID: " . $product_id . "\n";
echo "</pre>";

// SQL Test
$product_query = mysqli_query($conn, "SELECT * FROM Products WHERE product_id = $product_id AND brand_id = $user_id");

if (!$product_query) {
    die("MySQL Error: " . mysqli_error($conn));
}

$product = mysqli_fetch_assoc($product_query);

// Handle Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $delete_sql = "DELETE FROM Products WHERE product_id = $product_id AND brand_id = $user_id";
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: my_products.php?deleted=1");
        exit();
    } else {
        $error = "Error deleting product: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Details</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h2.page-title {
            text-align: center;
            color: #2d3436;
            font-size: 26px;
            margin-bottom: 25px;
        }

        .product-details p {
            font-size: 17px;
            margin: 10px 0;
            color: #333;
        }

        .product-details img {
            max-width: 250px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s ease-in-out;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            transform: scale(1.03);
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2980b9;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-top: 15px;
            font-size: 15px;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <?php if ($product): ?>
        <h2 class="page-title"><?php echo htmlspecialchars($product['name']); ?></h2>

        <div class="product-details">
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
            <p><strong>Price:</strong> Rs. <?php echo number_format($product['price'], 2); ?></p>

            <?php if (!empty($product['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image">
            <?php endif; ?>
        </div>

        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
            <button type="submit" name="delete" class="btn btn-danger">Delete Product</button>
        </form>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <a href="my_products.php" class="back-link">← Back to My Products</a>

    <?php else: ?>
        <br><br><br><br>
        <h2 class="page-title">Product not found or you do not have permission to view it.</h2>
        <a href="my_products.php" class="back-link">← Back to My Products</a>
    <?php endif; ?>
</div>
</body>
</html>
