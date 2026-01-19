<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    header("Location: login.php");
    exit();
}

require "db_connect.php";
$trainerId = $_SESSION['user_id'];
$trainerName = $_SESSION['user_name'];
$trainerInitials = strtoupper(substr($trainerName, 0, 1) . substr(explode(' ', $trainerName)[1] ?? '', 0, 1));

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_achievement') {
    $title = $_POST['title'];
    $issuer = $_POST['issuer'];
    $date = $_POST['date_earned'];
    
    $image_url = '';
    if (isset($_FILES['certificate_image']) && $_FILES['certificate_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        $filename = $_FILES['certificate_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $uploadDir = 'uploads/achievements/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFilename = uniqid('cert_') . '.' . $ext;
            $destPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['certificate_image']['tmp_name'], $destPath)) {
                $image_url = $destPath;
            }
        }
    }
    
    if ($title && $image_url) {
        $stmt = $conn->prepare("INSERT INTO trainer_achievements (trainer_id, title, issuer, date_earned, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $trainerId, $title, $issuer, $date, $image_url);
        $stmt->execute();
        $stmt->close();
        $msg = "Achievement added successfully!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM trainer_achievements WHERE id = ? AND trainer_id = ?");
    $stmt->bind_param("ii", $delId, $trainerId);
    $stmt->execute();
    $stmt->close();
    header("Location: trainer_achievements.php");
    exit();
}

