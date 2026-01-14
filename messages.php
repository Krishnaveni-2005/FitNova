<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$trainer_id = isset($_GET['trainer']) ? intval($_GET['trainer']) : 0;

// Fetch trainer details if approved
// We check if THIS user is assigned to THIS trainer AND status is approved.
// If trainer_id is not passed, we try to find the assigned trainer.
if ($trainer_id === 0) {
    $sql = "SELECT assigned_trainer_id FROM users WHERE user_id = ? AND assignment_status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $trainer_id = $res->fetch_assoc()['assigned_trainer_id'];
    }
}

// Security Check
$approved = false;
$trainerName = "";

if ($trainer_id > 0) {
    // Verify assignment and approval
    $checkSql = "SELECT u.assignment_status, t.first_name, t.last_name 
                 FROM users u 
                 JOIN users t ON t.user_id = ?
                 WHERE u.user_id = ? AND u.assigned_trainer_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("iii", $trainer_id, $user_id, $trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['assignment_status'] === 'approved') {
            $approved = true;
            $trainerName = $row['first_name'] . " " . $row['last_name'];
        }
    }
}

if (!$approved) {
    // If not approved, user shouldn't be here.
    echo "<!DOCTYPE html><html><head><title>Access Denied</title><link href='https://fonts.googleapis.com/css2?family=Outfit&display=swap' rel='stylesheet'><style>body{font-family:'Outfit',sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;background:#f8f9fa;margin:0;} .card{background:white;padding:40px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.1);text-align:center;max-width:400px;} h2{color:#0F2C59;} p{color:#666;margin-bottom:20px;} .btn{background:#0F2C59;color:white;text-decoration:none;padding:10px 20px;border-radius:5px;}</style></head><body><div class='card'><h2>Access Restricted</h2><p>You can only message a trainer who has accepted your hire request.</p><a href='prouser_dashboard.php' class='btn'>Go to Dashboard</a></div></body></html>";
    exit();
}

// Handle Sending Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $insSql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insSql);
        $stmt->bind_param("iis", $user_id, $trainer_id, $msg);
        $stmt->execute();
        
        // Notify Trainer
        $notifMsg = "New message from client " . ($_SESSION['user_name'] ?? 'Client');
        // Ideally we should have a 'trainer_notifications' table or share 'user_notifications'
        // Since schema is shared 'user_notifications' referenced by user_id:
        $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'new_message_client', ?)";
        $nStmt = $conn->prepare($notifSql);
        $nStmt->bind_param("is", $trainer_id, $notifMsg);
        $nStmt->execute();
        $nStmt->close();
    }
    header("Location: messages.php?trainer=" . $trainer_id);
    exit();
}

// Fetch Messages
$msgs = [];
$msgSql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC";
$stmt = $conn->prepare($msgSql);
$stmt->bind_param("iiii", $user_id, $trainer_id, $trainer_id, $user_id);
$stmt->execute();
$msgs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($trainerName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; font-family: 'Outfit', sans-serif; background: #f0f2f5; height: 100vh; display: flex; flex-direction: column; }
        .header { background: #fff; padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; }
        .back-btn { color: #333; text-decoration: none; font-size: 1.1rem; }
        .trainer-info h3 { margin: 0; font-size: 1.1rem; color: #0F2C59; }
        .trainer-info span { font-size: 0.8rem; color: #28a745; display: flex; align-items: center; gap: 5px; }
        
        .chat-container { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        .message { max-width: 70%; padding: 10px 15px; border-radius: 15px; font-size: 0.95rem; line-height: 1.5; position: relative; }
        .sent { background: #0F2C59; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
        .received { background: white; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .time { font-size: 0.7rem; margin-top: 5px; opacity: 0.7; text-align: right; }
        
        .input-area { background: white; padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 10px; }
        .input-area input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 25px; outline: none; transition: border 0.3s; }
        .input-area input:focus { border-color: #0F2C59; }
        .send-btn { background: #0F2C59; color: white; border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; }
        .send-btn:hover { transform: scale(1.1); }
    </style>
</head>
<body>
    
    <div class="header">
        <a href="prouser_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <div class="trainer-info">
            <h3><?php echo htmlspecialchars($trainerName); ?></h3>
            <span><i class="fas fa-circle" style="font-size: 6px;"></i> Active Now</span>
        </div>
        <div style="width: 24px;"></div>
    </div>

    <div class="chat-container" id="chatContainer">
        <?php foreach ($msgs as $m): 
            $isMe = $m['sender_id'] == $user_id;
        ?>
            <div class="message <?php echo $isMe ? 'sent' : 'received'; ?>">
                <?php echo htmlspecialchars($m['message_text']); ?>
                <div class="time"><?php echo date('h:i A', strtotime($m['created_at'])); ?></div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($msgs)): ?>
            <p style="text-align: center; color: #999; margin-top: 50px;">Start your training conversation!</p>
        <?php endif; ?>
    </div>

    <form class="input-area" method="POST">
        <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
        <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
    </form>

    <script>
        const container = document.getElementById('chatContainer');
        container.scrollTop = container.scrollHeight;
    </script>

</body>
</html>
