<?php 
ob_start();
include 'includes/db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - ClothNet</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    * {
      margin: 0; padding: 0; box-sizing: border-box;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f172a, #1e3a8a, #3b82f6);
      background-size: 400% 400%;
      animation: gradientMove 20s ease infinite;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    @keyframes gradientMove {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }
    .container {
      backdrop-filter: blur(20px);
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 40px;
      width: 90%;
      max-width: 450px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      animation: fadeIn 1s ease forwards;
      color: #fff;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    .container::before {
      content: '';
      position: absolute;
      top: -40%;
      left: -40%;
      width: 180%;
      height: 180%;
      background: radial-gradient(circle, rgba(255,255,255,0.05), transparent);
      animation: sparkle 8s linear infinite;
      pointer-events: none;
      z-index: 0;
    }
    @keyframes sparkle {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: scale(0.95);}
      to {opacity: 1; transform: scale(1);}
    }
    h2 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 25px;
      position: relative;
      color: #e0f2fe;
    }
    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: #60a5fa;
      border-radius: 2px;
    }
    input, select {
      width: 100%;
      padding: 14px 18px;
      margin: 12px 0;
      border: none;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      font-size: 15px;
      outline: none;
      transition: 0.3s ease;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);
    }
    input::placeholder, select {
      color: #cbd5e1;
    }
    input:focus, select:focus {
      background: rgba(255, 255, 255, 0.25);
      box-shadow: 0 0 8px #93c5fd;
    }
    button {
      width: 100%;
      padding: 14px;
      margin-top: 10px;
      background: linear-gradient(to right, #60a5fa, #3b82f6);
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      z-index: 1;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    button::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 0;
      height: 100%;
      background: rgba(255,255,255,0.15);
      z-index: 0;
      transition: width 0.4s ease;
    }
    button:hover::before {
      width: 100%;
    }
    button:hover {
      transform: scale(1.02);
      box-shadow: 0 0 15px rgba(96, 165, 250, 0.5);
    }
    p {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }
    a {
      color: #93c5fd;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    a:hover {
      text-decoration: underline;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Sign Up</h2>
    <form method="POST">
      <input type="text" name="name" placeholder="Full Name" required><br>
      <input type="email" name="email" placeholder="Email" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <input type="text" name="phone_number" placeholder="Phone Number" required><br>
      <input type="text" name="address" placeholder="Address" required><br>
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="Brand">Brand</option>
        <option value="Shop Owner">Shop Owner</option>
      </select><br>
      <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name     = $conn->real_escape_string($_POST['name']);
  $email    = $conn->real_escape_string($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
  $role     = $conn->real_escape_string($_POST['role']);
  $phone    = $conn->real_escape_string($_POST['phone_number']);
  $address  = $conn->real_escape_string($_POST['address']);

  // Check for existing email
  $checkQuery = "SELECT * FROM users WHERE email = '$email'";
  $result = $conn->query($checkQuery);

  if ($result && $result->num_rows > 0) {
      echo "<script>alert('Email already registered. Please try logging in.');</script>";
  } else {
      $sql = "INSERT INTO users (name, email, password, phone_number, address, role) 
              VALUES ('$name', '$email', '$password', '$phone', '$address', '$role')";

      if ($conn->query($sql)) {
          header("Location: login.php");
          exit;
      } else {
          echo "Error: " . $conn->error;
      }
  }
}
ob_end_flush();
?>
