<?php
session_start();

// Enable exceptions for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inst_id = $_POST['inst_id'] ?? '';
    $sub_id  = $_POST['sub_id'] ?? '';
    $sec_id  = $_POST['sec_id'] ?? '';

    // Step 0: Check for duplicate teaching assignment
    $dup = $conn->prepare("
        SELECT TA_ID 
        FROM TEACHING_ASSIGNMENT 
        WHERE INST_ID = ? AND SUB_ID = ? AND SEC_ID = ?
    ");
    $dup->bind_param("sss", $inst_id, $sub_id, $sec_id);
    $dup->execute();
    $dup->store_result();

    if ($dup->num_rows > 0) {
        echo "<script>
                alert('Error: This teaching assignment already exists.');
                window.location.href='../Admin/AdminDashboard.php';
            </script>";
        exit;
    }
    $dup->close();

    try {
        // Step 1: Get the latest TA_ID
        $result = $conn->query("SELECT TA_ID FROM TEACHING_ASSIGNMENT ORDER BY TA_ID DESC LIMIT 1");

        if ($result && $row = $result->fetch_assoc()) {
            $lastNum = intval(substr($row['TA_ID'], 3));
            $newNum  = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        if ($newNum > 999) {
            echo "<script>
                    alert('Error: Teaching Assignment ID limit reached (TA_999 is the maximum).');
                    window.location.href='../Admin/AdminDashboard.php';
                  </script>";
            exit;
        }

        $newTaId = "TA_" . str_pad($newNum, 3, "0", STR_PAD_LEFT);

        // Step 4: Insert new teaching assignment
        $sql = "INSERT INTO TEACHING_ASSIGNMENT (TA_ID, INST_ID, SUB_ID, SEC_ID)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $newTaId, $inst_id, $sub_id, $sec_id);

        $stmt->execute();

        echo "<script>
                alert('Teaching Assignment added successfully! ID: $newTaId');
                window.location.href='../Admin/AdminDashboard.php';
              </script>";

        $stmt->close();

    } catch (mysqli_sql_exception $e) {
        // Log the technical error for debugging
        error_log("Database error: " . $e->getMessage());

        // Show a clean message to the user
        echo "<script>
                alert('⚠️ Unable to add teaching assignment. Please ensure the Subject ID, Instructor ID, and Section ID exist.');
                window.location.href='../Admin/AdminDashboard.php';
              </script>";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Teaching Assignment</title>
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
        input[type="text"] {
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
        <h2>Add Teaching Assignment</h2>
        <form method="POST" action="add_teachingassign.php">
            <label for="inst_id">Instructor ID</label>
            <input type="text" id="inst_id" name="inst_id" required>

            <label for="sub_id">Subject ID</label>
            <input type="text" id="sub_id" name="sub_id" required>

            <label for="sec_id">Section ID</label>
            <input type="text" id="sec_id" name="sec_id" required>

            <button type="submit">Add Teaching Assignment</button>
        </form>
    </div>
</body>
</html>
