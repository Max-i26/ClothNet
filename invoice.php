<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Brand') {
    header('Location: login.php');
    exit();
}

$brand_id = $_SESSION['user_id'];
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die('Invalid order ID.');
}

$order_id = intval($_GET['order_id']);

$sql = "
SELECT 
    po.order_id,
    o.order_date,
    o.total_price,
    p.name AS product_name,
    p.image_url,
    p.price AS product_price,
    po.quantity,
    u.user_id AS shop_owner_id,
    u.name AS buyer_name,
    u.email AS buyer_email,
    u.address AS buyer_address,
    u.phone_number AS buyer_phone,
    b.name AS brand_name,
    b.phone_number AS brand_phone
FROM Product_Orders po
JOIN Orders o ON po.order_id = o.order_id
JOIN Products p ON po.product_id = p.product_id
JOIN Users u ON po.shop_owner_id = u.user_id
JOIN Users b ON p.brand_id = b.user_id
WHERE po.order_id = ? AND p.brand_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $order_id, $brand_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Order not found or unauthorized access.');
}

$orderItems = $result->fetch_all(MYSQLI_ASSOC);
$shop_owner_id = $orderItems[0]['shop_owner_id'];
$orderDate     = $orderItems[0]['order_date'];
$buyerName     = $orderItems[0]['buyer_name'];
$buyerEmail    = $orderItems[0]['buyer_email'];
$buyerPhone    = $orderItems[0]['buyer_phone'];
$buyerAddress  = $orderItems[0]['buyer_address'];
$brandName     = $orderItems[0]['brand_name'];
$brandPhone    = $orderItems[0]['brand_phone'];

// Generate HTML rows
$totalAmount = 0;
$html_rows = '';
foreach ($orderItems as $item) {
    $unitPrice = $item['product_price'];
    $subtotal = $unitPrice * $item['quantity'];
    $totalAmount += $subtotal;
    $html_rows .= "<tr>
        <td>{$item['product_name']}</td>
        <td>{$item['quantity']}</td>
        <td>Rs. " . number_format($unitPrice, 2) . "</td>
        <td>Rs. " . number_format($subtotal, 2) . "</td>
    </tr>";
}

// Build Modern PDF HTML
$html = '
<style>
    body { font-family: "Segoe UI", sans-serif; font-size: 13px; background-color: #fff; color: #333; }
    .invoice-box { padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); max-width: 800px; margin: auto; }
    h1 { text-align: center; color: #2563eb; margin-bottom: 20px; }
    .info { margin-bottom: 25px; line-height: 1.6; }
    .info strong { color: #111; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #2563eb; color: white; }
    .total-row td { font-weight: bold; border-top: 2px solid #2563eb; }
    .footer { text-align: center; font-size: 12px; color: #666; margin-top: 40px; }
</style>

<div class="invoice-box">
    <h1>Invoice #'.$order_id.'</h1>

    <div class="info">
        <strong>Order Date:</strong> '.date('M d, Y', strtotime($orderDate)).'<br>
        <strong>Buyer:</strong> '.$buyerName.'<br>
        <strong>Email:</strong> '.$buyerEmail.'<br>
        <strong>Phone:</strong> '.$buyerPhone.'<br>
        <strong>Address:</strong> '.$buyerAddress.'<br><br>

        <strong>Brand:</strong> '.$brandName.'<br>
        <strong>Brand Phone:</strong> '.$brandPhone.'
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            '.$html_rows.'
            <tr class="total-row">
                <td colspan="3" align="right">Total Amount:</td>
                <td>Rs. '.number_format($totalAmount, 2).'</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Thank you for doing business with us! <br>Powered by <strong>ClothNet</strong>.
    </div>
</div>
';

// Generate and save PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$invoice_filename = "invoices/invoice_{$order_id}.pdf";
file_put_contents($invoice_filename, $dompdf->output());

// Notify shop owner
$notif_check = mysqli_query($conn, "SELECT * FROM Notifications WHERE user_id = $shop_owner_id AND message LIKE '%invoice #$order_id%'");
if (mysqli_num_rows($notif_check) == 0) {
    $msg = "📥 Your invoice for order #$order_id is ready. <a href='$invoice_filename' download>Click here to download</a>";
    $notif_stmt = $conn->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)");
    $notif_stmt->bind_param('is', $shop_owner_id, $msg);
    $notif_stmt->execute();
}

// Immediate download
if (isset($_GET['download']) && $_GET['download'] == 1) {
    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=invoice_$order_id.pdf");
    echo $dompdf->output();
    exit;
}

// Confirmation
echo "<script>alert('Invoice generated and sent to shop owner!'); window.location.href='product_orders.php';</script>";
exit;
?>
