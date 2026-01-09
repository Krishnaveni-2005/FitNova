<?php
// create_workouts.php
// Generates the missing workout detail pages based on the core_strength template.

$templatePath = __DIR__ . '/workout_core_strength.php';
if (!file_exists($templatePath)) {
    die("Template file not found: $templatePath");
}
$template = file_get_contents($templatePath);

$workouts = [
    'workout_full_body_hiit.php' => [
        'title' => 'Full Body HIIT',
        'subtitle' => 'Maximize Fat Burn in Minimum Time',
        'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1200',
        'time' => '20 minutes',
        'cals' => '300 kcal burned',
        'equip' => 'None / Dumbbells',
        'level' => 'Beginner - Advanced',
        'desc' => 'High Intensity Interval Training (HIIT) alternates short bursts of intense anaerobic exercise with recovery periods. This full-body routine is designed to spike your heart rate and keep burning calories long after the workout is done.',
        'exercises' => [
            ['Jumping Jacks', '3 sets x 45 seconds', 'Warm up the whole body and get the heart rate up.'],
            ['Burpees', '3 sets x 30 seconds', 'Full body explosive movement. Add a pushup for extra intensity.'],
            ['Squat Jumps', '3 sets x 30 seconds', 'Explosive power for legs and glutes. Land softly.'],
            ['Pushups', '3 sets x 30 seconds', 'Upper body strength. Modify on knees if needed.'],
            ['High Knees', '3 sets x 30 seconds', 'Cardio blast. Drive knees as high as possible.'],
            ['Mountain Climbers', '3 sets x 30 seconds', 'Core stability and cardio in one.']
        ]
    ],
    'workout_strength_training.php' => [
        'title' => 'Strength Training 101',
        'subtitle' => 'Build a Strong Foundation',
        'image' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&q=80&w=1200',
        'time' => '45 minutes',
        'cals' => '250 kcal burned',
        'equip' => 'Barbell / Dumbbells',
        'level' => 'Beginner',
        'desc' => 'Master the compound movements that are essential for building overall body strength and muscle mass. Focus on controlled movements and proper form.',
        'exercises' => [
            ['Goblet Squats', '3 sets x 10-12 reps', 'Hold weight at chest. Sit back and down, keeping chest up.'],
            ['Pushups / Chest Press', '3 sets x 10-12 reps', 'Engage chest and triceps. Keep core tight.'],
            ['Bent Over Rows', '3 sets x 10-12 reps', 'Hinge at hips, pull weight to hip crease. Squeeze back.'],
            ['Overhead Press', '3 sets x 10-12 reps', 'Press weight vertically without arching back.'],
            ['Romanian Deadlift', '3 sets x 10-12 reps', 'Hinge at hips, slight knee bend. Feel hamstring stretch.']
        ]
    ],
    'workout_yoga_flexibility.php' => [
        'title' => 'Yoga for Flexibility',
        'subtitle' => 'Restore, Relax, and Lengthen',
        'image' => 'https://images.unsplash.com/photo-1549576490-b0b4831ef60a?auto=format&fit=crop&q=80&w=1200',
        'time' => '30 minutes',
        'cals' => '100 kcal burned',
        'equip' => 'Yoga Mat',
        'level' => 'All Levels',
        'desc' => 'A gentle flow focused on opening up tight areas like hips, hamstrings, and shoulders. Perfect for recovery days or stress relief.',
        'exercises' => [
            ['Downward Facing Dog', 'Hold for 5-8 breaths', 'Lengthen spine and pedal out the feet.'],
            ['Warrior II', 'Hold for 5 breaths (each side)', 'Open hips and strengthen legs. Gaze over front hand.'],
            ['Triangle Pose', 'Hold for 5 breaths (each side)', 'Side body stretch and hamstring opener.'],
            ['Pigeon Pose', 'Hold for 10 breaths (each side)', 'Deep hip opener. Relax into the stretch.'],
            ['Child\'s Pose', 'Hold for 10 breaths', 'Resting pose to lengthen the back and relax.']
        ]
    ],
    'workout_cardio_blast.php' => [
        'title' => 'Cardio Blast',
        'subtitle' => 'Sweat it Out',
        'image' => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&q=80&w=1200',
        'time' => '30 minutes',
        'cals' => '350+ kcal burned',
        'equip' => 'Jump Rope (Optional)',
        'level' => 'Intermediate',
        'desc' => 'High-energy cardio session designed to improve cardiovascular health and burn maximum calories.',
        'exercises' => [
            ['Jump Rope / Mock Rope', '3 mins', 'Steady bounce to get rhythm.'],
            ['Boxing Jacks', '45 seconds', 'Jumping jacks with a punch.'],
            ['Skaters', '45 seconds', 'Side to side plyometric movement.'],
            ['Running in Place', '60 seconds', 'High knees for intensity.'],
            ['Butt Kicks', '60 seconds', 'Dynamic hamstring stretch/cardio.']
        ]
    ],
    'workout_upper_body.php' => [
        'title' => 'Upper Body Power',
        'subtitle' => 'Define Your Arms and Torso',
        'image' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&q=80&w=1200',
        'time' => '40 minutes',
        'cals' => '200 kcal burned',
        'equip' => 'Dumbbells',
        'level' => 'Intermediate',
        'desc' => 'Target chest, back, shoulders, and arms with this comprehensive upper body resistance routine.',
        'exercises' => [
            ['Dumbbell Chest Press', '3 sets x 12 reps', 'Press weights up over chest.'],
            ['Single Arm Row', '3 sets x 12 reps (each)', 'Pull weight to hip, engage lat.'],
            ['Lateral Raises', '3 sets x 15 reps', 'Lift weights to side to shoulder height.'],
            ['Bicep Curls', '3 sets x 12 reps', 'Curl weight with control.'],
            ['Tricep Extensions', '3 sets x 12 reps', 'Extend arm overhead or kickback.']
        ]
    ],
    'workout_lower_body.php' => [
        'title' => 'Lower Body Sculpt',
        'subtitle' => 'Strong Legs and Glutes',
        'image' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&q=80&w=1200',
        'time' => '40 minutes',
        'cals' => '250 kcal burned',
        'equip' => 'Dumbbells (Optional)',
        'level' => 'Intermediate',
        'desc' => 'A leg-focused workout to build strength and tone in the quads, hamstrings, and glutes.',
        'exercises' => [
            ['Weighted Squats', '3 sets x 12 reps', 'Keep weight in heels.'],
            ['Walking Lunges', '3 sets x 12 reps (each leg)', 'Step forward, drop back knee.'],
            ['Glute Bridges', '3 sets x 20 reps', 'Squeeze glutes at the top.'],
            ['Calf Raises', '3 sets x 20 reps', 'Lift heels as high as possible.'],
            ['Sumo Squats', '3 sets x 12 reps', 'Wide stance to target inner thighs.']
        ]
    ],
    'workout_functional.php' => [
        'title' => 'Functional Fitness',
        'subtitle' => 'Move Better in Daily Life',
        'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=1200',
        'time' => '35 minutes',
        'cals' => '220 kcal burned',
        'equip' => 'Kettlebell / Dumbbell',
        'level' => 'All Levels',
        'desc' => 'Exercises designed to train your muscles to work together and prepare them for daily tasks by simulating common movements.',
        'exercises' => [
            ['Kettlebell Swings', '3 sets x 15 reps', 'Hinge movement driven by hips.'],
            ['Farmer\'s Walk', '3 sets x 60 seconds', 'Walk carrying weights. Core stability.'],
            ['Step Ups', '3 sets x 12 reps (each leg)', 'Step up onto a box or chair.'],
            ['Woodchoppers', '3 sets x 12 reps (each side)', 'Rotational core movement.'],
            ['Bear Crawl', '3 sets x 45 seconds', 'Functional core and shoulder stability.']
        ]
    ],
    'workout_pilates.php' => [
        'title' => 'Pilates Core Flow',
        'subtitle' => 'Control and Precision',
        'image' => 'https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?auto=format&fit=crop&q=80&w=1200',
        'time' => '30 minutes',
        'cals' => '150 kcal burned',
        'equip' => 'Mat',
        'level' => 'Beginner',
        'desc' => 'Low-impact exercises emphasizing core strength, posture, balance, and flexibility.',
        'exercises' => [
            ['The Hundred', '100 pumps', 'Legs lifted, pump arms vigorously.'],
            ['Roll Ups', '8-10 reps', 'Articulate spine vertebrae by vertebrae.'],
            ['Single Leg Circles', '8 reps (each direction)', 'Stable pelvis, mobile hip.'],
            ['Rolling Like a Ball', '8-10 reps', 'Massage the spine, find balance.'],
            ['Spine Stretch', '8-10 reps', 'Sit tall, reach forward over imaginary ball.']
        ]
    ]
];