// Fetch Achievements
$achievements = [];
$stmt = $conn->prepare("SELECT * FROM trainer_achievements WHERE trainer_id = ? ORDER BY date_earned DESC");
$stmt->bind_param("i", $trainerId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $achievements[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Achievements - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #DAC0A3;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --sidebar-bg: #ffffff;
            --text-color: #333333;
            --border-color: #E9ECEF;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); display: flex; min-height: 100vh; margin: 0; }
        
        .sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.02); }
        .sidebar-brand { padding: 30px; display: flex; align-items: center; border-bottom: 1px solid var(--border-color); }
        .brand-logo { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 24px; color: var(--primary-color); text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .brand-logo span { background: var(--secondary-color); color: var(--primary-color); padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 5px; font-weight: 700; }
        .sidebar-menu { padding: 20px 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: 12px 20px; color: #6C757D; display: flex; align-items: center; gap: 12px; text-decoration: none; font-weight: 500; transition: 0.3s; border-left: 3px solid transparent; }
        .menu-item:hover, .menu-item.active { background: #eef2ff; color: var(--primary-color); border-left-color: var(--primary-color); }
        .user-profile-preview { padding: 20px; border-top: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px; margin-top: auto; }
        .user-avatar-sm { width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        
        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        
        .form-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        
        .btn-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; position: relative; transition: 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .card-img { height: 200px; width: 100%; object-fit: cover; border-bottom: 1px solid #eee; }
        .card-body { padding: 15px; }
        .card-title { font-weight: 700; color: var(--primary-color); margin-bottom: 5px; }
        .card-meta { color: #666; font-size: 13px; margin-bottom: 15px; }
        .btn-delete { color: #dc2626; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>
    <!-- Image Modal -->
    <div id="certModal" style="display: none; position: fixed; z-index: 9999; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9);">
      <span style="position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; transition: 0.3s; cursor: pointer;" onclick="document.getElementById('certModal').style.display='none'">&times;</span>
      <img style="margin: auto; display: block; width: 80%; max-width: 700px; max-height: 80vh; object-fit: contain;" id="img01">
      <div id="caption" style="margin: auto; display: block; width: 80%; max-width: 700px; text-align: center; color: #ccc; padding: 10px 0; height: 150px;"></div>
    </div>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-logo"><i class="fas fa-dumbbell"></i> FitNova <span>TRAINER</span></a>
        </div>
        <nav class="sidebar-menu">
            <a href="trainer_dashboard.php" class="menu-item"><i class="fas fa-home"></i> Overview</a>
            <a href="trainer_clients.php" class="menu-item"><i class="fas fa-users"></i> My Clients</a>
            <a href="trainer_schedule.php" class="menu-item"><i class="fas fa-calendar-alt"></i> Schedule</a>
            <a href="trainer_workouts.php" class="menu-item"><i class="fas fa-clipboard-list"></i> Workout Plans</a>
            <a href="trainer_diets.php" class="menu-item"><i class="fas fa-utensils"></i> Diet Plans</a>
            <a href="trainer_achievements.php" class="menu-item active"><i class="fas fa-medal"></i> Achievements</a>
            <a href="trainer_performance.php" class="menu-item"><i class="fas fa-chart-line"></i> Performance</a>
            <a href="trainer_messages.php" class="menu-item"><i class="fas fa-envelope"></i> Messages</a>
            <a href="client_profile_setup.php" class="menu-item"><i class="fas fa-user-circle"></i> Profile</a>
        </nav>
        <div class="user-profile-preview" style="padding: 20px; border-top: 1px solid #E9ECEF; display: flex; align-items: center; gap: 12px; margin-top: auto; background: #fff;">
            <div style="width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?php echo $trainerInitials; ?>
            </div>
            <div>
                <h4 style="font-size:15px; margin:0; color:#333; font-weight:600;"><?php echo htmlspecialchars($trainerName); ?></h4>
                <p style="font-size:11px; margin:0; color:#64748b; text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Expert Trainer</p>
            </div>
            <a href="logout.php" title="Logout" style="margin-left: auto; color: #64748b; text-decoration: none; font-size: 16px; transition: 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="font-family:'Outfit', sans-serif;">Manage Achievements 🏆</h2>
        </div>

        <?php if(isset($msg)) echo "<div style='background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px;'>$msg</div>"; ?>

        <div class="form-card">
            <h3 style="margin-bottom:20px;">Upload New Certificate / Badge</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_achievement">
                <div class="form-row">
                    <div class="form-group">
                        <label>Certificate Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Certified Personal Trainer" required>
                    </div>
                    <div class="form-group">
                        <label>Issuing Organization</label>
                        <input type="text" name="issuer" class="form-control" placeholder="e.g. NASM, ACE">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date Earned</label>
                        <input type="date" name="date_earned" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Certificate Image</label>
                        <input type="file" name="certificate_image" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Upload Achievement</button>
            </form>
        </div>

        <h3 style="margin-bottom:20px;">My Badges & Certificates (<?php echo count($achievements); ?>)</h3>
        
        <?php if(empty($achievements)): ?>
            <p style="color:#666; font-style:italic;">No achievements added yet. Upload your first certificate!</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach($achievements as $ach): ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($ach['image_url']); ?>" class="card-img" alt="<?php echo htmlspecialchars($ach['title']); ?>" 
                         style="cursor: pointer;"
                         onclick="showImage('<?php echo htmlspecialchars($ach['image_url']); ?>', '<?php echo htmlspecialchars($ach['title']); ?>')">
                    <div class="card-body">
                        <div class="card-title"><?php echo htmlspecialchars($ach['title']); ?></div>
                        <div class="card-meta">
                            <?php if($ach['issuer']) echo "Issued by " . htmlspecialchars($ach['issuer']) . " • "; ?>
                            <?php echo date('M Y', strtotime($ach['date_earned'])); ?>
                        </div>
                        <a href="?delete=<?php echo $ach['id']; ?>" class="btn-delete" onclick="return confirm('Delete this achievement?')"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <script>
        function showImage(src, title) {
            var modal = document.getElementById("certModal");
            var modalImg = document.getElementById("img01");
            var captionText = document.getElementById("caption");
            modal.style.display = "block";
            modalImg.src = src;
            captionText.innerHTML = title;
        }

        // Close the modal when clicking anywhere outside the image
        window.onclick = function(event) {
            var modal = document.getElementById("certModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
