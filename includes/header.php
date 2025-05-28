<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']); // Get current file name
?>

<header>
    <h1><?= htmlspecialchars($role) ?> Dashboard</h1>
    <nav>
        <ul>
            <li><a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">Home</a></li>

            <?php if ($role === 'Brand'): ?>
                <li><a href="add_product.php" class="<?= $currentPage == 'add_product.php' ? 'active' : '' ?>">Add Product</a></li>
                <li><a href="my_products.php" class="<?= $currentPage == 'my_products.php' ? 'active' : '' ?>">My Products</a></li>
                <li><a href="product_orders.php" class="<?= $currentPage == 'product_orders.php' ? 'active' : '' ?>">View Orders</a></li>
                <li><a href="brand_dashboard.php" class="<?= $currentPage == 'brand_dashboard.php' ? 'active' : '' ?>">View</a></li>
            <?php elseif ($role === 'Shop Owner'): ?>
                <li><a href="products.php" class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">Browse Products</a></li>
                <li><a href="favorites.php" class="<?= $currentPage == 'favorites.php' ? 'active' : '' ?>">Favorites</a></li>
                <li><a href="orders.php" class="<?= $currentPage == 'orders.php' ? 'active' : '' ?>">My Orders</a></li>
            <?php endif; ?>
            <li><a href="notifications.php" class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">Notifications</a></li>
            <li style="position: relative;">
                <a href="chat.php" class="<?= $currentPage == 'chat.php' ? 'active' : '' ?>">Messages</a>
                <span id="messageNotifCount" class="notif-badge" style="display:none;">0</span>
            </li>
            <li><a href="logout.php" class="<?= $currentPage == 'logout.php' ? 'active' : '' ?>">Logout</a></li>
        </ul>
    </nav>
</header>

<script>
function fetchUnreadMessages() {
    fetch('get_unreadcount.php')
        .then(response => response.json())
        .then(data => {
            const countBadge = document.getElementById('messageNotifCount');
            if (data.count > 0) {
                countBadge.textContent = data.count;
                countBadge.style.display = 'inline-block';
            } else {
                countBadge.style.display = 'none';
            }
        });
}
fetchUnreadMessages();
setInterval(fetchUnreadMessages, 5000); // refresh every 5 seconds
</script>
