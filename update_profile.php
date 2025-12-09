<?php
session_start();

$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$studId = $_SESSION['stud_id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST['password'] ?? '';

    if (empty($studId)) {
        echo "<script>
                alert('Warning: No student is logged in.');
                window.location.href='update_profile.php';
              </script>";
    } elseif (empty($password)) {
        echo "<script>
                alert('Warning: New password cannot be empty.');
                window.location.href='update_profile.php';
              </script>";
    } else {
        $sql = "UPDATE STUDENT SET STUD_PASS = ? WHERE STUD_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $password, $studId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // ✅ Update session so dashboard reflects new password
                $_SESSION['stud_pass'] = $password;

                echo "<script>
                        alert('Password updated successfully for Student ID: $studId');
                        window.location.href='StudentDashboard.php';
                      </script>";
            } else {
                echo "<script>
                        alert('No student found with ID: $studId');
                        window.location.href='update_profile.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Error updating password: " . addslashes($stmt->error) . "');
                    window.location.href='update_profile.php';
                  </script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
