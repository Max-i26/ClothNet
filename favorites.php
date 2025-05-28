<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'Shop Owner') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch favourite products details with brand name
$sql = "
    SELECT P.product_id, P.name, P.image_url, P.price, P.description, B.name AS brand_name 
    FROM favourites F
    JOIN Products P ON F.product_id = P.product_id
    JOIN Users B ON P.brand_id = B.user_id
    WHERE F.user_id = $user_id
    ORDER BY F.added_on DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Favourite Products</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
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
        .fav-container {
            max-width: 900px;
            margin: 80px auto;
            padding: 20px;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            border-radius: 16px;
            color: #c3c7cf;
            font-family: 'Poppins', sans-serif;
        }
        .fav-item {
            display: flex;
            background-color: #1e293b;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            gap: 20px;
            align-items: center;
            transition: transform 0.3s ease;
        }
        .fav-item:hover {
            transform: scale(1.02);
        }
        .fav-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.6);
        }
        .fav-info {
            flex-grow: 1;
        }
        .fav-info h3 {
            margin: 0 0 8px 0;
            color: #fff;
        }
        .fav-info p {
            margin: 5px 0;
        }
        .fav-info .price {
            font-weight: 700;
            color: #06b6d4;
            font-size: 18px;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="fav-container">
    <h2>My Favourite Products</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
                $img = !empty($row['image_url']) && file_exists($row['image_url']) 
                    ? htmlspecialchars($row['image_url']) 
                    : "images/1747933538_download (8).jpeg"; // fallback image
            ?>
            <div class="fav-item">
                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['name'] ?? 'Product'); ?>">
                <div class="fav-info">
                    <h3><?php echo htmlspecialchars($row['name'] ?? ''); ?></h3>
                    <p><strong>Brand:</strong> <?php echo htmlspecialchars($row['brand_name'] ?? ''); ?></p>
                    <p class="price">Price: $<?php echo number_format($row['price'] ?? 0, 2); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($row['description'] ?? '')); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no favourite products yet. Start reviewing products with 4 or 5 stars!</p>
    <?php endif; ?>
</div>

</body>
</html>
