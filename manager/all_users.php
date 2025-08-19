<?php
session_start();
include 'db.php';

// Ensure user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$school_id = $_SESSION['school_id'];

// --- Fetch Teachers ---
$teachers = $conn->prepare("SELECT id, name, enrolment_no FROM teacher WHERE school_id=?");
$teachers->bind_param("i", $school_id);
$teachers->execute();
$teachers_result = $teachers->get_result();

// --- Fetch Users ---
$users = $conn->prepare("SELECT id, FirstName, LastName, email, role FROM users WHERE school_id=?");
$users->bind_param("i", $school_id);
$users->execute();
$users_result = $users->get_result();

// --- Fetch Students with class name ---
$students = $conn->prepare("
    SELECT s.id, s.firstname, s.lastname, s.admno, c.name AS class_name
    FROM student s
    LEFT JOIN class c ON s.class_id = c.id
    WHERE s.school_id=?
");
$students->bind_param("i", $school_id);
$students->execute();
$students_result = $students->get_result();

// --- Fetch Classes ---
$classes = $conn->prepare("SELECT id, name FROM class WHERE school_id=?");
$classes->bind_param("i", $school_id);
$classes->execute();
$classes_result = $classes->get_result();
?>
<!DOCTYPE html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

 <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>
    <h2 class="mb-4 text-center">📊 School Dashboard</h2>

    <!-- Teachers -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">👨‍🏫 Teachers</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Enrolment No</th></tr>
                </thead>
                <tbody>
                <?php while($row = $teachers_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['enrolment_no']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Users -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">👥 Users</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>
                </thead>
                <tbody>
                <?php while($row = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['FirstName'].' '.$row['LastName']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['role']) ?></span></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Students -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">🎓 Students</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Adm No</th><th>Class</th></tr>
                </thead>
                <tbody>
                <?php while($row = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></td>
                        <td><?= htmlspecialchars($row['admno']) ?></td>
                        <td><?= htmlspecialchars($row['class_name'] ?? 'N/A') ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Classes -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">🏫 Classes</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr><th>ID</th><th>Class Name</th></tr>
                </thead>
                <tbody>
                <?php while($row = $classes_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
