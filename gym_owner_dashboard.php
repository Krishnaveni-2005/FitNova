<?php
session_start();
// Security Check for Offline Gym Owner
$allowed_email = 'ashakayaplackal@gmail.com';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email']) || strtolower($_SESSION['user_email']) !== strtolower($allowed_email)) {
    header("Location: login.php?error=unauthorized_gym_owner");
    exit();
}

require 'db_connect.php';

// Handle Updates
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Settings
    if (isset($_POST['update_settings'])) {
        $open = $_POST['gym_open_time'];
        $close = $_POST['gym_close_time'];
        $status = $_POST['gym_status'];
        
        $conn->query("UPDATE gym_settings SET setting_value='$open' WHERE setting_key='gym_open_time'");
        $conn->query("UPDATE gym_settings SET setting_value='$close' WHERE setting_key='gym_close_time'");
        $conn->query("UPDATE gym_settings SET setting_value='$status' WHERE setting_key='gym_status'");
        $message = "Gym settings updated!";
    }
    
    // Update Equipment
    if (isset($_POST['update_equipment'])) {
        $id = $_POST['equip_id'];
        $avail = $_POST['available_units'];
        $status = $_POST['status']; // available, busy, unavailable
        
        $stmt = $conn->prepare("UPDATE gym_equipment SET available_units=?, status=? WHERE id=?");
        $stmt->bind_param("isi", $avail, $status, $id);
        if ($stmt->execute()) {
            $message = "Equipment updated!";
        } else {
            $message = "Error updating equipment.";
        }
    }

    // Add New Equipment
    if (isset($_POST['add_equipment'])) {
        $name = $_POST['equip_name'];
        $total = $_POST['total_units'];
        // Default available = total, default status available
        $avail = $total;
        $status = 'available';
        
        // Initial simple mapping based on name
        $icon = 'fas fa-dumbbell'; // Default
        $n = strtolower($name);
        
        if (strpos($n, 'run') !== false || strpos($n, 'tread') !== false || strpos($n, 'cardio') !== false) $icon = 'fas fa-running';
        if (strpos($n, 'cycle') !== false || strpos($n, 'bike') !== false || strpos($n, 'spin') !== false) $icon = 'fas fa-bicycle';
        if (strpos($n, 'ball') !== false || strpos($n, 'sphere') !== false) $icon = 'fas fa-volleyball-ball';
        if (strpos($n, 'mat') !== false || strpos($n, 'yoga') !== false) $icon = 'fas fa-scroll';
        if (strpos($n, 'bar') !== false || strpos($n, 'rod') !== false) $icon = 'fas fa-grip-lines';
        if (strpos($n, 'bench') !== false || strpos($n, 'press') !== false) $icon = 'fas fa-chair';
        if (strpos($n, 'weight') !== false || strpos($n, 'plate') !== false) $icon = 'fas fa-weight-hanging';
        if (strpos($n, 'box') !== false || strpos($n, 'step') !== false) $icon = 'fas fa-cube';
        
        // Check if exists
        $check = $conn->query("SELECT id FROM gym_equipment WHERE name='$name'");
        if ($check->num_rows > 0) {
             $message = "Equipment already exists!";
        } else {
             $color_class = 'success'; // Default to green/success for new available equipment
             $stmt = $conn->prepare("INSERT INTO gym_equipment (name, total_units, available_units, status, icon, color_class) VALUES (?, ?, ?, ?, ?, ?)");
             $stmt->bind_param("siisss", $name, $total, $avail, $status, $icon, $color_class);
             if ($stmt->execute()) {
                 $message = "New equipment added!";
             } else {
                 $message = "Error adding equipment: " . $conn->error;
             }
        }
    }

    // Delete Equipment
    if (isset($_POST['delete_equipment'])) {
        $id = $_POST['equip_id'];
        $stmt = $conn->prepare("DELETE FROM gym_equipment WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Equipment removed!";
        } else {
            $message = "Error removing equipment.";
        }
    }
}

// Fetch Data
$settings = [];
$res = $conn->query("SELECT * FROM gym_settings");
while($row = $res->fetch_assoc()) $settings[$row['setting_key']] = $row['setting_value'];

