<?php
session_start();
$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// ✅ Only look at ENROLLED enrollments
$sql = "SELECT s.STUD_ID, s.STUD_FNAME, s.STUD_LNAME, s.STUD_DOB, s.STUD_ADD, s.STUD_PNUM, s.STUD_GENDER,
               e.ENR_STAT, e.SEC_ID, e.PROG_ID, e.ENR_YR,  
               p.PROG_NAME, p.PROG_DESC
        FROM STUDENT s
        JOIN ENROLLMENT e ON s.STUD_ID = e.STUD_ID
        JOIN PROGRAM p ON e.PROG_ID = p.PROG_ID
        WHERE s.STUD_EMAIL = ? 
          AND s.STUD_PASS = ?
          AND e.ENR_STAT = 'ENROLLED'
        ORDER BY e.ENR_DATE DESC
        LIMIT 1";   // ensure only one record

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows >= 1) {
    $user = $result->fetch_assoc();

    // ✅ Guaranteed to be ENROLLED
    $_SESSION['stud_id']    = $user['STUD_ID'];
    $_SESSION['stud_fname'] = $user['STUD_FNAME'];
    $_SESSION['stud_lname'] = $user['STUD_LNAME'];
    $_SESSION['stud_dob']   = $user['STUD_DOB'];
    $_SESSION['stud_add']   = $user['STUD_ADD'];
    $_SESSION['stud_pnum']  = $user['STUD_PNUM'];
    $_SESSION['stud_gender']= $user['STUD_GENDER'];
    $_SESSION['stud_email'] = $email;
    $_SESSION['stud_pass']  = $password;

    // Program info
    $_SESSION['program_name']   = $user['PROG_NAME'];
    $_SESSION['program_desc']   = $user['PROG_DESC'];
    $_SESSION['program_id']     = $user['PROG_ID'];
    $_SESSION['enrollment_year']= $user['ENR_YR'];

    // Section info
    $secId = $user['SEC_ID'];
    $_SESSION['section_id'] = $secId;

    // Classmates
    $sqlClassmates = "SELECT s.STUD_FNAME, s.STUD_LNAME
                      FROM ENROLLMENT e
                      JOIN STUDENT s ON e.STUD_ID = s.STUD_ID
                      WHERE e.SEC_ID = ? AND e.ENR_STAT = 'ENROLLED' AND e.STUD_ID != ?";
    $stmt2 = $conn->prepare($sqlClassmates);
    $stmt2->bind_param("ss", $secId, $user['STUD_ID']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    $classmates = [];
    while ($row = $result2->fetch_assoc()) {
        $classmates[] = $row['STUD_FNAME'] . " " . $row['STUD_LNAME'];
    }
    $_SESSION['classmates'] = $classmates;

    // Professors
    $sqlProf = "SELECT i.INST_FNAME, i.INST_LNAME
                FROM TEACHING_ASSIGNMENT t
                JOIN INSTRUCTOR i ON t.INST_ID = i.INST_ID
                WHERE t.SEC_ID = ?";
    $stmt3 = $conn->prepare($sqlProf);
    $stmt3->bind_param("s", $secId);
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    $professors = [];
    while ($row = $result3->fetch_assoc()) {
        $professors[] = $row['INST_FNAME'] . " " . $row['INST_LNAME'];
    }
    $_SESSION['professors'] = $professors;

    // Subjects
    $sqlSubjects = "SELECT subj.SUB_NAME
                    FROM TEACHING_ASSIGNMENT t
                    JOIN SUBJECT subj ON t.SUB_ID = subj.SUB_ID
                    WHERE t.SEC_ID = ?";
    $stmt4 = $conn->prepare($sqlSubjects);
    $stmt4->bind_param("s", $secId);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    $subjects = [];
    while ($row = $result4->fetch_assoc()) {
        $subjects[] = $row['SUB_NAME'];
    }
    $_SESSION['subjects'] = $subjects;

    // ✅ Redirect only ENROLLED students to dashboard
    header("Location: StudentDashboard.php");
    exit;

} else {
    echo "Invalid email/password or enrollment status is not ENROLLED.";
}

$conn->close();
?>
