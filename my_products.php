<?php
session_start();
include 'includes/db.php';
$user = $_SESSION['user'];
$role = $user['role'];
$id = $_SESSION['user_id'];
include 'includes/header.php';


$sql = "SELECT * FROM Products WHERE availability > 0 AND brand_id = $id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Products | ClothNet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      background-size: 400% 400%;
      animation: bgMove 15s ease infinite;
      color: #fff;
    }

    /* Header */
header {
  background: linear-gradient(to right, #0f172a, #1e3a8a);
  color: #fff;
  padding: 20px 30px;
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}
header h1 {
  font-size: 26px;
  font-weight: bold;
}

/* Navigation */
nav ul {
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-top: 10px;
}
nav ul li a {
  color: #cbd5e1;
  text-decoration: none;
  font-weight: 500;
  padding: 8px 12px;
  border-radius: 6px;
  transition: all 0.3s ease;
}
nav ul li a:hover {
  background-color: #38bdf8;
  color: #0f172a;
}

    @keyframes bgMove {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .container {
      padding: 60px 20px;
      max-width: 1200px;
      margin: auto;
    }

    .page-title {
      font-size: 2.5rem;
      text-align: center;
      margin-bottom: 40px;
      color: #fff;
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 30px;
    }

    .product-card {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .product-card:hover {
      transform: scale(1.03);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
    }

    .product-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 14px;
      margin-bottom: 16px;
    }

    .product-card h3 {
      font-size: 20px;
      margin-bottom: 10px;
      color: #f3f4f6;
    }

    .product-card p {
      font-size: 16px;
      color: #a5b4fc;
      margin-bottom: 15px;
    }

    .btn {
      display: inline-block;
      padding: 12px 18px;
      margin: 6px 4px;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      background: linear-gradient(135deg, #4f46e5, #06b6d4);
      color: #fff;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
      text-decoration: none;
    }

    .btn:hover {
      background: linear-gradient(135deg, #3b82f6, #22d3ee);
      transform: scale(1.05);
    }

    form {
      display: inline;
    }

    @media (max-width: 600px) {
      .page-title {
        font-size: 2rem;
      }
      .product-card img {
        height: 160px;
      }
    }
  </style>
</head>
<body>
<div class="container"><br><br><br><br><br>
  <h2 class="page-title">Available Products</h2>

  <div class="product-grid">
    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="product-card">
          <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product Image">
          <h3><?php echo htmlspecialchars($row['name']); ?></h3>
          <p>Rs. <?php echo number_format($row['price'], 2); ?></p>
          <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn">👁 View</a>
          <form action="cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
            <input type="hidden" name="quantity" value="1">
            
          </form>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center;">No products available right now 😢</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
