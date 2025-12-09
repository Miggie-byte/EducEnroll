<?php
session_start(); // must start session here too

$fname   = $_REQUEST['stud_fname'];
$lname   = $_REQUEST['stud_lname'];
$dob     = $_REQUEST['stud_dob']; // YYYY-MM-DD format
$address = $_REQUEST['stud_add'];
$phone   = $_REQUEST['stud_pnum'];
$gender  = $_REQUEST['stud_gender'];


// Save values into session
$fname = trim($fname);
if ($fname !== "" && preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $fname)) {
    $_SESSION['stud_fname'] = $fname;
} else {
    echo "<script>
            alert('Invalid First name! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}

$lname = trim($lname);
if ($lname !== "" && preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $lname)) {
    $_SESSION['stud_lname'] = $lname;
} else {
    echo "<script>
            alert('Invalid Last name! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}

// Validate DOB format and store in session

if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dob)) {
    echo "<script>
            alert('Invalid date format! Use YYYY-MM-DD.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}

list($year, $month, $day) = explode('-', $dob);

// Validate if the date is real
if (checkdate((int)$month, (int)$day, (int)$year)) {
    $_SESSION['stud_dob'] = $dob;
} else {
    echo "<script>
            alert('Invalid date! Please enter a real date.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}

if ((int)$year < 1900) {
    echo "<script>
            alert('Invalid date! Year must not be earlier than 1900.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}

// Convert DOB to timestamp
$dob_timestamp = strtotime($dob);
$today_timestamp = strtotime(date("Y-m-d"));

// Reject dates greater than today
if ($dob_timestamp > $today_timestamp) {
    echo "<script>
            alert('Invalid date! Date of birth cannot be in the future.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}

$_SESSION['stud_dob'] = $dob;

// Validate address (allow letters, numbers, spaces, commas, periods, apostrophes)
$address = trim($address);

if ($address !== "" && preg_match("/^[a-zA-Z0-9\s,.'\"]+$/", $address)) {
    $_SESSION['stud_add'] = $address;
} else {
    echo "<script>
            alert('Invalid address! Only letters, numbers, spaces, commas, periods, and apostrophes allowed.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}


$_SESSION['stud_pnum']  = $phone;
$_SESSION['stud_gender']= $gender;

// Step 1: Generate STUD_ID (same logic as before)
$currentYear = date("Y");
$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");

$sql = "SELECT MAX(STUD_ID) AS max_id FROM STUDENT WHERE STUD_ID LIKE '$currentYear%'";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $lastId = $row['max_id'];
    $sequence = $lastId ? intval(substr($lastId, 4)) + 1 : 1;
} else {
    $sequence = 1;
}

$newStudId = $currentYear . str_pad($sequence, 6, "0", STR_PAD_LEFT);

// Step 2: Derive password (DOB without dashes)
$password = str_replace("-", "", $dob); // e.g., "20020514"

// Step 3: Derive email (Setup)
$fname_clean = preg_replace('/\s+/', '', $fname);
$lname_clean = preg_replace('/\s+/', '', $lname);

// Step 3: Derive email (firstname.lastname@schoolname.edu.ph)
$email = strtolower($fname_clean . "." . $lname_clean . "@schoolname.edu.ph");

// Step 3.5: Validate before insert
$errors = [];

if (!ctype_digit($phone) || strlen($phone) > 11) {
    echo "<script>
            alert('Invalid phone number! Must be numeric and up to 11 digits.');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    exit();
}

// Step 3.6: Check for duplicate entry before insert
$dupCheck = $conn->prepare("SELECT STUD_ID FROM STUDENT WHERE STUD_FNAME = ? AND STUD_LNAME = ? AND STUD_DOB = ?");
$dupCheck->bind_param("sss", $fname, $lname, $dob);
$dupCheck->execute();
$dupCheck->store_result();

if ($dupCheck->num_rows > 0) {
    // Duplicate found
    echo "<script>
            alert('Duplicate entry detected!');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
    $dupCheck->close();
    exit(); // stop further execution
}
$dupCheck->close();

//Step 4: If there are errors, show warning and skip insert
if (!empty($errors)) {
    echo "<script>
            alert('Warning:\\n" . implode("\\n", $errors) . "');
            window.location.href = 'StudentEnrollmentForm.html';
          </script>";
          exit();
} else {
    // Safe to insert
    $sqlInsert = "INSERT INTO STUDENT 
        (STUD_ID, STUD_FNAME, STUD_LNAME, STUD_DOB, STUD_PASS, STUD_EMAIL, STUD_PNUM, STUD_GENDER, STUD_ADD)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("sssssssss", $newStudId, $fname, $lname, $dob, $password, $email, $phone, $gender, $address);

    if ($stmt->execute()) {
        echo "<script>alert('New student inserted successfully! ID: $newStudId, Email: $email');</script>";
    } else {
        echo "<script>alert('Database error: " . addslashes($stmt->error) . "');</script>";
    }

    $stmt->close();
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


// INSERTION INTO ENROLLMENT TABLE

$sqlEnroll = "INSERT INTO ENROLLMENT (ENR_ID, ENR_DATE, STUD_ID, SEC_ID, ENR_STAT, ENR_YR, PROG_ID)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sqlEnroll);
$stmt->bind_param("sssssss", $newEnrId, $enrDate, $newStudId, $secId, $enrStatus, $yearLevel, $progId);

if ($stmt->execute()) {
} else {
    echo "Error inserting enrollment: " . $conn->error;
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
            <strong>Student ID:</strong> <?php echo htmlspecialchars($newStudId); ?>
        </div>
        <div class="credential">
            <strong>Email:</strong> <?php echo htmlspecialchars($email); ?>
        </div>
        <div class="credential">
            <strong>Password:</strong> <?php echo htmlspecialchars($password); ?>
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

