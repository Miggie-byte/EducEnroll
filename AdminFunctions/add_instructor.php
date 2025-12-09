<?php
// add_prof.php
session_start();

// Connect to DB
$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inst_fname = $_POST['inst_fname'] ?? '';
    if (preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $inst_fname)) {
} else {
    echo "<script>
            alert('Invalid First name! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
            window.location.href = 'add_instructor.php';
          </script>";
          exit();
}

    $inst_lname = $_POST['inst_lname'] ?? '';
    if (preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $inst_lname)) {
} else {
    echo "<script>
            alert('Invalid Last name! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
            window.location.href = 'add_instructor.php';
          </script>";
          exit();
}
    $inst_email = $_POST['inst_email'] ?? '';
if (preg_match("/^.+@schoolname\.edu\.ph$/", $inst_email)) {
    $_SESSION['inst_email'] = $inst_email;
} else {
    echo "<script>
            alert('Invalid Email! Must follow the format: xxxxx@schoolname.edu.ph');
            window.location.href = 'add_instructor.php';
          </script>";
    exit();
}
    $inst_pnum  = $_POST['inst_pnum'] ?? '';
    if (!ctype_digit($inst_pnum) || strlen($inst_pnum) > 11) {
    echo "<script>
            alert('Invalid phone number! Must be numeric and up to 11 digits.');
            window.location.href = 'add_instructor.php';
          </script>";
    exit();
}







    // Step 1: Get the latest INST_ID
    $result = $conn->query("SELECT INST_ID FROM INSTRUCTOR ORDER BY INST_ID DESC LIMIT 1");

    if ($result && $row = $result->fetch_assoc()) {
        // Extract numeric part after "INST_"
        $lastNum = intval(substr($row['INST_ID'], 5));
        $newNum  = $lastNum + 1;
    } else {
        // If no record yet, start at 101
        $newNum = 101;
    }

    // Step 2: Check range
    if ($newNum > 410) {
        die("<p style='color:red;'>Error: Instructor ID limit reached (INST_410 is the maximum).</p>");
    }

    // Step 3: Format new ID
    $newInstId = "INST_" . $newNum;

    // Step 4: Check for duplicates before inserting
    $checkSql = "SELECT INST_ID FROM INSTRUCTOR WHERE INST_EMAIL = ? OR INST_PNUM = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $inst_email, $inst_pnum);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('Error: Instructor with this email or phone number already exists.');
        window.location.href = '../Admin/AdminDashboard.php';</script>";
    } else {
        // Step 5: Insert new instructor
        $sql = "INSERT INTO INSTRUCTOR (INST_ID, INST_FNAME, INST_LNAME, INST_EMAIL, INST_PNUM)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $newInstId, $inst_fname, $inst_lname, $inst_email, $inst_pnum);

        if ($stmt->execute()) {
            echo "<script>alert('Professor added successfully! ID: $newInstId');
            window.location.href = '../Admin/AdminDashboard.php';</script>";
            
        } else {
            echo "<script>alert('Error: " . addslashes($stmt->error) . "');
            window.location.href = '../Admin/AdminDashboard.php';</script>";
        }
    }
        $stmt->close();
        
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Professor</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 400px;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .back-btn:hover {
            background: #ddd;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add Professor</h2>
        <form method="POST" action="add_instructor.php">
            <label for="inst_fname">First Name</label>
            <input type="text" id="inst_fname" name="inst_fname" required>

            <label for="inst_lname">Last Name</label>
            <input type="text" id="inst_lname" name="inst_lname" required>

            <label for="inst_email">Email</label>
            <input type="email" id="inst_email" name="inst_email" required>

            <label for="inst_pnum">Phone Number</label>
            <input type="text" id="inst_pnum" name="inst_pnum" required>

            <button type="submit">Add Professor</button>
            <a href="../Admin/AdminDashboard.php" class="back-btn">← Go Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
