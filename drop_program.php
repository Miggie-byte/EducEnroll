<?php
session_start();
$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get session attributes
$studId     = $_SESSION['stud_id'] ?? '';
$programId  = $_SESSION['program_id'] ?? '';
$sectionId  = $_SESSION['section_id'] ?? '';
$enrollYear = $_SESSION['enrollment_year'] ?? '';

if (empty($studId) || empty($programId) || empty($sectionId) || empty($enrollYear)) {
    echo "<script>
            alert('Warning: Missing enrollment information.');
            window.location.href='StudentDashboard.php';
          </script>";
    exit;
}

// ✅ Delete the enrollment row
$sql = "DELETE FROM ENROLLMENT 
        WHERE STUD_ID = ? AND PROG_ID = ? AND SEC_ID = ? AND ENR_YR = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $studId, $programId, $sectionId, $enrollYear);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Optionally clear related session values
        unset($_SESSION['program_id'], $_SESSION['section_id'], $_SESSION['enrollment_year'], $_SESSION['enr_stat']);

        echo "<script>
                alert('You have successfully dropped your section. Enrollment record deleted.');
                window.location.href='StudentEnrollmentForm.html';
              </script>";
    } else {
        echo "<script>
                alert('No matching enrollment record found.');
                window.location.href='StudentDashboard.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Error deleting section: " . addslashes($stmt->error) . "');
            window.location.href='StudentDashboard.php';
          </script>";
}

$stmt->close();
$conn->close();
?>
