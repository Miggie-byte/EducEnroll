<?php
session_start();

$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $enr_id   = $_POST['enr_id'] ?? '';
    $enr_stat = $_POST['enr_stat'] ?? '';

    // Validate inputs
    $errors = [];
    if (empty($enr_id))  $errors[] = "Enrollment ID is required.";
    if (empty($enr_stat)) $errors[] = "Enrollment status is required.";

        // ✅ Check if ENR_ID exists
    $sqlCheck = "SELECT ENR_ID FROM ENROLLMENT WHERE ENR_ID = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $enr_id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows == 0) {
        // ENR_ID not found → stop here
        echo "<script>
                alert('Error: Enrollment ID $enr_id does not exist.');
                window.location.href='update_studentstat.php';
              </script>";
        $stmtCheck->close();
        exit();
    }
    $stmtCheck->close();

    if (!empty($errors)) {
        echo "<script>
                alert('Warning:\\n" . implode("\\n", $errors) . "');
                window.location.href='update_studentstat.php';
              </script>";
    } else {
        // ✅ Update ENR_STAT for the given ENR_ID
        $sql = "UPDATE ENROLLMENT SET ENR_STAT = ? WHERE ENR_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $enr_stat, $enr_id);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Enrollment status updated successfully for Enrollment ID: $enr_id');
                    window.location.href='../Admin/AdminDashboard.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error updating status: " . addslashes($stmt->error) . "');
                    window.location.href='update_studentstat.php';
                  </script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Student Status</title>
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
        input[type="text"], select {
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
        <h2>Update Student Status</h2>
        <form method="POST" action="update_studentstat.php">
            <label for="enr_id">Enrollment ID</label>
            <input type="text" id="enr_id" name="enr_id" required>

            <label for="enr_stat">Enrollment Status</label>
            <select id="enr_stat" name="enr_stat" required>
                <option value="">-- Select Status --</option>
                <option value="PENDING">PENDING</option>
                <option value="ENROLLED">ENROLLED</option>
                <option value="COMPLETED">COMPLETED</option>
                <option value="DROPPED">DROPPED</option>
            </select>

            <button type="submit">Update Status</button>
            <a href="../Admin/AdminDashboard.php" class="back-btn">← Go Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
