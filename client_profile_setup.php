<?php
session_start();
require "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// 1. Fetch Existing Profile Data (This ensures they ONLY see THEIR data)
$existingProfile = null;
$fetchSql = "SELECT * FROM client_profiles WHERE user_id = ?";
$fetchStmt = $conn->prepare($fetchSql);
$fetchStmt->bind_param("i", $userId);
$fetchStmt->execute();
$profileResult = $fetchStmt->get_result();

if ($profileResult->num_rows > 0) {
    $existingProfile = $profileResult->fetch_assoc();
}

// 2. Handle Form Submission (Save/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $target_weight = $_POST['target_weight'];
    $goal = $_POST['goal'];
    $activity = $_POST['activity'];
    $injuries = $_POST['injuries'];
    $medical = $_POST['medical'];
    $allergies = $_POST['allergies'];
    $sleep = $_POST['sleep'];
    $diet = $_POST['diet'];
    $water = $_POST['water'];
    $workout_days = $_POST['workout_days'];
    $equipment = $_POST['equipment'];

    if ($existingProfile) {
        // UPDATE existing record
        $sql = "UPDATE client_profiles SET gender=?, dob=?, height_cm=?, weight_kg=?, target_weight_kg=?, primary_goal=?, activity_level=?, injuries=?, medical_conditions=?, allergies=?, sleep_hours_avg=?, diet_preference=?, water_intake_liters=?, workout_days_per_week=?, equipment_access=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddssssssisdisi", $gender, $dob, $height, $weight, $target_weight, $goal, $activity, $injuries, $medical, $allergies, $sleep, $diet, $water, $workout_days, $equipment, $userId);
    } else {
        // INSERT new record
        $sql = "INSERT INTO client_profiles (user_id, gender, dob, height_cm, weight_kg, target_weight_kg, primary_goal, activity_level, injuries, medical_conditions, allergies, sleep_hours_avg, diet_preference, water_intake_liters, workout_days_per_week, equipment_access) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issddssssssisdis", $userId, $gender, $dob, $height, $weight, $target_weight, $goal, $activity, $injuries, $medical, $allergies, $sleep, $diet, $water, $workout_days, $equipment);
    }

    if ($stmt->execute()) {
        $_SESSION['profile_complete'] = true;
        header("Location: freeuser_dashboard.php?setup=success");
        exit();
    } else {
        $error = "Error saving profile: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Profile - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0F2C59;
            --primary-light: rgba(15, 44, 89, 0.05);
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --accent: #E63946;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg); color: var(--text); line-height: 1.6; }

        .setup-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        
        .setup-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; }
        .setup-header div h1 { font-family: 'Outfit', sans-serif; font-size: 32px; color: var(--primary); margin-bottom: 10px; }
        .setup-header p { color: var(--text-light); }
        
        .edit-toggle-btn {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .edit-toggle-btn.editing { background: var(--accent); }

        /* Progress Bar */
        .progress-stepper { display: flex; justify-content: space-between; margin-bottom: 40px; position: relative; }
        .progress-stepper::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: var(--border); z-index: 1; }
        .step { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .step-circle { width: 32px; height: 32px; border-radius: 50%; background: var(--card); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; transition: all 0.3s; }
        .step.active .step-circle { background: var(--primary); border-color: var(--primary); color: white; transform: scale(1.1); box-shadow: 0 0 0 5px rgba(15, 44, 89, 0.1); }
        .step.completed .step-circle { background: var(--success); border-color: var(--success); color: white; }
        .step-label { font-size: 12px; font-weight: 600; color: var(--text-light); }
        .step.active .step-label { color: var(--primary); }

        /* Form Card */
        .form-card { background: var(--card); border-radius: 20px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); padding: 40px; min-height: 480px; display: flex; flex-direction: column; }
        
        .form-step { display: none; animation: fadeIn 0.4s ease-out; }
        .form-step.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .step-title { margin-bottom: 30px; }
        .step-title h2 { font-family: 'Outfit', sans-serif; font-size: 24px; margin-bottom: 8px; }
        .step-title p { font-size: 14px; color: var(--text-light); }

        /* Input Grid */
        .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group.full { grid-column: span 2; }
        
        label { display: block; font-size: 13px; font-weight: 600; color: var(--text-light); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        input, select, textarea { width: 100%; padding: 12px 16px; border-radius: 10px; border: 1px solid var(--border); font-family: inherit; font-size: 15px; transition: all 0.2s; background: #fcfdfe; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15, 44, 89, 0.1); background: white; }
        input:disabled, select:disabled, textarea:disabled { background: #f1f5f9; cursor: not-allowed; opacity: 0.8; }

        .radio-group { display: flex; gap: 15px; }
        .radio-box { flex: 1; position: relative; }
        .radio-box input { position: absolute; opacity: 0; }
        .radio-label { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; border: 1px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.2s; font-weight: 600; }
        .radio-box input:checked + .radio-label { border-color: var(--primary); background: var(--primary-light); color: var(--primary); box-shadow: 0 4px 6px rgba(15, 44, 89, 0.05); }

        /* Buttons */
        .form-actions { margin-top: auto; display: flex; justify-content: space-between; padding-top: 30px; border-top: 1px solid #f1f5f9; }
        .btn { padding: 12px 28px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s; border: none; font-size: 15px; display: flex; align-items: center; gap: 10px; }
        .btn-prev { background: #f1f5f9; color: var(--text-light); }
        .btn-prev:hover { background: #e2e8f0; }
        .btn-next { background: var(--primary); color: white; margin-left: auto; }
        .btn-next:hover { background: #0a1f3f; transform: translateY(-1px); }
        .btn-save { background: var(--success); color: white; display: none; }
        .btn-save:hover { background: #059669; }

        @media (max-width: 640px) { .input-grid { grid-template-columns: 1fr; } .form-group.full { grid-column: span 1; } }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <div>
                <h1><?php echo $existingProfile ? 'Your Fitness Profile' : 'Complete Profile'; ?></h1>
                <p>Hello, <?php echo htmlspecialchars($userName); ?>. This data is private to you.</p>
            </div>
            <?php if ($existingProfile): ?>
            <button type="button" class="edit-toggle-btn" id="editToggle">
                <i class="fas fa-edit"></i> <span id="editText">Edit Profile</span>
            </button>
            <?php endif; ?>
        </div>

        <div class="progress-stepper">
            <div class="step active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Physicals</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Goals</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Health</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Lifestyle</div>
            </div>
            <div class="step" data-step="5">
                <div class="step-circle">5</div>
                <div class="step-label">Finish</div>
            </div>
        </div>

        <form id="setupForm" action="client_profile_setup.php" method="POST">
            <div class="form-card">
                <!-- Step 1: Physical Data -->
                <div class="form-step active" id="step-1">
                    <div class="step-title">
                        <h2>Physical Basics</h2>
                        <p>Isolated metabolic calculations based on your private data.</p>
                    </div>
                    <div class="input-grid">
                        <div class="form-group full">
                            <label>Gender Identity</label>
                            <div class="radio-group" id="gender-group">
                                <div class="radio-box">
                                    <input type="radio" name="gender" value="male" id="gender-m" <?php echo ($existingProfile && $existingProfile['gender']=='male') ? 'checked' : 'checked'; ?>>
                                    <label class="radio-label" for="gender-m"><i class="fas fa-mars"></i> Male</label>
                                </div>
                                <div class="radio-box">
                                    <input type="radio" name="gender" value="female" id="gender-f" <?php echo ($existingProfile && $existingProfile['gender']=='female') ? 'checked' : ''; ?>>
                                    <label class="radio-label" for="gender-f"><i class="fas fa-venus"></i> Female</label>
                                </div>
                                <div class="radio-box">
                                    <input type="radio" name="gender" value="other" id="gender-o" <?php echo ($existingProfile && $existingProfile['gender']=='other') ? 'checked' : ''; ?>>
                                    <label class="radio-label" for="gender-o">Other</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" value="<?php echo $existingProfile['dob'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" name="height" value="<?php echo $existingProfile['height_cm'] ?? ''; ?>" placeholder="e.g. 175" required step="0.1">
                        </div>
                        <div class="form-group">
                            <label>Current Weight (kg)</label>
                            <input type="number" name="weight" value="<?php echo $existingProfile['weight_kg'] ?? ''; ?>" placeholder="e.g. 70" required step="0.1">
                        </div>
                        <div class="form-group">
                            <label>Target Weight (kg)</label>
                            <input type="number" name="target_weight" value="<?php echo $existingProfile['target_weight_kg'] ?? ''; ?>" placeholder="e.g. 65" step="0.1">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Goals & Activity -->
                <div class="form-step" id="step-2">
                    <div class="step-title">
                        <h2>Your Ambitions</h2>
                        <p>Your goals are unique to your journey.</p>
                    </div>
                    <div class="form-group">
                        <label>Primary Fitness Goal</label>
                        <select name="goal" required>
                            <option value="weight_loss" <?php echo ($existingProfile && $existingProfile['primary_goal']=='weight_loss') ? 'selected' : ''; ?>>Weight Loss</option>
                            <option value="muscle_gain" <?php echo ($existingProfile && $existingProfile['primary_goal']=='muscle_gain') ? 'selected' : ''; ?>>Muscle Gain</option>
                            <option value="endurance" <?php echo ($existingProfile && $existingProfile['primary_goal']=='endurance') ? 'selected' : ''; ?>>Endurance</option>
                            <option value="flexibility" <?php echo ($existingProfile && $existingProfile['primary_goal']=='flexibility') ? 'selected' : ''; ?>>Flexibility</option>
                            <option value="general_health" <?php echo ($existingProfile && $existingProfile['primary_goal']=='general_health') ? 'selected' : ''; ?>>General Well-being</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Activity Level</label>
                        <select name="activity" required>
                            <option value="sedentary" <?php echo ($existingProfile && $existingProfile['activity_level']=='sedentary') ? 'selected' : ''; ?>>Sedentary</option>
                            <option value="lightly_active" <?php echo ($existingProfile && $existingProfile['activity_level']=='lightly_active') ? 'selected' : ''; ?>>Lightly Active</option>
                            <option value="moderately_active" <?php echo ($existingProfile && $existingProfile['activity_level']=='moderately_active') ? 'selected' : ''; ?>>Moderately Active</option>
                            <option value="very_active" <?php echo ($existingProfile && $existingProfile['activity_level']=='very_active') ? 'selected' : ''; ?>>Very Active</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Workout Days Per Week</label>
                        <input type="number" name="workout_days" value="<?php echo $existingProfile['workout_days_per_week'] ?? '3'; ?>" min="1" max="7">
                    </div>
                </div>

                <!-- Step 3: Health & History -->
                <div class="form-step" id="step-3">
                    <div class="step-title">
                        <h2>Medical & History</h2>
                        <p>Private medical insights and constraints.</p>
                    </div>
                    <div class="form-group">
                        <label>Past or Current Injuries</label>
                        <textarea name="injuries" rows="3" placeholder="Lower back pain, etc..."><?php echo $existingProfile['injuries'] ?? ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Medical Conditions</label>
                        <textarea name="medical" rows="3" placeholder="Asthma, etc..."><?php echo $existingProfile['medical_conditions'] ?? ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Allergies</label>
                        <input type="text" name="allergies" value="<?php echo $existingProfile['allergies'] ?? ''; ?>" placeholder="Peanuts, etc...">
                    </div>
                </div>

                <!-- Step 4: Lifestyle & Nutrition -->
                <div class="form-step" id="step-4">
                    <div class="step-title">
                        <h2>Lifestyle Habits</h2>
                        <p>Habits that define your health metrics.</p>
                    </div>
                    <div class="input-grid">
                        <div class="form-group">
                            <label>Avg. Sleep (Hours)</label>
                            <input type="number" name="sleep" value="<?php echo $existingProfile['sleep_hours_avg'] ?? ''; ?>" min="1" max="24" placeholder="7">
                        </div>
                        <div class="form-group">
                            <label>Daily Water (Liters)</label>
                            <input type="number" name="water" value="<?php echo $existingProfile['water_intake_liters'] ?? ''; ?>" step="0.1" placeholder="2.5" required>
                        </div>
                        <div class="form-group full">
                            <label>Dietary Preference</label>
                            <select name="diet">
                                <option value="balanced" <?php echo ($existingProfile && $existingProfile['diet_preference']=='balanced') ? 'selected' : ''; ?>>Balanced</option>
                                <option value="vegetarian" <?php echo ($existingProfile && $existingProfile['diet_preference']=='vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
                                <option value="vegan" <?php echo ($existingProfile && $existingProfile['diet_preference']=='vegan') ? 'selected' : ''; ?>>Vegan</option>
                                <option value="keto" <?php echo ($existingProfile && $existingProfile['diet_preference']=='keto') ? 'selected' : ''; ?>>Ketogenic</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Final Review -->
                <div class="form-step" id="step-5">
                    <div class="step-title">
                        <h2>Final Check</h2>
                        <p>Equipment and confirmation.</p>
                    </div>
                    <div class="form-group">
                        <label>Equipment Access</label>
                        <select name="equipment">
                            <option value="full_gym" <?php echo ($existingProfile && $existingProfile['equipment_access']=='full_gym') ? 'selected' : ''; ?>>Full Commercial Gym</option>
                            <option value="home_gym" <?php echo ($existingProfile && $existingProfile['equipment_access']=='home_gym') ? 'selected' : ''; ?>>Home Gym</option>
                            <option value="bodyweight" <?php echo ($existingProfile && $existingProfile['equipment_access']=='bodyweight') ? 'selected' : ''; ?>>Bodyweight Only</option>
                        </select>
                    </div>
                    <div style="text-align: center; padding: 40px 0;">
                        <i class="fas fa-shield-alt" style="font-size: 64px; color: var(--primary); margin-bottom: 20px;"></i>
                        <h3>Privacy Guaranteed</h3>
                        <p>Your profile is your own. Click Save to apply changes.</p>
                    </div>
                </div>

                <div class="form-actions" id="formActions">
                    <button type="button" class="btn btn-prev" id="prevBtn" style="visibility: hidden;"><i class="fas fa-arrow-left"></i> Back</button>
                    <button type="button" class="btn btn-next" id="nextBtn">Next <i class="fas fa-arrow-right"></i></button>
                    <button type="submit" class="btn btn-save" id="saveBtn">Save Changes <i class="fas fa-save"></i></button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 5;
        const hasExisting = <?php echo $existingProfile ? 'true' : 'false'; ?>;
        
        const setupForm = document.getElementById('setupForm');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const saveBtn = document.getElementById('saveBtn');
        const editToggle = document.getElementById('editToggle');
        const editText = document.getElementById('editText');

        // Initial State: Disable inputs if profile exists
        if (hasExisting) {
            disableForm();
        }

        function disableForm() {
            const inputs = setupForm.querySelectorAll('input, select, textarea');
            inputs.forEach(input => input.disabled = true);
            saveBtn.style.display = 'none';
        }

        function enableForm() {
            const inputs = setupForm.querySelectorAll('input, select, textarea');
            inputs.forEach(input => input.disabled = false);
            if (currentStep === totalSteps) {
                saveBtn.style.display = 'flex';
                nextBtn.style.display = 'none';
            }
        }

        if (editToggle) {
            editToggle.addEventListener('click', () => {
                const isEditing = editToggle.classList.contains('editing');
                if (isEditing) {
                    disableForm();
                    editToggle.classList.remove('editing');
                    editText.innerText = "Edit Profile";
                    editToggle.querySelector('i').className = "fas fa-edit";
                } else {
                    enableForm();
                    editToggle.classList.add('editing');
                    editText.innerText = "Cancel Editing";
                    editToggle.querySelector('i').className = "fas fa-times";
                }
            });
        }

        function updateForm() {
            document.querySelectorAll('.form-step').forEach(step => step.classList.remove('active'));
            document.getElementById(`step-${currentStep}`).classList.add('active');

            document.querySelectorAll('.step').forEach((step, index) => {
                const stepIdx = index + 1;
                if (stepIdx === currentStep) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else if (stepIdx < currentStep) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                } else {
                    step.classList.remove('active', 'completed');
                }
            });

            prevBtn.style.visibility = (currentStep === 1) ? 'hidden' : 'visible';
            
            const isEditingMode = editToggle ? editToggle.classList.contains('editing') : true;

            if (currentStep === totalSteps) {
                nextBtn.style.display = 'none';
                if (isEditingMode) saveBtn.style.display = 'flex';
            } else {
                nextBtn.style.display = 'flex';
                saveBtn.style.display = 'none';
            }
        }

        // Validation Logic
        function validateCurrentStep() {
            const currentStepDiv = document.getElementById(`step-${currentStep}`);
            // Select all inputs, selects, and textareas in the current step
            const inputs = currentStepDiv.querySelectorAll('input, select, textarea');
            let isValid = true;
            let firstInvalid = null;

            // Helper to check radio groups
            const radioGroupsChecked = {};

            inputs.forEach(input => {
                // Skip if disabled (editing mode check handled by UI logic, but good safety)
                if (input.disabled) return;

                let isInputValid = true;

                if (input.type === 'radio') {
                    if (radioGroupsChecked[input.name] === undefined) {
                        const group = currentStepDiv.querySelectorAll(`input[name="${input.name}"]`);
                        const isChecked = Array.from(group).some(r => r.checked);
                        radioGroupsChecked[input.name] = isChecked;
                        if (!isChecked) isInputValid = false;
                    } else {
                        // Already checked this group in this loop iteration (via the first radio button)
                        if (!radioGroupsChecked[input.name]) isInputValid = false; 
                         // But we don't need to re-flag invalid, just skip processing
                         return;
                    }
                    // Visual feedback for radio
                    const groupContainer = input.closest('.radio-group');
                    if (groupContainer) {
                        groupContainer.style.border = isInputValid ? 'none' : '1px solid var(--accent)';
                        if(!isInputValid) groupContainer.style.borderRadius = '10px';
                    }

                } else {
                    // Text, Number, Date, Select, Textarea
                    // User requested "nothing went not filled", so we check strict emptiness
                    if (!input.value.trim()) {
                        isInputValid = false;
                        input.style.borderColor = 'var(--accent)';
                    } else {
                        input.style.borderColor = 'var(--border)';
                    }
                }

                if (!isInputValid) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = input;
                }
            });

            if (!isValid) {
                if(firstInvalid) firstInvalid.focus();
                return false;
            }
            return true;
        }

        nextBtn.addEventListener('click', () => {
             // Validate before proceeding
            if (!validateCurrentStep()) {
                // Optional: usage of SweetAlert if available, otherwise native alert
                // using native alert as fallback or a simple UI indication
                // The red borders are the primary indicator
                alert("Please fill in all fields to proceed."); 
                return;
            }

            if (currentStep < totalSteps) {
                currentStep++;
                updateForm();
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                updateForm();
            }
        });

        updateForm();
    </script>
</body>
</html>
