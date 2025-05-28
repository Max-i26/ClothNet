<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['role'];
$opposite_role = $current_user_role === 'Brand' ? 'Shop Owner' : 'Brand';

$users_sql = "SELECT user_id, name FROM Users WHERE role = ?";
$stmt = $conn->prepare($users_sql);
$stmt->bind_param('s', $opposite_role);
$stmt->execute();
$users_result = $stmt->get_result();

$users_array = [];
while ($user = $users_result->fetch_assoc()) {
    $users_array[] = $user;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
  background-size: 400% 400%;
  animation: bgMove 15s ease infinite;
  color: #fff;
}

.chat-wrapper {
  max-width: 950px;
  height: 85vh;
  display: flex;
  flex-direction: column;
  margin: 80px auto 20px;
  background: linear-gradient(135deg, rgba(18, 18, 28, 0.9), rgba(34, 34, 59, 0.9));
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  overflow: hidden;
}

.chat-wrapper h2 {
  text-align: center;
  font-size: 28px;
  color: #ffffff;
  margin-bottom: 20px;
}

.search-user {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.search-user input,
.search-user select,
.search-user button {
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 15px;
  border: none;
  background-color: #2c2c54;
  color: #f1f5f9;
  outline: none;
}

.search-user button {
  background: linear-gradient(90deg, #4e54c8, #8f94fb);
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.search-user button:hover {
  background: linear-gradient(90deg, #667eea, #764ba2);
}

.messages-box {
  flex: 1;
  overflow-y: auto;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid #ffffff33;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  scroll-behavior: smooth;
}

.msg {
  padding: 12px 18px;
  font-size: 15px;
  border-radius: 20px;
  max-width: 70%;
  word-wrap: break-word;
  display: inline-block;
  position: relative;
  transition: all 0.3s ease;
}

.msg.sent {
  align-self: flex-end;
  background: linear-gradient(135deg, #4e54c8, #8f94fb);
  color: white;
  text-align: right;
}

.msg.received {
  align-self: flex-start;
  background: linear-gradient(135deg, #e0eafc, #cfdef3);
  color: #1e293b;
}

.input-container {
  display: flex;
  gap: 10px;
  margin-top: 15px;
  padding-top: 10px;
  border-top: 1px solid #ffffff22;
  align-items: center;
}

.input-container textarea {
  flex: 1;
  resize: none;
  padding: 14px;
  border-radius: 12px;
  font-size: 15px;
  background-color: #f1f5f9;
  color: #1e293b;
  border: none;
  height: 48px;
}

.input-container input[type="file"] {
  background-color: #fff;
  padding: 8px;
  border-radius: 10px;
  font-size: 12px;
  color: #1e293b;
}

.input-container button {
  padding: 10px 20px;
  background: linear-gradient(to right, #00b4db, #0083b0);
  color: white;
  font-size: 16px;
  font-weight: 600;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  transition: 0.3s ease;
}

.input-container button:hover {
  background: linear-gradient(to right, #43cea2, #185a9d);
}
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="chat-wrapper">
    <h2>💬 Chat System</h2>

    <form id="selectUserForm" method="GET">
        <div class="search-user">
            <input type="text" id="userSearch" placeholder="Search <?php echo $opposite_role; ?> name...">
            <select name="receiver_id" id="userSelect" required>
                <option value="">-- Select a <?php echo $opposite_role; ?> --</option>
                <?php foreach ($users_array as $user): ?>
                    <option value="<?= $user['user_id'] ?>" <?= isset($_GET['receiver_id']) && $_GET['receiver_id'] == $user['user_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="searchBtn">Search</button>
        </div>
    </form>

    <script>
        const originalOptions = [...document.querySelectorAll('#userSelect option')];
        const searchBtn = document.getElementById('searchBtn');
        const userSearch = document.getElementById('userSearch');
        const userSelect = document.getElementById('userSelect');

        searchBtn.addEventListener('click', () => {
            const searchVal = userSearch.value.trim().toLowerCase();
            userSelect.innerHTML = '';

            if (!searchVal) {
                originalOptions.forEach(opt => userSelect.appendChild(opt.cloneNode(true)));
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Select a user --';
            userSelect.appendChild(defaultOpt);

            originalOptions.forEach(opt => {
                if (opt.value && opt.textContent.toLowerCase().includes(searchVal)) {
                    userSelect.appendChild(opt.cloneNode(true));
                }
            });
        });

        userSelect.addEventListener('change', () => {
            if (userSelect.value !== "") {
                document.getElementById('selectUserForm').submit();
            }
        });
    </script>

    <?php if (isset($_GET['receiver_id'])): 
        $receiver_id = intval($_GET['receiver_id']);
    ?>
        <div id="messages" class="messages-box"></div>

        <form id="sendForm" enctype="multipart/form-data" class="input-container">
            <input type="hidden" name="sender_id" value="<?= $current_user_id ?>">
            <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">

            <textarea name="message_text" rows="1" placeholder="Type your message..." required></textarea>
            <input type="file" name="file">
            <button type="submit">Send</button>
        </form>

        <script>
            const receiver_id = <?= $receiver_id ?>;

            function loadMessages() {
                fetch(`get_messages.php?receiver_id=${receiver_id}`)
                    .then(res => res.text())
                    .then(html => {
                        const box = document.getElementById('messages');
                        box.innerHTML = html;
                        box.scrollTop = box.scrollHeight;
                    });
            }

            setInterval(loadMessages, 2000);
            loadMessages();

            document.getElementById('sendForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    this.reset();
                    loadMessages();
                });
            });
        </script>
    <?php endif; ?>
</div>
</body>
</html>
