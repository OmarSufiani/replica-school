<?php
session_start();
include 'db.php'; // your database connection

if (!isset($_SESSION['role'])) {
    die("Unauthorized Access");
}

$role = $_SESSION['role'];

// Build query
if ($role === 'Superadmin') {
    $query = "
        SELECT s.*, sc.school_name, c.name AS class_name
        FROM student s
        LEFT JOIN school sc ON s.school_id = sc.id
        LEFT JOIN student_subject ss ON s.id = ss.student_id
        LEFT JOIN class c ON ss.class_id = c.id
        GROUP BY s.id
        ORDER BY sc.school_name, s.firstname
    ";
} elseif ($role === 'admin') {
    $school_id = $_SESSION['school_id'];
    $query = "
        SELECT s.*, sc.school_name, c.name AS class_name
        FROM student s
        LEFT JOIN school sc ON s.school_id = sc.id
        LEFT JOIN student_subject ss ON s.id = ss.student_id
        LEFT JOIN class c ON ss.class_id = c.id
        WHERE s.school_id = $school_id
        GROUP BY s.id
        ORDER BY s.firstname
    ";
} else {
    die("Unauthorized Access");
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">&larr; Back to Dashboard</a>
    <h2 class="mb-4 text-center">List of Students</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>DOB</th>
                    <th>Guardian Name</th>
                    <th>Guardian Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Admission No</th>
                    <th>School</th>
                    <th>Class</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while ($student = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($student['firstname']) ?></td>
                        <td><?= htmlspecialchars($student['lastname']) ?></td>
                        <td><?= htmlspecialchars($student['gender']) ?></td>
                        <td><?= htmlspecialchars($student['dob']) ?></td>
                        <td><?= htmlspecialchars($student['guardian_name']) ?></td>
                        <td><?= htmlspecialchars($student['guardian_phone']) ?></td>
                        <td><?= htmlspecialchars($student['address']) ?></td>
                        <td><?= htmlspecialchars($student['status']) ?></td>
                        <td><?= htmlspecialchars($student['admno']) ?></td>
                        <td><?= htmlspecialchars($student['school_name']) ?></td>
                        <td><?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
