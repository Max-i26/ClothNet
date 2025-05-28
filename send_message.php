<?php
include 'includes/db.php';
session_start();

if (!isset($_POST['sender_id'], $_POST['receiver_id'])) {
    http_response_code(400);
    exit("Missing sender or receiver ID");
}

$sender_id = intval($_POST['sender_id']);
$receiver_id = intval($_POST['receiver_id']);
$message_text = isset($_POST['message_text']) ? trim($_POST['message_text']) : '';
$file_url = '';

// Handle file upload
if (!empty($_FILES['file']['name'])) {
    $uploadDir = "uploads/chat/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($_FILES["file"]["name"]);
    $targetPath = $uploadDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetPath)) {
        $file_path = $targetPath;
    }
}

// Insert message into DB
$stmt = $conn->prepare("INSERT INTO Messages (sender_id, receiver_id, message_text, file_path, status) VALUES (?, ?, ?, ?, 'sent')");
$stmt->bind_param("iiss", $sender_id, $receiver_id, $message_text, $file_path);

if ($stmt->execute()) {
    echo "Message sent";

    // Add notification
    $notif_msg = "📨 New message from user ID $sender_id. <a href='chat.php?receiver_id=$sender_id'>Click to view</a>";
    $notif = $conn->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)");
    $notif->bind_param("is", $receiver_id, $notif_msg);
    $notif->execute();
} else {
    http_response_code(500);
    echo "Error: " . $stmt->error;
}
?>
