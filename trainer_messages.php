<?php
session_start();
require "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

$trainerId = $_SESSION['user_id'];
$trainerName = $_SESSION['user_name'];
$trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr(explode(' ', $trainerName)[1] ?? '', 0, 1));

// Fetch all contacts (users who have exchanged messages with this trainer)
$contactsSql = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.role
                FROM users u
                JOIN messages m ON (u.user_id = m.sender_id OR u.user_id = m.receiver_id)
                WHERE (m.sender_id = ? OR m.receiver_id = ?) 
                AND u.user_id != ?
                AND (u.assigned_trainer_id = ? AND u.assignment_status = 'approved')
                ORDER BY m.created_at DESC";
$stmt = $conn->prepare($contactsSql);
$stmt->bind_param("iiii", $trainerId, $trainerId, $trainerId, $trainerId);
$stmt->execute();
$contactsResult = $stmt->get_result();
$contacts = $contactsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// If no contacts, let's just get assigned clients as potential contacts
if (empty($contacts)) {
    $clientsSql = "SELECT user_id, first_name, last_name, role FROM users WHERE assigned_trainer_id = ? AND assignment_status = 'approved' LIMIT 5";
    $stmt = $conn->prepare($clientsSql);
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$selectedContactId = $_GET['chat_with'] ?? ($contacts[0]['user_id'] ?? null);
$chatMessages = [];

if ($selectedContactId) {
    // Fetch messages for the selected contact
    $msgSql = "SELECT * FROM messages 
               WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
               ORDER BY created_at ASC";
    $stmt = $conn->prepare($msgSql);
    $stmt->bind_param("iiii", $trainerId, $selectedContactId, $selectedContactId, $trainerId);
    $stmt->execute();
    $chatMessages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Mark as read
    $updateRead = "UPDATE messages SET is_read = TRUE WHERE sender_id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($updateRead);
    $stmt->bind_param("ii", $selectedContactId, $trainerId);
    $stmt->execute();
    $stmt->close();
}

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'])) {
    $receiverId = $_POST['receiver_id'];
    $text = $conn->real_escape_string($_POST['message_text']);
    if (!empty($text)) {
        // Verify approval
        $checkSql = "SELECT 1 FROM users WHERE user_id = ? AND assigned_trainer_id = ? AND assignment_status = 'approved'";
        $chkStmt = $conn->prepare($checkSql);
        $chkStmt->bind_param("ii", $receiverId, $trainerId);
        $chkStmt->execute();
        if ($chkStmt->get_result()->num_rows > 0) {
            $sendSql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sendSql);
            $stmt->bind_param("iis", $trainerId, $receiverId, $text);
            $stmt->execute();
            $stmt->close();
            
            // Notify User of New Message
            $notifMsg = "You have a new message from Coach " . $trainerName;
            $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'new_message', ?)";
            $nStmt = $conn->prepare($notifSql);
            $nStmt->bind_param("is", $receiverId, $notifMsg);
            $nStmt->execute();
            $nStmt->close();
        }
        $chkStmt->close();
    }
    header("Location: trainer_messages.php?chat_with=" . $receiverId);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - FitNova Trainer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Professional Color Palette matching Free User */
            --primary-color: #0F2C59;
            /* Deep Navy Blue */
            --secondary-color: #DAC0A3;
            /* Warm Beige/Champagne */
            --accent-color: #E63946;
            /* Professional Red */
            --bg-color: #F8F9FA;
            --sidebar-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6C757D;
            --border-color: #E9ECEF;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar Styles */
        .sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; height: 100vh; flex-shrink: 0; }
        .sidebar-brand {
            padding: 30px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 24px;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo span {
            color: var(--secondary-color);
            font-size: 10px;
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
            font-weight: 700;
        }
        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 30px;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            border-left: 3px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            color: var(--primary-color);
            background-color: rgba(15, 44, 89, 0.05);
        }

        .menu-item.active {
            border-left-color: var(--primary-color);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .user-profile-preview {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-sm {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .user-info-sm h4 {
            font-size: 14px;
            margin-bottom: 2px;
            color: var(--text-color);
        }

        .user-info-sm p {
            font-size: 11px;
            color: var(--text-light);
            text-transform: uppercase;
        }

        /* Messaging Layout */
        .messaging-container { flex: 1; display: flex; background: white; margin: 20px; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }

        /* Contacts List */
        .contacts-panel { width: 350px; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; }
        .contacts-header { padding: 25px; border-bottom: 1px solid var(--border-color); }
        .contacts-header h2 { font-family: 'Outfit', sans-serif; font-size: 22px; color: #1e293b; }
        .search-contacts { margin-top: 15px; position: relative; }
        .search-contacts input { width: 100%; padding: 10px 15px 10px 35px; border-radius: 10px; border: 1px solid var(--border-color); background: #f8fafc; font-size: 14px; }
        .search-contacts i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 12px; }

        .contacts-list { flex: 1; overflow-y: auto; }
        .contact-item { padding: 15px 25px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: var(--transition); border-bottom: 1px solid #f1f5f9; text-decoration: none; }
        .contact-item:hover { background: #f8fafc; }
        .contact-item.active { background: #eef2ff; border-right: 3px solid var(--primary-color); }
        .contact-avatar { width: 45px; height: 45px; border-radius: 12px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--primary-color); position: relative; }
        .status-dot { width: 10px; height: 10px; background: #10b981; border: 2px solid white; border-radius: 50%; position: absolute; bottom: -2px; right: -2px; }
        .contact-info h4 { font-size: 15px; color: #1e293b; }
        .contact-info p { font-size: 12px; color: var(--text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }

        /* Chat Window */
        .chat-panel { flex: 1; display: flex; flex-direction: column; background: #fdfdfe; }
        .chat-header { padding: 20px 30px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: white; }
        .chat-user-info { display: flex; align-items: center; gap: 15px; }
        .chat-user-info h3 { font-size: 18px; color: #1e293b; }
        .chat-user-info span { font-size: 12px; color: #10b981; font-weight: 600; }

        .chat-messages { flex: 1; padding: 30px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; background: #f8fafc; }
        .message-bubble { max-width: 70%; padding: 12px 18px; border-radius: 15px; font-size: 14px; line-height: 1.5; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .msg-received { background: white; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 2px; border: 1px solid var(--border-color); }
        .msg-sent { background: var(--primary-color); color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
        .msg-time { font-size: 10px; margin-top: 5px; opacity: 0.7; text-align: right; }

        .chat-input-area { padding: 25px; border-top: 1px solid var(--border-color); background: white; }
        .chat-input-wrapper { display: flex; gap: 15px; background: #f8fafc; border: 1px solid var(--border-color); padding: 10px; border-radius: 15px; }
        .chat-input-wrapper textarea { flex: 1; border: none; background: transparent; padding: 10px; resize: none; font-family: inherit; font-size: 14px; max-height: 100px; }
        .chat-input-wrapper textarea:focus { outline: none; }
        .send-btn { width: 45px; height: 45px; border-radius: 12px; background: var(--primary-color); color: white; border: none; cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .send-btn:hover { background: var(--primary-dark); transform: scale(1.05); }

        .empty-chat { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-light); padding: 40px; text-align: center; }
        .empty-chat i { font-size: 60px; margin-bottom: 20px; opacity: 0.2; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); position: absolute; }
            .contacts-panel { width: 100%; }
            .chat-panel { display: none; }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-logo">
                <i class="fas fa-dumbbell"></i> FitNova <span>TRAINER</span>
            </a>
        </div>

        <nav class="sidebar-menu">
            <a href="trainer_dashboard.php" class="menu-item">
                <i class="fas fa-home"></i> Overview
            </a>
            <a href="trainer_clients.php" class="menu-item">
                <i class="fas fa-users"></i> My Clients
            </a>
            <a href="trainer_schedule.php" class="menu-item">
                <i class="fas fa-calendar-alt"></i> Schedule
            </a>
            <a href="trainer_workouts.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i> Workout Plans
            </a>
            <a href="trainer_diets.php" class="menu-item">
                <i class="fas fa-utensils"></i> Diet Plans
            </a>
            <a href="trainer_performance.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Performance
            </a>
            <a href="trainer_messages.php" class="menu-item active">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="client_profile_setup.php" class="menu-item">
                <i class="fas fa-user-circle"></i> Profile
            </a>
        </nav>

        <div class="user-profile-preview">
            <div class="user-avatar-sm"><?php echo $trainerInitials; ?></div>
            <div class="user-info-sm">
                <h4><?php echo htmlspecialchars($trainerName); ?></h4>
                <p>Expert Trainer</p>
            </div>
            <a href="logout.php" style="margin-left: auto; color: var(--text-light);"><i
                    class="fas fa-sign-out-alt fa-flip-horizontal"></i></a>
        </div>
    </aside>

    <div class="messaging-container">
        <!-- Contacts Panel -->
        <div class="contacts-panel">
            <div class="contacts-header">
                <h2>Message Hub</h2>
                <div class="search-contacts">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search conversations...">
                </div>
            </div>
            <div class="contacts-list">
                <?php if (empty($contacts)): ?>
                    <p style="padding: 20px; text-align: center; color: var(--text-light); font-size: 14px;">No conversations yet.</p>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): 
                        $init = strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1));
                        $isActive = ($contact['user_id'] == $selectedContactId) ? 'active' : '';
                    ?>
                    <a href="trainer_messages.php?chat_with=<?php echo $contact['user_id']; ?>" class="contact-item <?php echo $isActive; ?>">
                        <div class="contact-avatar"><?php echo $init; ?><div class="status-dot"></div></div>
                        <div class="contact-info">
                            <h4><?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></h4>
                            <p><?php echo ucfirst($contact['role']); ?> Member</p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Panel -->
        <div class="chat-panel">
            <?php if ($selectedContactId): 
                $currContact = null;
                foreach($contacts as $c) if($c['user_id'] == $selectedContactId) $currContact = $c;
            ?>
                <div class="chat-header">
                    <div class="chat-user-info">
                        <div class="contact-avatar" style="width: 40px; height: 40px; font-size: 14px;"><?php echo strtoupper(substr($currContact['first_name'] ?? 'U',0,1).substr($currContact['last_name'] ?? 'S',0,1)); ?></div>
                        <div>
                            <h3><?php echo htmlspecialchars(($currContact['first_name'] ?? 'User') . ' ' . ($currContact['last_name'] ?? '')); ?></h3>
                            <span>Online</span>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <i class="fas fa-phone-alt" style="margin-right: 20px; color: var(--text-light); cursor: pointer;"></i>
                        <i class="fas fa-video" style="color: var(--text-light); cursor: pointer;"></i>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php if (empty($chatMessages)): ?>
                        <div class="empty-chat"><p>Start a new conversation with your client.</p></div>
                    <?php else: ?>
                        <?php foreach($chatMessages as $msg): 
                            $isSent = ($msg['sender_id'] == $trainerId);
                        ?>
                            <div class="message-bubble <?php echo $isSent ? 'msg-sent' : 'msg-received'; ?>">
                                <?php echo htmlspecialchars($msg['message_text']); ?>
                                <div class="msg-time"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="chat-input-area">
                    <form action="trainer_messages.php" method="POST">
                        <input type="hidden" name="receiver_id" value="<?php echo $selectedContactId; ?>">
                        <div class="chat-input-wrapper">
                            <textarea name="message_text" placeholder="Type your message here..." rows="1" required></textarea>
                            <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <h3>Your Inbox</h3>
                    <p>Select a client or teammate from the list to start messaging.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom of chat
        const chatBox = document.getElementById('chatMessages');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

        // Auto-resize textarea
        const textarea = document.querySelector('.chat-input-wrapper textarea');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }
    </script>
</body>

</html>
