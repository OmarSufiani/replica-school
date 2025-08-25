<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Ramzy School System</title>
    <!-- ✅ Bootstrap 5 CDN -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Makes it mobile-friendly -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Bigger buttons for touch */
        .btn {
            font-size: 1.2rem;
            padding: 15px;
        }
        /* Card stretches for mobile */
        .card {
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="card mx-auto shadow-lg">
        <div class="card-body text-center">
            <!-- Small Bootstrap Back Button -->
            <div class="text-start mb-3">
                            <div class="d-flex justify-content-end mb-3">
                    <a href="../logout.php" class="btn btn-outline-primary btn-sm">
                        &larr; Logout
                    </a>
                </div>

            </div>

            <h2 class="mb-4">📘 School Dashboard</h2>
        

                <!-- Show logged-in user -->
                <p class="text-muted mb-4">
                    Logged in as: <strong>
                        <?= htmlspecialchars($_SESSION['FirstName'] . ' ' . $_SESSION['LastName']) ?>
                    </strong> 
                    (Role: <?= htmlspecialchars($_SESSION['role']) ?>)
                </p>

                <br>

            <div class="d-grid gap-3">
            <?php if ($_SESSION['role'] === 'Superadmin'): ?>
                    <a class="btn btn-primary" href="add_school.php">➕ Add School</a>
                    <a class="btn btn-primary" href="manage_users.php">➕ Manage Admins</a>
                
                     <a class="btn btn-primary" href="manage_schools.php">➕ Desable School</a>
                    <a class="btn btn-secondary" href="update_user.php">⚙️ Settings</a>
                <?php endif; ?>



                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a class="btn btn-primary" href="all_users.php">➕ Statistics</a>
                     <a class="btn btn-primary" href="csv.php">📊 View  School Report</a>
                    <a class="btn btn-primary" href="active_students.php">➕ Students Subject Auto Assign to Next Year</a>
                     <a class="btn btn-primary" href="manage_users.php">➕ Manage_users</a>
                   
                    <a class="btn btn-primary" href="edit_student.php">➕ Edit Student</a>
                     <a class="btn btn-secondary" href="update_user.php">⚙️ Settings</a>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'dean'): ?>
                    <a class="btn btn-primary" href="add_class.php">➕ Add Class</a>
                    <a class="btn btn-primary" href="add_subject.php">➕ Add Subjects</a>
                    <a class="btn btn-primary" href="add_student.php">➕ Add Student</a>
                    <a class="btn btn-primary" href="student_subject.php">📚 Add Student_Subject_Class</a>
                    <a class="btn btn-primary" href="view_students.php">➕ View Students</a>
                    <a class="btn btn-primary" href="add_teacher.php">➕ Add Teacher</a>
                    <a class="btn btn-primary" href="tsubject_class.php">➕ Teacher Subject/Class</a>
                    <a class="btn btn-primary" href="add_classteacher.php">➕ Assign Class Teacher</a>
                    <a class="btn btn-primary" href="delete_student.php">➕ View Students </a>
                     <a class="btn btn-primary" href="delete_scores.php">➕ Manage Scores </a>
                    <a class="btn btn-primary" href="file.php">➕ All files</a>
                    <a class="btn btn-primary" href="csv.php">📊 View Report</a>
                     <a class="btn btn-primary" href="view_exams.php">➕ Exams</a>
                <a class="btn btn-primary" href="report_form.php">📄 Download Report Form</a>
                <a class="btn btn-primary" href="active_students.php">➕ Check Students Promotion Status</a>
                    <a class="btn btn-primary" href="edit_student.php">➕ Edit Student</a>
              
                <a class="btn btn-primary" href="manage_teachers.php">➕ Manage_Teachers</a>
                 <a class="btn btn-primary" href="manage_users.php">➕ Manage_users</a>
                   <a class="btn btn-secondary" href="update_user.php">⚙️ Settings</a>

                <?php endif; ?>

                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                    <a class="btn btn-primary mb-3" href="add_score.php">➕ Add Score</a>
                  <a class="btn btn-primary mb-3" href="view_results.php">➕ View Results</a>
                <a class="btn btn-primary" href="report_form.php">📄 Download Report Form</a>
                <a class="btn btn-primary" href="upload_exam.php">➕ Upload Exam</a>
                    <a class="btn btn-primary" href="view_exams.php">➕ Exams</a>
                <a class="btn btn-secondary" href="update_user.php">⚙️ Settings</a>
                

                <?php endif; ?>

                                <!-- These buttons are visible to all users -->
                
                <?php if ($_SESSION['role'] === 'user'): ?>
                
                <a class="btn btn-primary" href="report_form.php">📄 Download Report Form</a>
             <!-- <a class="btn btn-primary mb-3" href="edit_score.php">➕ Edit Score</a>-->
                
                <a class="btn btn-secondary" href="update_user.php">⚙️ Settings</a>
                <?php endif; ?>
                <!-- Settings visible to all -->

                
                
              
            </div>
        </div>
    </div>
</div>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
