<?php
session_start();

$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $prog_name = $_POST['prog_name'] ?? '';
    $prog_desc = $_POST['prog_desc'] ?? '';
    $prog_dep  = $_POST['prog_dep'] ?? '';

    $prog_name = trim($_POST['prog_name'] ?? '');

    $prog_dep  = trim($_POST['prog_dep'] ?? '');

    // PROGRAM NAME
    if ($prog_name === "" || !preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $prog_name)) {
        echo "<script>
                alert('Invalid Program Name! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
                window.location.href = 'add_program.php';
            </script>";
        exit();
    }
    $_SESSION['prog_name'] = $prog_name;

    // PROGRAM DEPARTMENT
    if ($prog_dep === "" || !preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $prog_dep)) {
        echo "<script>
                alert('Invalid Department! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
                window.location.href = 'add_program.php';
            </script>";
        exit();
    }
    $_SESSION['prog_dep'] = $prog_dep;

    // Step 0: Duplicate check (by program name)
    $check = $conn->prepare("SELECT PROG_ID FROM PROGRAM WHERE PROG_NAME = ?");
    $check->bind_param("s", $prog_name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>
                alert('Error: Program name already exists.');
                window.location.href='../Admin/AdminDashboard.php';
              </script>";
        exit;
    }
    $check->close();

    // Step 1: Get the latest PROG_ID
    $result = $conn->query("SELECT PROG_ID FROM PROGRAM ORDER BY PROG_ID DESC LIMIT 1");

    if ($result && $row = $result->fetch_assoc()) {
        // Extract numeric part after "PROG_"
        $lastNum = intval(substr($row['PROG_ID'], 5));
        $newNum  = $lastNum + 1;
    } else {
        // If no record yet, start at 101
        $newNum = 101;
    }

    // Step 2: Check range
    if ($newNum > 410) {
        echo "<script>
                alert('Error: Program ID limit reached (PROG_410 is the maximum).');
                window.location.href='../Admin/AdminDashboard.php';
              </script>";
        exit;
    }

    // Step 3: Format new ID
    $newProgId = "PROG_" . $newNum;

    // Step 4: Insert new program
    $sql = "INSERT INTO PROGRAM (PROG_ID, PROG_NAME, PROG_DESC, PROG_DEP)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $newProgId, $prog_name, $prog_desc, $prog_dep);

    if ($stmt->execute()) {
        echo "<script>
                alert('Program added successfully! ID: $newProgId');
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
    <title>Add Program</title>
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
        <h2>Add Program</h2>
        <form method="POST" action="add_program.php">
            <label for="prog_name">Program Name</label>
            <input type="text" id="prog_name" name="prog_name" required>

            <label for="prog_desc">Description</label>
            <textarea id="prog_desc" name="prog_desc" rows="3"></textarea>

            <label for="prog_dep">Department</label>
            <input type="text" id="prog_dep" name="prog_dep" required>

            <button type="submit">Add Program</button>
            <a href="../Admin/AdminDashboard.php" class="back-btn">← Go Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
