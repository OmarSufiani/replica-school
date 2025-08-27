<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

$role = $_SESSION['role'] ?? '';
$school_id = $_SESSION['school_id'] ?? null;

// ✅ Count helpers
function countUsersByRole($conn, $role, $school_id = null) {
    if ($school_id !== null) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role=? AND school_id=?");
        $stmt->bind_param("si", $role, $school_id);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role=?");
        $stmt->bind_param("s", $role);
    }
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res['count'] ?? 0;
}

function countTable($conn, $table, $school_id = null) {
    if ($school_id !== null) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table WHERE school_id=?");
        $stmt->bind_param("i", $school_id);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table");
    }
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res['count'] ?? 0;
}

// ✅ Scope
$scopeSchool = ($role === 'Superadmin') ? null : $school_id;

// ✅ Counts
$totalAdmins   = countUsersByRole($conn, 'admin', $scopeSchool);
$totalDeans    = countUsersByRole($conn, 'dean', $scopeSchool);
$totalTeachers = countUsersByRole($conn, 'teacher', $scopeSchool);
$totalUsers    = countUsersByRole($conn, 'user', $scopeSchool);
$totalStudents = countTable($conn, 'student', $scopeSchool);
$totalClasses  = countTable($conn, 'class', $scopeSchool);
$totalSchools  = countTable($conn, 'school'); // superadmin sees all
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <title>Document</title>
</head>
<body>
    
</body>
</html>
<div class="container mt-4">
    <h2 class="mb-4">📊 Dashboard Overview</h2>
    <div class="row">

        <?php if (in_array($role, ['Superadmin','admin'])): ?>
            <!-- Admins -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success shadow">
                    <div class="card-body">
                        <h5 class="card-title">Admins</h5>
                        <h2><?= $totalAdmins ?></h2>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Superadmin','admin','dean'])): ?>
            <!-- Deans -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning shadow">
                    <div class="card-body">
                        <h5 class="card-title">Deans</h5>
                        <h2><?= $totalDeans ?></h2>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Superadmin','admin','dean','teacher'])): ?>
            <!-- Teachers -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info shadow">
                    <div class="card-body">
                        <h5 class="card-title">Teachers</h5>
                        <h2><?= $totalTeachers ?></h2>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Superadmin','admin','dean','teacher'])): ?>
            <!-- Students -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-danger shadow">
                    <div class="card-body">
                        <h5 class="card-title">Students</h5>
                        <h2><?= $totalStudents ?></h2>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Superadmin','admin','dean','teacher','user'])): ?>
            <!-- Users -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary shadow">
                    <div class="card-body">
                        <h5 class="card-title">Normal User</h5>
                        <h2><?= $totalUsers ?></h2>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Superadmin','admin'])): ?>
            <!-- Classes -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-secondary shadow">
                    <div class="card-body">
                        <h5 class="card-title">Classes</h5>
                        <h2><?= $totalClasses ?></h2>
                    </div>
                </div>
            </div>

            <!-- Schools -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-dark shadow">
                    <div class="card-body">
                        <h5 class="card-title">Schools</h5>
                        <h2><?= $totalSchools ?></h2>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>