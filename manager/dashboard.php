<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Flash messages
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']); // clear after showing

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch user's school name
$schoolName = "";
if (!empty($user['school_id'])) {
    $stmt2 = $conn->prepare("SELECT school_name FROM school WHERE id = ?");
    $stmt2->bind_param("i", $user['school_id']);
    $stmt2->execute();
    $stmt2->bind_result($schoolName);
    $stmt2->fetch();
    $stmt2->close();
}

// Decide which page to include
$page = $_GET['page'] ?? 'partials/dashboard_content.php';
if (!str_contains($page, ".php")) {
    $page .= ".php";
}
$file = __DIR__ . "/" . ltrim($page, "/");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Ramzy School System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* Sidebar style */
    #sidebarMenu {
      min-width: 250px;
      max-width: 250px;
      background-color: #000; /* black sidebar */
    }
    #sidebarMenu .nav-link {
      color: #ccc;
      border-radius: 4px;
    }
    #sidebarMenu .nav-link:hover,
    #sidebarMenu .nav-link.active {
      background-color: #495057;
      color: #fff;
    }
  </style>
</head>
<body class="bg-light d-flex flex-column vh-100">

<!-- Top Navbar -->
<nav class="navbar navbar-dark bg-dark sticky-top shadow">
  <div class="container-fluid">
    <!-- Sidebar Toggle (mobile only) -->
    <button class="btn btn-outline-light me-2 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
      ☰
    </button>

    <!-- School Name -->
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <?= htmlspecialchars($schoolName ?: "No School Assigned") ?>
    </a>

    <!-- Right Side -->
    <div class="d-flex align-items-center ms-auto">
      <span class="text-white me-3">
        <?= htmlspecialchars($user['FirstName'] . " " . $user['LastName']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)
      </span>
      <a href="../logout.php" class="btn btn-sm btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse show vh-100">
      <div class="position-sticky pt-3">
        <ul class="nav flex-column mb-auto" id="menuLinks">
          <li>
            <a class="nav-link text-white <?= (!isset($_GET['page']) || $_GET['page'] === 'dashboard_content') ? 'active' : '' ?>" 
               href="dashboard.php?page=dashboard_content">🏠 Dashboard</a>
          </li>
          <?php if ($_SESSION['role'] === 'Superadmin'): ?>
            <li><a href="dashboard.php?page=add_school" class="nav-link">➕ Add School</a></li>
            <li><a href="dashboard.php?page=manage_users" class="nav-link">👤 Manage Admins</a></li>
            <li><a href="dashboard.php?page=manage_schools" class="nav-link">🏫 Disable School</a></li>
            <li><a href="dashboard.php?page=update_user" class="nav-link">⚙️ Settings</a></li>
          <?php endif; ?>

          <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="dashboard.php?page=all_users" class="nav-link">📊 Statistics</a></li>
            <li><a href="dashboard.php?page=csv" class="nav-link">📑 School Report</a></li>
            <li><a href="dashboard.php?page=active_students" class="nav-link">🔄 Student Promotion</a></li>
            <li><a href="dashboard.php?page=manage_users" class="nav-link">👤 Manage Users</a></li>
            <li><a href="dashboard.php?page=edit_student" class="nav-link">✏️ Edit Student</a></li>
            <li><a href="dashboard.php?page=update_user" class="nav-link">⚙️ Settings</a></li>
          <?php endif; ?>

          <?php if ($_SESSION['role'] === 'dean'): ?>
            <li><a href="dashboard.php?page=add_class" class="nav-link">➕ Add Class</a></li>
            <li><a href="dashboard.php?page=add_subject" class="nav-link">📚 Add Subject</a></li>
            <li><a href="dashboard.php?page=add_student" class="nav-link">👩‍🎓 Add Student</a></li>
            <li><a href="dashboard.php?page=student_subject" class="nav-link">📘 Student Subjects</a></li>
            <li><a href="dashboard.php?page=view_students" class="nav-link">👀 View Students</a></li>
            <li><a href="dashboard.php?page=add_teacher" class="nav-link">👨‍🏫 Add Teacher</a></li>
            <li><a href="dashboard.php?page=tsubject_class" class="nav-link">📖 Teacher Subject/Class</a></li>
            <li><a href="dashboard.php?page=csv" class="nav-link">📑 View Report</a></li>
            <li><a href="dashboard.php?page=view_exams" class="nav-link">📝 Exams</a></li>
            <li><a href="dashboard.php?page=report_form" class="nav-link">📄 Report Form</a></li>
            <li><a href="dashboard.php?page=manage_teachers" class="nav-link">👨‍🏫 Manage Teachers</a></li>
            <li><a href="dashboard.php?page=manage_users" class="nav-link">👤 Manage Users</a></li>
            <li><a href="dashboard.php?page=update_user" class="nav-link">⚙️ Settings</a></li>
          <?php endif; ?>

          <?php if ($_SESSION['role'] === 'teacher'): ?>
            <li><a href="dashboard.php?page=add_score" class="nav-link">➕ Add Score</a></li>
            <li><a href="dashboard.php?page=view_results" class="nav-link">📊 View Results</a></li>
            <li><a href="dashboard.php?page=report_form" class="nav-link">📄 Report Form</a></li>
            <li><a href="dashboard.php?page=upload_exam" class="nav-link">⬆️ Upload Exam</a></li>
            <li><a href="dashboard.php?page=view_exams" class="nav-link">📝 Exams</a></li>
            <li><a href="dashboard.php?page=update_user" class="nav-link">⚙️ Settings</a></li>
          <?php endif; ?>

          <?php if ($_SESSION['role'] === 'user'): ?>
            <li><a href="dashboard.php?page=report_form" class="nav-link">📄 Report Form</a></li>
            <li><a href="dashboard.php?page=update_user" class="nav-link">⚙️ Settings</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </nav>
<main class="flex-grow-1 p-4 col-md-9 ms-sm-auto col-lg-10">
    <?php
    $page = $_GET['page'] ?? 'dashboard_content';
    $allowed_pages = [
    'dashboard_content',
        'manage_users',
        'all_users',
        'csv',
        'edit_student',
        'active_students',
         'update_user',
         'add_score',
         'view_results',
         'report_form',
         'upload_exam',
         'view_exams',
         'add_school',
         'manage_schools',
         'add_class',
         'add_subject',
         'add_student',
         'student_subject',
         'view_students',
         'add_teacher',
         'tsubject_class',
         'manage_teachers',

       
        // add all other pages here
    ];

    if (in_array($page, $allowed_pages)) {
        include $page . '.php';
    } else {
        echo '<div class="alert alert-danger">Page not found.</div>';
    }
    ?>
</main>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
