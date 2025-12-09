<?php
session_start(); // must start session here too

// Step 1: Generate STUD_ID (same logic as before)
$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// ENROLLMENT ASSIGNMENT AND INSERTION
// Generate ENR_ID
$sql = "SELECT MAX(ENR_ID) AS max_enr FROM ENROLLMENT";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $lastEnr = $row['max_enr']; // e.g. ER_000000123
    if ($lastEnr) {
        $seq = intval(substr($lastEnr, 3)) + 1; // strip "ER_"
    } else {
        $seq = 1;
    }
} else {
    $seq = 1;
}

$newEnrId = "ER_" . str_pad($seq, 9, "0", STR_PAD_LEFT);


// SET STATUS TO "PENDING" AS DEFAULT AND SET ER_DATE

$enrStatus = "PENDING";
$enrDate   = date("Y-m-d");

// INSERT YEAR LEVEL 
$yearLevel = $_REQUEST['year_level']; // from enrollment form


// INSERT ALSO THE CORRESPONDING PROG_ID BASED ON THE PROGRAM SELECTED

$progName = $_REQUEST['prog_name']; // e.g. "BS Computer Science"
$sqlProg = "SELECT PROG_ID FROM PROGRAM WHERE PROG_NAME = ?";
$stmt = $conn->prepare($sqlProg);
$stmt->bind_param("s", $progName);
$stmt->execute();
$result = $stmt->get_result();
$progRow = $result->fetch_assoc();
$progId  = $progRow['PROG_ID'];


// ASSIGN SECTION BASED ON PROGRAM AND YEAR LEVEL

// Program and Year Level from form
$progName  = $_REQUEST['prog_name'];   // e.g. "Computer Science"
$yearLevel = $_REQUEST['year_level'];  // e.g. "1"

// Find candidate sections that match program and year level
$sqlSec = "SELECT SEC_ID, SEC_NAME, SEC_YR, SEC_NUM_STUD, SEC_PROG
           FROM SECTION
           WHERE SEC_YR = ? AND SEC_PROG = ?
           ORDER BY SEC_NAME ASC";

$stmt = $conn->prepare($sqlSec);
$stmt->bind_param("is", $yearLevel, $progName);
$stmt->execute();
$result = $stmt->get_result();

$secId = null;
while ($row = $result->fetch_assoc()) {
    if ($row['SEC_NUM_STUD'] < 40) {
        $secId = $row['SEC_ID'];
        break; // assign first available section with capacity
    }
}

// If no section found with available slots
if (!$secId) {
    die("Error: No available section for Program '$progName' Year $yearLevel. Please contact admin.");
}

// CHECK IF STUDENT ALREADY HAS ENROLLED OR PENDING ENROLLMENT
$studId = $_REQUEST['stud_id']; // from enrollment form

$studId = trim($_REQUEST['stud_id']);

if ($studId === "") {
    echo "<script>
            alert('Student ID cannot be empty or spaces only.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}


// Check if stud_id exists in STUDENT table
$sqlCheck = "SELECT STUD_ID FROM STUDENT WHERE STUD_ID = ?";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("s", $studId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    // Student ID does not exist → stop here
    echo "<script>
            alert('Error: Student ID $studId does not exist.');
            window.location.href = 'POTSEnrollmentForm.html';
          </script>";
    $stmt->close();
    exit(); // stop further execution
}
$stmt->close();


$sqlCheck = "
    SELECT e.ENR_STAT, e.ENR_YR, p.PROG_NAME
    FROM ENROLLMENT e
    JOIN PROGRAM p ON e.PROG_ID = p.PROG_ID
    WHERE e.STUD_ID = ?
      AND (e.ENR_STAT = 'PENDING' OR e.ENR_STAT = 'ENROLLED')
";

$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("s", $studId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row     = $res->fetch_assoc();
    $status  = $row['ENR_STAT'];
    $year    = $row['ENR_YR'];
    $program = $row['PROG_NAME'];

    echo "<script>
            alert('This student already has an enrollment with status: $status. Program: $program, Year Level: $year');
            window.location.href='POTSEnrollmentForm.html';
          </script>";
    exit();
}


// INSERTION INTO ENROLLMENT TABLE
$sqlEnroll = "INSERT INTO ENROLLMENT (ENR_ID, ENR_DATE, STUD_ID, SEC_ID, ENR_STAT, ENR_YR, PROG_ID)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sqlEnroll);
$stmt->bind_param("sssssss", $newEnrId, $enrDate, $studId, $secId, $enrStatus, $yearLevel, $progId);

if ($stmt->execute()) {
    echo "Enrollment created with Year Level $yearLevel, Program $progName.";
} else {
    echo "Error inserting enrollment: " . $conn->error;
}

// FETCH STUDENT DETAILS FOR DISPLAY
$sqlDetails = "
    SELECT s.STUD_FNAME, s.STUD_LNAME, e.ENR_YR, p.PROG_NAME
    FROM STUDENT s
    JOIN ENROLLMENT e ON s.STUD_ID = e.STUD_ID
    JOIN PROGRAM p ON e.PROG_ID = p.PROG_ID
    WHERE s.STUD_ID = ?
    ORDER BY e.ENR_DATE DESC
    LIMIT 1
";
$stmt = $conn->prepare($sqlDetails);
$stmt->bind_param("s", $studId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $fname     = $row['STUD_FNAME'];
    $lname     = $row['STUD_LNAME'];
    $yearLevel = $row['ENR_YR'];
    $progName  = $row['PROG_NAME'];
} else {
    die("Error: No student record found for ID $studId");
}

//AFTER INSERTION UPDATE SECTION NUMBER OF STUDENTS

// Recalculate SEC_NUM_STUD based on actual enrolled students
$sqlUpdateSec = "UPDATE SECTION s
                 SET s.SEC_NUM_STUD = (
                     SELECT COUNT(*)
                     FROM ENROLLMENT e
                     WHERE e.SEC_ID = s.SEC_ID
                       AND e.ENR_STAT = 'ENROLLED'
                 )
                 WHERE s.SEC_ID = ?";
$stmt = $conn->prepare($sqlUpdateSec);
$stmt->bind_param("s", $secId);
$stmt->execute();






$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT CREDENTIAL PAGE</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #4CAF50;
            font-weight: 600;
        }
        .credential {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.5;
            transition: border-color 0.3s ease;
        }
        .credential:hover {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }
        .credential strong {
            color: #555;
            font-weight: 500;
        }

        .submit-buttons {
            display: flex;
            justify-content: space-between; /* pushes one form left, the other right */
            margin-top: 20px;
        }

        .submit-buttons form button,
        .submit-buttons form input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            height: 40px;
            font-size: 16px;
            font-weight: 600;
        }

    </style>
</head>
<body>
    <div class="container">
    <h2>Student Credentials</h2>
    <div class="credential">
        <strong>Student ID:</strong> <?php echo htmlspecialchars($studId); ?>
    </div>
    <div class="credential">
        <strong>Name:</strong> <?php echo htmlspecialchars($fname . " " . $lname); ?>
    </div>
    <div class="credential">
        <strong>Year Level:</strong> <?php echo htmlspecialchars($yearLevel); ?>
    </div>
    <div class="credential">
        <strong>Program:</strong> <?php echo htmlspecialchars($progName); ?>
    </div>

    <div class="submit-buttons">
        <form action="StudentEnrollmentForm.html" method="get">
            <button type="submit">
                Back to Enrollment Form
            </button>
        </form>

        <form action="login.html" method="post">
            <input type="submit" value="Go to Student Dashboard">
        </form>
    </div>
</div>

</body>
</html>

