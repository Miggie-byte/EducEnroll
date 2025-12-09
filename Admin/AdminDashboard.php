<?php
$conn = new mysqli("localhost:3310", "root", "Dj@070821", "EducEnroll");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get selected table from URL
$table = $_GET['table'] ?? '';

$allowed = ['STUDENT','ENROLLMENT','PROGRAM','SUBJECT','TEACHING_ASSIGNMENT','SECTION','INSTRUCTOR'];

if ($table && in_array($table, $allowed)) {
    $sql = "SELECT * FROM `$table`";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            color: #333;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 1100px;
            width: 100%;
            margin: 30px;
        }

        h2 {
            margin-bottom: 30px;
            color: #4CAF50;
            font-weight: 600;
            text-align: center;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .dashboard-item {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            text-align: center;
        }

        .dashboard-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .dashboard-item h3 {
            margin: 0;
            color: #4CAF50;
            font-size: 18px;
        }

        .dashboard-item p {
            margin: 10px 0 0 0;
            color: #777;
            font-size: 14px;
        }

        .nav-panel {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .nav-item {
            padding: 10px 15px;
            background: #f2f2f2;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            color: #333;
        }

        .nav-item:hover,
        .nav-item.active {
            background: #4CAF50;
            color: white;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        tr:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>

        <!-- Dashboard shortcuts -->
        <div class="dashboard-grid">
            <div class="dashboard-item" onclick="window.location.href='../AdminFunctions/add_instructor.php'">
                <h3>Add an Instructor</h3>
                <p>Add a new Instructor to the system.</p>
            </div>
            <div class="dashboard-item" onclick="window.location.href='../AdminFunctions/add_subject.php'">
                <h3>Add Subject</h3>
                <p>Create a new subject.</p>
            </div>
            <div class="dashboard-item" onclick="window.location.href='../AdminFunctions/add_teachingassign.php'">
                <h3>Add Teaching Assignment</h3>
                <p>Assign teaching roles.</p>
            </div>
            <div class="dashboard-item" onclick="window.location.href='../AdminFunctions/add_program.php'">
                <h3>Add Program</h3>
                <p>Set up a new program.</p>
            </div>

            <div class="dashboard-item" onclick="window.location.href='../AdminFunctions/update_studentstat.php'">
                <h3>Update Student Status</h3>
                <p>Update a Student's Enrollment Status</p>
            </div>

            <div class="dashboard-item" onclick="window.location.href='../AdminFunctions/delete_teachingassign.php'">
                <h3>Delete Teaching Assignment</h3>
                <p>Delete a teaching assignment</p>
            </div>
        </div>

        <!-- Nav panel -->
        <div class="nav-panel">
            <a class="nav-item <?= ($table=='STUDENT')?'active':'' ?>" href="?table=STUDENT">STUDENT</a>
            <a class="nav-item <?= ($table=='ENROLLMENT')?'active':'' ?>" href="?table=ENROLLMENT">ENROLLMENT</a>
            <a class="nav-item <?= ($table=='PROGRAM')?'active':'' ?>" href="?table=PROGRAM">PROGRAM</a>
            <a class="nav-item <?= ($table=='SUBJECT')?'active':'' ?>" href="?table=SUBJECT">SUBJECT</a>
            <a class="nav-item <?= ($table=='TEACHING_ASSIGNMENT')?'active':'' ?>" href="?table=TEACHING_ASSIGNMENT">TEACHING ASSIGNMENT</a>
            <a class="nav-item <?= ($table=='SECTION')?'active':'' ?>" href="?table=SECTION">SECTION</a>
            <a class="nav-item <?= ($table=='INSTRUCTOR')?'active':'' ?>" href="?table=INSTRUCTOR">INSTRUCTOR</a>
        </div>

        <!-- Table output -->
        <div class="table-container">
            <?php if (!empty($table)): ?>
                <?php if ($result && $result->num_rows > 0): ?>
                    <table>
                        <tr>
                            <?php while ($field = $result->fetch_field()): ?>
                                <th><?= htmlspecialchars($field->name) ?></th>
                            <?php endwhile; ?>
                        </tr>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <?php foreach ($row as $val): ?>
                                    <td><?= htmlspecialchars($val) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p>No records found in <?= htmlspecialchars($table) ?>.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Select a table above to view its records.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
