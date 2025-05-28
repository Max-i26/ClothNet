<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$query = $conn->prepare("SELECT notification_id, message, timestamp FROM notifications WHERE user_id = ? ORDER BY timestamp DESC");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body {
    font-family: 'Outfit', sans-serif;
    background: radial-gradient(circle at top left, #0f172a, #1e293b);
    color: #f8fafc;
    padding: 60px 20px;
    min-height: 100vh;
}

.notifications-container {
    max-width: 800px;
    margin-top: 100px;
    margin-left: auto;
    margin-right: auto;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 0 20px rgba(0, 229, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    animation: fadeIn 0.6s ease-out;
}

.notifications-container h2 {
    text-align: center;
    font-size: 28px;
    color: #22d3ee;
    margin-bottom: 50px;
}

.notif-item {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.6), rgba(2, 132, 199, 0.15));
    padding: 20px;
    border-radius: 14px;
    margin-bottom: 18px;
    transition: all 0.3s ease-in-out;
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.notif-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 229, 255, 0.08);
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(2, 132, 199, 0.2));
}

.notif-item a {
    color: #38bdf8;
    font-weight: 500;
}

.notif-item a:hover {
    text-decoration: underline;
}

.notif-time {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 6px;
    display: block;
}

/* Responsive */
@media (max-width: 600px) {
    .notifications-container {
        padding: 20px;
    }

    .notif-item {
        padding: 16px;
    }
}

/* Fade In Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="notifications-container">
    <h2>🔔 Your Notifications</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="notif-item">
                <?= $row['message'] ?>
                <div class="notif-time"><?= date("F j, Y, g:i a", strtotime($row['timestamp'])) ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;">No notifications found.</p>
    <?php endif; ?>
</div>

</body>
</html>