foreach ($workouts as $filename => $data) {
    $content = $template;
    
    // Replace placeholders with specific data
    // Note: This relies on the specific structure of workout_core_strength.php
    // We'll use regex/string replacement to swap out content blocks.
    
    // 1. Meta Tags
    $content = str_replace('Core Strength Training - FitNova', $data['title'] . ' - FitNova', $content);
    $content = str_replace('Core Strength Training', $data['title'], $content);
    $content = str_replace('Build a Powerful Core Foundation', $data['subtitle'], $content);
    
    // 2. Image
    $content = preg_replace("/https:\/\/images\.unsplash\.com\/photo-[a-zA-Z0-9-]+\?auto=format&fit=crop&q=80&w=1200/", $data['image'], $content);
    
    // 3. Meta Data
    $content = str_replace('25 minutes', $data['time'], $content);
    $content = str_replace('200 kcal burned', $data['cals'], $content);
    $content = str_replace('No Equipment', $data['equip'], $content);
    $content = str_replace('Intermediate', $data['level'], $content);
    
    // 4. Description
    $descPattern = '/<h2>About This Workout<\/h2>\s*<p>(.*?)<\/p>/s';
    $content = preg_replace($descPattern, "<h2>About This Workout</h2>\n            <p>{$data['desc']}</p>", $content);
    
    // 5. Exercises List
    // We build the new list HTML
    $listHtml = '<ul class="exercise-list">';
    foreach ($data['exercises'] as $index => $ex) {
        $num = $index + 1;
        $listHtml .= "\n                <li>\n                    <strong>$num. {$ex[0]}</strong>\n                    {$ex[1]}<br>\n                    {$ex[2]}\n                </li>";
    }
    $listHtml .= "\n            </ul>";
    
    $listPattern = '/<ul class="exercise-list">.*?<\/ul>/s';
    $content = preg_replace($listPattern, $listHtml, $content);
    
    $fullPath = __DIR__ . '/' . $filename;
    file_put_contents($fullPath, $content);
    echo "Created: $fullPath\n";
}

echo "Done!";
?>
