<?php
session_start(); // must start session here too

// Student info
$studId  = $_SESSION['stud_id'] ?? '';
$fname   = $_SESSION['stud_fname'] ?? '';
$lname   = $_SESSION['stud_lname'] ?? '';
$dob     = $_SESSION['stud_dob'] ?? '';
$address = $_SESSION['stud_add'] ?? '';
$phone   = $_SESSION['stud_pnum'] ?? '';
$gender  = $_SESSION['stud_gender'] ?? '';
$email   = $_SESSION['stud_email'] ?? ''; // if you stored email in login.php
$password = $_SESSION['stud_pass'] ?? '';

// Program info
$programName = $_SESSION['program_name'] ?? '';
$programDesc = $_SESSION['program_desc'] ?? '';
$programid   = $_SESSION['program_id'] ?? '';
$sectionId   = $_SESSION['section_id'] ?? '';
$enrollyear  = $_SESSION['enrollment_year'] ?? '';

// Classmates & Professors (arrays stored in login.php)
$classmates  = $_SESSION['classmates'] ?? [];
$subjects    = $_SESSION['subjects'] ?? [];   // you can populate this later
$profs       = $_SESSION['professors'] ?? [];
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- For icons -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            color: white;
            font-size: 24px;
            font-weight: 600;
        }
        .header h1 {
            margin: 0;
        }
        .logout-btn {
            background: #ff4757;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .logout-btn:hover {
            background: #ff3742;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        .section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #4CAF50;
            font-weight: 600;
            font-size: 20px;
            display: flex;
            align-items: center;
        }
        .section h3 i {
            margin-right: 10px;
            color: #667eea;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .section ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .section li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        .section li i {
            margin-right: 10px;
            color: #4CAF50;
        }
        .section li:last-child {
            border-bottom: none;
        }
        .no-data {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hello <?php echo htmlspecialchars($fname); ?>, Welcome!</h1>
        <form method="post" action="login.html" style="margin: 0;">
            <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>
    <div class="container">
        <div class="section">
            <h3><i class="fas fa-user-edit"></i> User Profile</h3>
            <form method="post" action="update_profile.php">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($fname); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lname); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
                    <span id="togglePassword" 
                        style="position: absolute; right: 40px; bottom: 95px; cursor: pointer; font-size: 18px;"><i class="fas fa-eye"></i>
                    </span>
                </div>
                <button type="submit" class="btn">Update Profile</button>
            </form>
        </div>

        <div class="section">
            <h3><i class="fas fa-graduation-cap"></i> My Program</h3>
            <p><strong>Program:</strong> <?php echo htmlspecialchars($programName); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($programDesc); ?></p>
        </div>


        <div class="section">
            <h3><i class="fas fa-users"></i> My Section: <?php echo htmlspecialchars($sectionId); ?></h3>
            <ul>
                <?php
                // Assuming $classmates is an array of classmate names
                if (!empty($classmates)) {
                    foreach ($classmates as $classmate) {
                        echo '<li><i class="fas fa-user"></i> ' . htmlspecialchars($classmate) . '</li>';
                    }
                } else {
                    echo '<li class="no-data">No classmates listed.</li>';
                }
                ?>
            </ul>
        </div>
        <div class="section">
            <h3><i class="fas fa-book"></i> My Subjects</h3>
            <ul>
                <?php
                // Assuming $subjects is an array of subject names
                if (!empty($subjects)) {

                    $uniqueSubjects = array_unique($subjects);

                    foreach ($uniqueSubjects as $subject) {
                        echo '<li><i class="fas fa-graduation-cap"></i> ' . htmlspecialchars($subject) . '</li>';
                    }
                } else {
                    echo '<li class="no-data">No subjects listed.</li>';
                }
                ?>
            </ul>
        </div>
        <div class="section">
            <h3><i class="fas fa-chalkboard-teacher"></i> My Profs</h3>
            <ul>
                <?php
                // Assuming $profs is an array of professor names
                if (!empty($profs)) {

                    $uniqueProfs = array_unique($profs);

                    foreach ($uniqueProfs as $prof) {
                        echo '<li><i class="fas fa-user-tie"></i> ' . htmlspecialchars($prof) . '</li>';
                    }
                } else {
                    echo '<li class="no-data">No professors listed.</li>';
                }
                ?>
            </ul>
        </div>

        <div class="section">
            <h3><i class="fas fa-times-circle"></i> Drop Program</h3>
            <p>If you no longer wish to stay enrolled in your current Program, you can drop it below.</p>

            <form method="POST" action="drop_program.php"
                onsubmit="return confirm('Are you sure you want to drop your program?');">
                <button type="submit" class="btn" style="background:#e53935;">
                    <i class="fas fa-times-circle"></i> Drop Program
                </button>
            </form>
        </div>

    </div>

    <script>
    const togglePassword = document.querySelector("#togglePassword");
    const passwordInput = document.querySelector("#password");

    togglePassword.addEventListener("click", function () {
        // Toggle the type attribute
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);

        // Toggle the icon (optional)
        this.textContent = type === "password" ? "👁" : "🙈";
    });
</script>
</body>
</html>
