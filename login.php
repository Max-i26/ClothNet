<?php
session_start();
include 'includes/db.php';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['user_id'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - ClothNet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    /* 🌈 Background */
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      height: 100vh;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      background-size: 400% 400%;
      animation: gradientBG 10s ease infinite;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes gradientBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    /* ✨ Glass Card */
    .container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
      width: 90%;
      max-width: 400px;
      color: #fff;
      text-align: center;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    h2 {
      font-size: 28px;
      margin-bottom: 20px;
      color: #fff;
      font-weight: 600;
    }

    /* 🎯 Inputs */
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 14px 16px;
      margin: 10px 0;
      border: none;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      font-size: 16px;
      outline: none;
      transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    input::placeholder {
      color: #d1d5db;
    }

    input:focus {
      background: rgba(255, 255, 255, 0.3);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.6);
    }

    /* 🔥 Button */
    button {
      margin-top: 16px;
      width: 100%;
      padding: 14px;
      font-size: 16px;
      font-weight: bold;
      background: linear-gradient(135deg, #4f46e5, #0ea5e9);
      color: #fff;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: transform 0.3s ease, background 0.3s ease;
    }

    button:hover {
      transform: scale(1.05);
      background: linear-gradient(135deg, #3b82f6, #06b6d4);
    }

    /* 🚨 Error */
    .error {
      background: rgba(255, 0, 0, 0.15);
      color: #f87171;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    /* 🧷 Link */
    p {
      margin-top: 20px;
      color: #cbd5e1;
    }

    a {
      color: #60a5fa;
      text-decoration: none;
      font-weight: bold;
    }

    a:hover {
      text-decoration: underline;
    }

    /* 📱 Mobile */
    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
      }

      h2 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Welcome to ClothNet 💫</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
      <input type="email" name="email" placeholder="Email" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <button type="submit">Log In</button>
    </form>
    <p>New here? <a href="signup.php">Join the future</a></p>
  </div>
</body>
</html>
