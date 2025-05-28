<?php
session_start();
require 'includes/db.php';
$user = $_SESSION['user'];
$role = $user['role'];

// Ensure only Brand users can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Brand') {
    header("Location: login.php");
    exit();
}

$brand_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $category    = $_POST['category'];
    $availability = isset($_POST['availability']) ? 1 : 0;

    // Handle file upload
    $image_url = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "images/";
        $filename   = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO Products (name, description, price, category, image_url, availability, brand_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssii", $name, $description, $price, $category, $image_url, $availability, $brand_id);

    if ($stmt->execute()) {
        $message = "✅ Product added successfully!";
    } else {
        $message = "❌ Error adding product: " . $stmt->error;
    }

    $stmt->close();
}
?>

<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    
  <link rel="stylesheet" href="css/dashboard.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    
</head>
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Outfit', sans-serif;
  background: linear-gradient(135deg, #dbeafe, #f0f9ff);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  overflow-x: hidden;
}

.container {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
  padding: 50px 40px;
  width: 100%;
  max-width: 720px;
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.3);
  animation: fadeIn 1s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(25px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.container h2 {
  font-size: 30px;
  text-align: center;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 10px;
}

.container p {
  font-size: 14.5px;
  text-align: center;
  color: #64748b;
  margin-bottom: 25px;
}

/* Flash Message Styling */
.container > p[style] {
  font-weight: 600;
  padding: 12px 16px;
  border-radius: 10px;
  text-align: center;
  margin-top: 12px;
  animation: pulse 0.4s ease-in;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
@keyframes pulse {
  0% { transform: scale(0.9); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

/* Form Elements */
form label {
  display: block;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
  font-size: 15px;
}

form input[type="text"],
form input[type="number"],
form input[type="file"],
form textarea {
  width: 100%;
  padding: 14px 16px;
  margin-bottom: 20px;
  font-size: 15px;
  border: 1.5px solid #cbd5e1;
  border-radius: 12px;
  background: rgba(255,255,255,0.95);
  transition: 0.3s ease;
}

form input:focus,
form textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
  outline: none;
}

/* Checkbox */
label[for="availability"] {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 16px 0 26px;
  font-size: 15px;
  color: #475569;
}

input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #3b82f6;
  cursor: pointer;
}

/* Button */
form button {
  width: 100%;
  background: linear-gradient(to right, #3b82f6, #6366f1);
  color: #fff;
  padding: 15px 0;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

form button:hover {
  background: linear-gradient(to right, #4338ca, #4f46e5);
  box-shadow: 0 8px 22px rgba(79, 70, 229, 0.3);
}
</style>


<div class="container">
  <h2>✨ Add a New Product</h2>

  <?php if ($message): ?>
      <p style="color: <?= strpos($message, '✅') !== false ? '#22c55e' : '#ef4444' ?>;">
          <?= htmlspecialchars($message) ?>
      </p>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" novalidate>
      <label for="name">Product Name:</label>
      <input type="text" name="name" id="name" placeholder="Enter product name..." required>

      <label for="description">Description:</label>
      <textarea name="description" id="description" rows="4" placeholder="Write a short description..."></textarea>

      <label for="price">Price (Rs):</label>
      <input type="number" step="0.01" min="0" name="price" id="price" placeholder="Enter product price..." required>

      <label for="category">Category:</label>
      <input type="text" name="category" id="category" placeholder="Eg: Shirts, Pants..." required>

      <label for="image">Image:</label>
      <input type="file" name="image" id="image" accept="image/*">

      <label for="availability">
          <input type="checkbox" name="availability" id="availability" checked>
          Available
      </label>

      <button type="submit">➕ Add Product</button>
  </form>
</div>
