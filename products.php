<?php
include 'includes/db.php';

// Fetch products
$sql = "SELECT p.*, u.name AS brand_name FROM Products p 
        JOIN Users u ON p.brand_id = u.user_id 
        WHERE p.availability = 1";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClothNet | Browse Products</title>
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<?php include 'includes/header.php'; ?>
<body>
  <div class="container">
    <h2>Available Products</h2>
    <div class="product-grid">
      <?php while ($product = mysqli_fetch_assoc($result)) : ?>
        <div class="product-card">
          <img src="<?php echo $product['image_url'] ?: 'placeholder.jpg'; ?>" alt="Product Image">
          <h3><?php echo htmlspecialchars($product['name']); ?></h3>
          <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand_name']); ?></p>
          <p><?php echo htmlspecialchars($product['category']); ?></p>
          <p>Rs. <?php echo number_format($product['price'], 2); ?></p>

          <!-- Add to Cart Form -->
          <form action="cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <input type="number" name="quantity" value="1" min="1" class="small-input">
            <button type="submit" name="add_to_cart">Add to Cart</button>
          </form>

          <!-- Add Review Button -->
          <form action="reviews.php" method="GET" style="margin-top: 10px;">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <button type="submit">Add Review</button>
          </form>

        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>

<!-- Original styling preserved -->
<style>
/* Reset & Base Styles */
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

/* Container */
.container {
  max-width: 1200px;
  margin: auto;
  padding: 30px 20px;
}

h2 {
  text-align: center;
  font-size: 30px;
  font-weight: 700;
  margin-bottom: 40px;
  color: rgb(198, 206, 223);
}

/* Product Grid */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
  gap: 24px;
}

/* Product Card */
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
  transform: translateY(-6px);
  box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
}

.product-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 14px;
  margin-bottom: 16px;
  background: #f1f5f9;
}

.product-card h3 {
  font-size: 20px;
  margin: 10px 0;
  color:rgb(255, 255, 255);
}

.product-card p {
  font-size: 14px;
  color: #475569;
  margin: 4px 0;
}

/* Cart & Review Form */
.product-card form {
  margin-top: 12px;
  width: 100%;
}

.small-input {
  width: 60px;
  padding: 8px 10px;
  margin-right: 10px;
  border: 1.8px solid #cbd5e1;
  border-radius: 10px;
  font-size: 15px;
  background-color: #f9fafb;
  transition: all 0.3s ease;
}

.small-input:focus {
  border-color: #3b82f6;
  background-color: #ffffff;
  box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
  outline: none;
}

.product-card button {
  padding: 10px 16px;
  background: linear-gradient(to right, #0ea5e9, #3b82f6);
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  font-size: 15px;
  transition: background 0.3s ease, transform 0.3s ease;
}

.product-card button:hover {
  background: linear-gradient(to right, #0284c7, #2563eb);
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
  .product-grid {
    grid-template-columns: 1fr;
  }

  .small-input {
    width: 100%;
    margin-bottom: 10px;
  }

  .product-card form {
    display: flex;
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
</html>
