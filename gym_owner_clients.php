<?php
session_start();
// Security Check for Offline Gym Owner
$allowed_email = 'ashakayaplackal@gmail.com';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email']) || strtolower($_SESSION['user_email']) !== strtolower($allowed_email)) {
    header("Location: login.php?error=unauthorized_gym_owner");
    exit();
}

require 'db_connect.php';

// Fetch Clients
$clients = $conn->query("SELECT u.user_id, u.first_name, u.last_name, u.email, u.gym_membership_status, u.profile_picture 
                         FROM users u 
                         WHERE u.gym_membership_status = 'active' 
                         ORDER BY u.first_name ASC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Clients - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0F2C59; --accent: #4FACFE; --bg: #F8F9FA; --white: #FFF; --shadow: 0 4px 15px rgba(0,0,0,0.05); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg); color: #333; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #0F2C59; height: 100vh; position: fixed; padding: 30px 20px; display: flex; flex-direction: column; box-shadow: 4px 0 15px rgba(0,0,0,0.1); color: #fff; z-index: 100; }
        .logo { font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 40px; display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 0 10px; }
        .menu-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.5); font-weight: 700; margin-bottom: 15px; padding-left: 15px; }
        .menu-item { display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px; font-weight: 500; transition: all 0.2s ease; font-size: 0.95rem; }
        .menu-item:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateX(2px); }
        .menu-item.active { background: #fff; color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-weight: 700; }
        .menu-item.active i { opacity: 1; color: var(--primary); }
        
        .user-profile { margin-top: auto; padding: 20px 15px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; border: 1px solid rgba(255,255,255,0.1); }
        .user-info div:first-child { font-weight: 600; font-size: 0.9rem; color: #fff; }
        .user-info div:last-child { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-bottom: 4px; }
        .user-info a { color: var(--accent); font-size: 0.75rem; text-decoration: none; transition: 0.2s; }
        .user-info a:hover { text-decoration: underline; color: #fff; }
        
        .main-content { margin-left: 260px; padding: 40px; width: 100%; min-height: 100vh; }
        .card { background: var(--white); padding: 25px; border-radius: 16px; box-shadow: var(--shadow); margin-bottom: 30px; }
        .card h2 { font-size: 1.2rem; margin-bottom: 20px; color: var(--primary); border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: 600; color: #888; font-size: 0.85rem; text-transform: uppercase; }
        .status-badge { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; background: #d1fae5; color: #065f46; }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 350px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .close-modal { position: absolute; top: 15px; right: 20px; cursor: pointer; font-size: 1.5rem; color: #888; }
        .btn-view { background: var(--accent); color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo"><i class="fas fa-dumbbell"></i> FitNova Gym</a>
        
        <div class="menu-title">Main Menu</div>
        <a href="gym_owner_dashboard.php" class="menu-item"><i class="fas fa-chart-pie"></i> Overview</a>
        <a href="gym_owner_clients.php" class="menu-item active"><i class="fas fa-users"></i> Clients</a>
        <a href="home.php" class="menu-item"><i class="fas fa-home"></i> Home</a>
        <a href="gym.php" class="menu-item"><i class="fas fa-building"></i> Offline Gym Page</a>
        <a href="fitshop.php" class="menu-item"><i class="fas fa-shopping-bag"></i> Fitshop</a>
        <a href="gym_owner_trainers.php" class="menu-item"><i class="fas fa-users"></i> Trainers</a>
        
        <div class="user-profile">
            <div class="user-avatar">AK</div>
            <div class="user-info">
                <div>Asha Kayaplackal</div>
                <div style="margin-bottom: 2px;">Offline Owner</div>
                <a href="logout.php" style="color: var(--accent); font-size: 0.8rem; font-weight: 600; text-decoration: none;">Sign Out</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 style="color: var(--primary); margin-bottom: 10px;">Offline Gym Members</h1>
        <p style="color: #666; margin-bottom: 30px;">Manage users with active offline gym access.</p>

        <div class="card">
            <h2><i class="fas fa-users"></i> Active Members List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clients->num_rows > 0): ?>
                        <?php while($client = $clients->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 35px; height: 35px; border-radius: 50%; background: #eee; display: flex; justify-content: center; align-items: center; font-weight: bold; color: #555;">
                                        <?php echo strtoupper(substr($client['first_name'], 0, 1)); ?>
                                    </div>
                                    <span style="font-weight: 500;"><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><span class="status-badge">Active</span></td>
                            <td>
                                <button class="btn-view" onclick="showQR(<?php echo $client['user_id']; ?>, '<?php echo $client['first_name']; ?>')">
                                    <i class="fas fa-qrcode"></i> View QR
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #888;">No active offline members found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- QR Modal -->
    <div id="qr-modal" class="modal">
        <div class="modal-content" style="position: relative;">
            <span class="close-modal" onclick="document.getElementById('qr-modal').style.display='none'">&times;</span>
            <h3 style="color: var(--primary); margin-bottom: 15px;">Member Access QR</h3>
            <div id="qr-container" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                <img id="qr-image" src="" alt="QR Code" style="width: 150px; height: 150px;">
            </div>
            <p id="qr-user-name" style="font-weight: 600; color: #333; margin-bottom: 5px;"></p>
            <p style="font-size: 0.85rem; color: #888;">Scan for Entry</p>
        </div>
    </div>

    <script>
        function showQR(userId, userName) {
            const modal = document.getElementById('qr-modal');
            const qrImg = document.getElementById('qr-image');
            const nameText = document.getElementById('qr-user-name');
            
            // Generate QR for User ID (JSON format for extensibility)
            const qrData = JSON.stringify({ user_id: userId, type: 'offline_access' });
            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrData)}`;
            
            nameText.textContent = userName;
            modal.style.display = 'flex';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('qr-modal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
