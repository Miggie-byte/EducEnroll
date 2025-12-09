<?php
session_start();

$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sub_name   = $_POST['sub_name'] ?? '';
    $sub_units  = $_POST['sub_units'] ?? '';
    $sub_prereq = $_POST['sub_prereq'] ?? '';
    $sub_desc   = $_POST['sub_desc'] ?? '';

    $sub_name = trim($_POST['sub_name'] ?? '');

    if ($sub_name === "" || !preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $sub_name)) {
        echo "<script>
                alert('Invalid subject name! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
                window.location.href = 'add_subject.php';
            </script>";
        exit();
    }
    
    $sub_name   = $_POST['sub_name'] ?? '';
    // Step 0: Check duplicate subject name
    $check = $conn->prepare("SELECT SUB_ID FROM SUBJECT WHERE SUB_NAME = ?");
    $check->bind_param("s", $sub_name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>
                alert('Error: Subject name already exists.');
                window.location.href='../Admin/AdminDashboard.php';
            </script>";
        exit;
    }
    $check->close();

    // Step 1: Get the latest SUB_ID
    $result = $conn->query("SELECT SUB_ID FROM SUBJECT ORDER BY SUB_ID DESC LIMIT 1");

    if ($result && $row = $result->fetch_assoc()) {
        // Extract numeric part after "SUB_"
        $lastNum = intval(substr($row['SUB_ID'], 4));
        $newNum  = $lastNum + 1;
    } else {
        // If no record yet, start at 101
        $newNum = 101;
    }

    // Step 2: Check range
    if ($newNum > 410) {
        echo "<script>
                alert('Error: Subject ID limit reached (SUB_410 is the maximum).');
                window.location.href='../Admin/AdminDashboard.php';
              </script>";
        exit;
    }

    // Step 3: Format new ID
    $newSubId = "SUB_" . $newNum;

    // Step 4: Validate and cast units
    if (!is_numeric($sub_units)) {
        echo "<script>
                alert('Error: Units must be a number.');
                window.location.href='../Admin/AdminDashboard.php';
            </script>";
        exit;
    }
    $sub_units = (int)$sub_units; // force integer

    // Step 5: Insert new subject
    $sql = "INSERT INTO SUBJECT (SUB_ID, SUB_NAME, SUB_UNITS, SUB_PREREQ, SUB_DESC)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $newSubId, $sub_name, $sub_units, $sub_prereq, $sub_desc);

    if ($stmt->execute()) {
        echo "<script>
                alert('Subject added successfully! ID: $newSubId');
                window.location.href='../Admin/AdminDashboard.php';
            </script>";
    } else {
        echo "<script>
                alert('Error: " . addslashes($stmt->error) . "');
                window.location.href='../Admin/AdminDashboard.php';
            </script>";
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Subject</title>
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
        input[type="text"], textarea {
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
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add Subject</h2>
        <form method="POST" action="add_subject.php">
            <label for="sub_name">Subject Name</label>
            <input type="text" id="sub_name" name="sub_name" required>

            <label for="sub_units">Units</label>
            <input type="text" id="sub_units" name="sub_units" required>

            <label for="sub_prereq">Prerequisite</label>
            <input type="text" id="sub_prereq" name="sub_prereq">

            <label for="sub_desc">Description</label>
            <textarea id="sub_desc" name="sub_desc" rows="3"></textarea>

            <button type="submit">Add Subject</button>
            <a href="../Admin/AdminDashboard.php" class="back-btn">← Go Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