$equipment = $conn->query("SELECT * FROM gym_equipment");
$trainers = $conn->query("SELECT u.first_name, u.last_name, u.trainer_specialization, ta.check_in_time, ta.status 
                          FROM trainer_attendance ta 
                          JOIN users u ON ta.trainer_id = u.user_id 
                          WHERE DATE(ta.check_in_time) = CURDATE() ORDER BY ta.check_in_time DESC");

// Active Trainers Count
$activeTrainersCount = $conn->query("SELECT COUNT(DISTINCT trainer_id) as count FROM trainer_attendance WHERE status = 'checked_in'")->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Owner Dashboard - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0F2C59; --accent: #4FACFE; --bg: #F8F9FA; --white: #FFF; --shadow: 0 4px 15px rgba(0,0,0,0.05); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg); color: #333; display: flex; }
        
        /* Sidebar - Solid Dark Blue Background */
        .sidebar { 
            width: 260px; 
            background: #0F2C59; 
            height: 100vh; 
            position: fixed; 
            padding: 30px 20px; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 4px 0 15px rgba(0,0,0,0.1); 
            color: #fff;
            z-index: 100;
        }
        
        .logo { 
            font-size: 1.5rem; 
            font-weight: 800; 
            color: #fff; 
            margin-bottom: 40px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            text-decoration: none; 
            padding: 0 10px;
        }
        .logo i { color: var(--accent); font-size: 1.4rem; }
        
        .menu-title { 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            color: rgba(255,255,255,0.5); 
            font-weight: 700; 
            margin-bottom: 15px; 
            padding-left: 15px;
        }
        
        .menu-item { 
            display: flex; 
            align-items: center; 
            padding: 12px 15px; 
            margin-bottom: 8px; 
            color: rgba(255,255,255,0.7); 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 500; 
            transition: all 0.2s ease; 
            font-size: 0.95rem; 
        }
        .menu-item i { width: 25px; font-size: 1.1rem; margin-right: 10px; opacity: 0.8; transition: 0.2s; }
        
        .menu-item:hover { 
            background: rgba(255,255,255,0.1); 
            color: #fff; 
            transform: translateX(2px); 
        }
        
        .menu-item.active { 
            background: #fff; 
            color: var(--primary); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            font-weight: 700;
        }
        .menu-item.active i { opacity: 1; color: var(--primary); }
        
        .user-profile { 
            margin-top: auto; 
            padding: 20px 15px; 
            border-top: 1px solid rgba(255,255,255,0.1); 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }
        .user-avatar { 
            width: 40px; 
            height: 40px; 
            background: rgba(255,255,255,0.1); 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 700; 
            color: #fff; 
            border: 1px solid rgba(255,255,255,0.1);
        }
        .user-info div:first-child { font-weight: 600; font-size: 0.9rem; color: #fff; }
        .user-info div:last-child { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-bottom: 4px; }
        .user-info a { color: var(--accent); font-size: 0.75rem; text-decoration: none; transition: 0.2s; }
        .user-info a:hover { text-decoration: underline; color: #fff; }

        /* Mobile Responsive Sidebar */
        @media(max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; align-items: center; }
            .logo span, .menu-item span, .menu-title, .user-info { display: none; }
            .logo i { margin: 0; font-size: 1.8rem; }
            .menu-item { justify-content: center; padding: 12px; }
            .menu-item i { margin: 0; font-size: 1.3rem; }
            .main-content { margin-left: 70px; padding: 20px; }
            .user-profile { padding: 10px; justify-content: center; background: transparent; }
        }


        /* Main */
        .main-content { margin-left: 260px; padding: 40px; width: 100%; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        h1 { font-size: 2rem; color: var(--primary); }
        
        .card { background: var(--white); padding: 25px; border-radius: 16px; box-shadow: var(--shadow); margin-bottom: 30px; }
        .card h2 { font-size: 1.2rem; margin-bottom: 20px; color: var(--primary); border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        /* Forms */
        .form-row { display: flex; gap: 20px; align-items: flex-end; }
        .form-group { flex: 1; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
        button { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        button:hover { opacity: 0.9; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: 600; color: #888; font-size: 0.85rem; text-transform: uppercase; }
        .status-badge { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-available { background: #d1fae5; color: #065f46; }
        .status-busy { background: #fef3c7; color: #92400e; }
        .status-unavailable { background: #fee2e2; color: #991b1b; }
        
        .alert { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        
        .edit-form { display: flex; gap: 10px; align-items: center; }
        .edit-form input { width: 80px; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h3 { margin: 0; color: var(--primary); }
        .close-modal { cursor: pointer; font-size: 1.5rem; color: #888; }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="#" class="logo"><i class="fas fa-dumbbell"></i> FitNova Gym</a>
        
        <div class="menu-title">Main Menu</div>
        <a href="gym_owner_dashboard.php" class="menu-item"><i class="fas fa-chart-pie"></i> Overview</a>
        <a href="gym_owner_clients.php" class="menu-item"><i class="fas fa-users"></i> Clients</a>
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

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Gym Owner Dashboard</h1>
                <p>Manage offline gym status, equipment, and trainers.</p>
            </div>
            <div style="text-align: right;">
                <div id="live-time" style="font-size: 1.5rem; font-weight: 800; color: var(--accent);"><?php echo date('h:i A'); ?></div>
                <div id="live-date" style="font-size: 0.9rem; color: #888;"><?php echo date('l, F j, Y'); ?></div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- 0. Gym Overview Stats -->
        <div class="card" style="margin-bottom: 30px; background: transparent; box-shadow: none; padding: 0; border: none;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <!-- Status Card -->
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid <?php echo ($settings['gym_status'] == 'open') ? '#22c55e' : '#ef4444'; ?>;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #888; text-transform: uppercase;">Gym Status</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #333; margin-top: 5px;">
                        <?php echo ucfirst($settings['gym_status']); ?>
                    </div>
                </div>

                <!-- Equipment Card -->
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid var(--accent);">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #888; text-transform: uppercase;">Total Equipment</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #333; margin-top: 5px;">
                        <?php echo $equipment->num_rows; ?> <span style="font-size: 0.9rem; font-weight: 500; color: #888;">Items</span>
                    </div>
                    <?php $equipment->data_seek(0); // Reset pointer for inventory table ?>
                </div>

                <!-- Trainers Card -->
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #0F2C59;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #888; text-transform: uppercase;">Trainers On-Duty</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #333; margin-top: 5px;">
                        <?php echo $activeTrainersCount; ?> <span style="font-size: 0.9rem; font-weight: 500; color: #888;">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. Gym Operating Status -->
        <div class="card">
            <h2><i class="fas fa-clock"></i> Operating Hours & Status</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Open Time</label>
                        <input type="text" name="gym_open_time" value="<?php echo $settings['gym_open_time'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Close Time</label>
                        <input type="text" name="gym_close_time" value="<?php echo $settings['gym_close_time'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Current Status</label>
                        <select name="gym_status">
                            <option value="open" <?php if(($settings['gym_status']??'') == 'open') echo 'selected'; ?>>Open</option>
                            <option value="closed" <?php if(($settings['gym_status']??'') == 'closed') echo 'selected'; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 0;">
                        <button type="submit" name="update_settings">Save Settings</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 2. Manage Equipment -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <h2 style="margin: 0; border: none; padding: 0;"><i class="fas fa-dumbbell"></i> Manage Equipment Inventory</h2>
                <button onclick="document.getElementById('add-equip-modal').style.display='flex'" style="background: var(--primary); font-size: 0.9rem;"><i class="fas fa-plus"></i> Add Equipment</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Equipment Name</th>
                        <th>Total Units</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($equipment->num_rows > 0): ?>
                        <?php while($row = $equipment->fetch_assoc()): 
                            $formId = "form-" . $row['id'];
                        ?>
                        <tr>
                            <td><i class="<?php echo $row['icon']; ?>" style="color: var(--accent); margin-right:8px;"></i> <?php echo $row['name']; ?></td>
                            <td><?php echo $row['total_units']; ?></td>
                            <td>
                                <input type="number" form="<?php echo $formId; ?>" name="available_units" value="<?php echo $row['available_units']; ?>" min="0" max="<?php echo $row['total_units']; ?>" disabled style="background: #f9f9f9; border: 1px solid transparent; width: 80px; padding: 8px; border-radius: 6px;">
                            </td>
                            <td>
                                <select form="<?php echo $formId; ?>" name="status" disabled style="background: #f9f9f9; border: 1px solid transparent; padding: 8px; border-radius: 6px;">
                                    <option value="available" <?php if($row['status']=='available') echo 'selected'; ?>>Available</option>
                                    <option value="busy" <?php if($row['status']=='busy') echo 'selected'; ?>>Busy</option>
                                    <option value="unavailable" <?php if($row['status']=='unavailable') echo 'selected'; ?>>Unavailable</option>
                                </select>
                            </td>
                            <td>
                                <!-- Actions -->
                                <button type="button" class="btn-edit" onclick="enableEdit(this)" style="padding: 6px 12px; font-size: 0.8rem; background: var(--primary); color: white; border: none; border-radius: 4px; cursor: pointer;">Edit</button>
                                <button type="button" class="btn-save" onclick="saveEquipment(this, <?php echo $row['id']; ?>)" style="padding: 6px 12px; font-size: 0.8rem; background: #22c55e; color: white; border: none; border-radius: 4px; cursor: pointer; display: none;">Save</button>
                                
                                <form method="POST" style="display:inline; margin-left: 5px;" onsubmit="return confirm('Remove this equipment?');">
                                    <input type="hidden" name="equip_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_equipment" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1rem;" title="Remove"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No equipment found. <a href="setup_gym_db.php">Run Setup</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 3. Pending Trainer Approvals -->
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> Pending Trainer Approvals</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Applied On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="pending-trainers-list">
                    <?php
                    // Fetch Pending Trainers
                    $pending_trainers = $conn->query("SELECT user_id, first_name, last_name, email, created_at FROM users WHERE role='trainer' AND account_status='pending' AND trainer_type='offline' ORDER BY created_at DESC");
                    
                    if ($pending_trainers && $pending_trainers->num_rows > 0):
                        while($pt = $pending_trainers->fetch_assoc()):
                    ?>
                        <tr id="pt-row-<?php echo $pt['user_id']; ?>">
                            <td><?php echo htmlspecialchars($pt['first_name'] . ' ' . $pt['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($pt['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($pt['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <button onclick="handleTrainerAction(<?php echo $pt['user_id']; ?>, 'approve')" style="background: #22c55e; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Approve</button>
                                    <button onclick="handleTrainerAction(<?php echo $pt['user_id']; ?>, 'reject')" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Reject</button>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr><td colspan="4" style="text-align: center; color: #888; padding: 20px;">No pending trainer requests.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function updateTime() {
            const now = new Date();
            
            // Format Time (HH:MM AM/PM)
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            const timeString = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
            
            // Format Date (Day, Month DD, YYYY)
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('en-US', options);
            
            document.getElementById('live-time').textContent = timeString;
            document.getElementById('live-date').textContent = dateString;
        }

        // Update immediately and then every second
        updateTime();
        setInterval(updateTime, 1000);
        // Form Edit Toggle
        function enableEdit(btn) {
            const row = btn.closest('tr');
            if (!row) return;
            
            // Enable Inputs
            const inputs = row.querySelectorAll('input:not([type=hidden]), select');
            inputs.forEach(input => {
                input.removeAttribute('disabled');
                input.style.border = "1px solid #ddd";
                input.style.background = "#fff";
            });
            
            // Toggle Buttons
            row.querySelector('.btn-edit').style.display = 'none';
            row.querySelector('.btn-save').style.display = 'inline-block';
        }

        // AJAX Save Function
        function saveEquipment(btn, id) {
            const row = btn.closest('tr');
            const availInput = row.querySelector('input[name="available_units"]');
            const statusInput = row.querySelector('select[name="status"]');
            
            const data = {
                type: 'equipment',
                id: id,
                available_units: availInput.value,
                status: statusInput.value
            };

            // Visual feedback
            btn.textContent = 'Saving...';
            btn.disabled = true;

            fetch('gym_owner_updates.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // Success State
                    btn.textContent = 'Saved!';
                    btn.style.background = 'var(--primary)';
                    setTimeout(() => {
                        // Reset UI
                        btn.style.display = 'none';
                        row.querySelector('.btn-edit').style.display = 'inline-block';
                        btn.textContent = 'Save';
                        btn.disabled = false;
                        btn.style.background = '#22c55e';
                        
                        // Disable Inputs
                        [availInput, statusInput].forEach(el => {
                            el.disabled = true;
                            el.style.background = '#f9f9f9';
                            el.style.borderColor = 'transparent';
                        });
                    }, 1000);
                } else {
                    alert('Error updating: ' + res.message);
                    btn.textContent = 'Save';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                btn.textContent = 'Retry';
                btn.disabled = false;
            });
        }
        // Trainer Approval Action
        function handleTrainerAction(id, action) {
            if (!confirm(`Are you sure you want to ${action} this trainer?`)) return;

            fetch('admin_trainer_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ trainer_id: id, action: action })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove row from table
                    const row = document.getElementById(`pt-row-${id}`);
                    if (row) {
                        row.style.background = action === 'approve' ? '#dcfce7' : '#fee2e2';
                        setTimeout(() => row.remove(), 1000);
                    }
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
        }
    </script>
    <!-- Add Equipment Modal -->
    <div id="add-equip-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Equipment</h3>
                <span class="close-modal" onclick="document.getElementById('add-equip-modal').style.display='none'">&times;</span>
            </div>
            <form method="POST">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Equipment Name</label>
                    <input type="text" name="equip_name" required placeholder="e.g. Treadmill" style="width: 100%;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Total Units</label>
                    <input type="number" name="total_units" required min="1" value="1" style="width: 100%;">
                </div>
                <div style="text-align: right;">
                    <button type="button" onclick="document.getElementById('add-equip-modal').style.display='none'" style="background: #ccc; margin-right: 10px; color: #333;">Cancel</button>
                    <button type="submit" name="add_equipment">Add Equipment</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
