<?php
include 'includes/db.php';
session_start();

$current_user_id = $_SESSION['user_id'];
$receiver_id = intval($_GET['receiver_id']);

// ✅ Mark messages as seen when user views the chat
mysqli_query($conn, "UPDATE Messages 
    SET status1 = 'seen' 
    WHERE receiver_id = $current_user_id 
      AND sender_id = $receiver_id 
      AND status1 != 'seen'");

// 📨 Fetch messages
$sql = "
    SELECT * FROM Messages 
    WHERE (sender_id = $current_user_id AND receiver_id = $receiver_id)
       OR (sender_id = $receiver_id AND receiver_id = $current_user_id)
    ORDER BY timestamp ASC
";
$result = mysqli_query($conn, $sql);

while ($msg = mysqli_fetch_assoc($result)) {
    $class = $msg['sender_id'] == $current_user_id ? 'sent' : 'received';

    echo "<div class='msg $class'>";
    echo nl2br(htmlspecialchars($msg['message_text']));

    if ($msg['file_path']) {
        echo "<br><a href='{$msg['file_path']}' class='file-link' download>📎 Download file</a>";
    }

    // ✅ Tick indicator
    if ($msg['sender_id'] == $current_user_id) {
        $tick = ($msg['status1'] === 'seen') ? '✅✅ Seen' : (($msg['status1'] === 'delivered') ? '✅ Delivered' : '🕓 Sent');
        echo "<div class='status'>$tick</div>";
    } else {
        echo "<div class='status'>" . date('h:i A', strtotime($msg['timestamp'])) . "</div>";
    }

    echo "</div>";
}
?>
