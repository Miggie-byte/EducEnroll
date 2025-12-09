<?php
session_start();

$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ta_id = $_POST['ta_id'] ?? '';

    if (empty($ta_id)) {
        echo "<script>
                alert('Warning: TA_ID is required.');
                window.location.href='delete_teachingassign.php';
              </script>";
    } else {
        // Delete teaching assignment
        $sql = "DELETE FROM TEACHING_ASSIGNMENT WHERE TA_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ta_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<script>
                        alert('Teaching Assignment with ID $ta_id deleted successfully.');
                        window.location.href='../Admin/AdminDashboard.php';
                      </script>";
            } else {
                echo "<script>
                        alert('No Teaching Assignment found with ID $ta_id.');
                        window.location.href='delete_teachingassign.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Error deleting record: " . addslashes($stmt->error) . "');
                    window.location.href='delete_teachingassign.php';
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
    <title>Delete Teaching Assignment</title>
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
            color: #e53935;
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
            background: linear-gradient(135deg, #e53935 0%, #d32f2f 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background: #c62828;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Delete Teaching Assignment</h2>
        <form method="POST" action="delete_teachingassign.php"
              onsubmit="return confirm('Are you sure you want to delete Teaching Assignment with ID: ' + document.getElementById('ta_id').value + '?');">
            <label for="ta_id">Teaching Assignment ID</label>
            <input type="text" id="ta_id" name="ta_id" required>

            <button type="submit">Delete Assignment</button>
        </form>
    </div>
</body>
</html>

