<?php
session_start();
include 'db.php';

// Only admin or dean can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','dean'])) {
    die("<div class='alert alert-danger'>Unauthorized access</div>");
}

$message = "";
$promoted_list = [];
$graduated_list = [];

$current_year = date("Y");
$school_id    = $_SESSION['school_id'];

// ✅ Check if promotion already done this year
$check_sql = "SELECT id FROM year_log WHERE school_id=? AND year=?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $school_id, $current_year);
$check_stmt->execute();
$check_res = $check_stmt->get_result();

if ($check_res->num_rows > 0) {
    // Already done this year
    $message = "<div class='alert alert-warning mt-3'>⚠️ Promotion for $current_year already completed.</div>";
} else {
    // Run automatic promotions
    $sql = "SELECT s.id, s.firstname, s.lastname, s.class_id, c.name as class_name 
            FROM student s 
            JOIN class c ON s.class_id=c.id 
            WHERE s.school_id=? AND s.status='active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($student = $result->fetch_assoc()) {
        $student_id   = $student['id'];
        $class_id     = $student['class_id'];
        $class_name   = $student['class_name'];
        $student_name = $student['firstname'] . ' ' . $student['lastname'];

        // Parse class name (e.g. "8B", "9Blue")
        if (preg_match('/(\d+)([A-Za-z]*)/', $class_name, $matches)) {
            $class_number = intval($matches[1]);
            $class_suffix = $matches[2];
            $next_class_name = ($class_number + 1) . $class_suffix;

            // Find next class
            $nc_sql = "SELECT id FROM class WHERE name=? AND school_id=?";
            $nc_stmt = $conn->prepare($nc_sql);
            $nc_stmt->bind_param("si", $next_class_name, $school_id);
            $nc_stmt->execute();
            $nc_result = $nc_stmt->get_result();

            if ($nc_result->num_rows > 0) {
                $next_class = $nc_result->fetch_assoc();
                $next_class_id = $next_class['id'];

                // Update student class
                $update_sql = "UPDATE student SET class_id=? WHERE id=?";
                $u_stmt = $conn->prepare($update_sql);
                $u_stmt->bind_param("ii", $next_class_id, $student_id);
                $u_stmt->execute();

                // Copy subjects (from last year to this year)
                $prev_year = $current_year - 1;
                $sub_sql = "SELECT subject_id FROM student_subject 
                            WHERE student_id=? AND class_id=? AND year=?";
                $sub_stmt = $conn->prepare($sub_sql);
                $sub_stmt->bind_param("iii", $student_id, $class_id, $prev_year);
                $sub_stmt->execute();
                $sub_result = $sub_stmt->get_result();

                while ($sub = $sub_result->fetch_assoc()) {
                    $subject_id = $sub['subject_id'];

                    $chk_sql = "SELECT id FROM student_subject 
                                WHERE student_id=? AND subject_id=? AND class_id=? AND year=?";
                    $chk_stmt = $conn->prepare($chk_sql);
                    $chk_stmt->bind_param("iiii", $student_id, $subject_id, $next_class_id, $current_year);
                    $chk_stmt->execute();
                    $chk_res = $chk_stmt->get_result();

                    if ($chk_res->num_rows == 0) {
                        $ins_sql = "INSERT INTO student_subject (student_id, subject_id, class_id, year) 
                                    VALUES (?,?,?,?)";
                        $ins_stmt = $conn->prepare($ins_sql);
                        $ins_stmt->bind_param("iiii", $student_id, $subject_id, $next_class_id, $current_year);
                        $ins_stmt->execute();
                    }
                }

                $promoted_list[] = [
                    'name' => $student_name,
                    'old_class' => $class_name,
                    'new_class' => $next_class_name
                ];

            } else {
                // No next class → Graduate student
                $grad_sql = "UPDATE student SET status='graduated' WHERE id=?";
                $grad_stmt = $conn->prepare($grad_sql);
                $grad_stmt->bind_param("i", $student_id);
                $grad_stmt->execute();

                $graduated_list[] = $student_name;
            }
        }
    }

    // ✅ Log this promotion year safely (prevent duplicates)
    $log_sql = "INSERT INTO year_log (school_id, year) VALUES (?,?)";
    $log_stmt = $conn->prepare($log_sql);
    if ($log_stmt) {
        $log_stmt->bind_param("ii", $school_id, $current_year);
        if ($log_stmt->execute()) {
            $message = "<div class='alert alert-success mt-3'>✅ Automatic promotion and graduation done for $current_year.</div>";
        } else {
            // Duplicate key → already logged
            $message = "<div class='alert alert-warning mt-3'>⚠️ Promotion for $current_year already exists (no duplicate created).</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Promotion Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Dashboard</a>

    <h2 class="mb-4">Promotion Tracking for <?= $current_year ?></h2>
    <?= $message ?>

    <!-- ✅ Promotion history form -->
    <h4 class="mt-4">Check Promotion History</h4>
    <form method="get" class="mb-3">
        <label for="year" class="form-label">Select Year:</label>
        <input type="number" name="year" id="year" class="form-control w-25 d-inline" value="<?= $current_year ?>" min="2000" max="2100">
        <button type="submit" class="btn btn-primary">View</button>
    </form>

    <?php
    if (isset($_GET['year'])) {
        $view_year = intval($_GET['year']);
        $hist_sql = "SELECT * FROM year_log WHERE school_id=? AND year=?";
        $hist_stmt = $conn->prepare($hist_sql);
        $hist_stmt->bind_param("ii", $school_id, $view_year);
        $hist_stmt->execute();
        $hist_res = $hist_stmt->get_result();

        if ($hist_res->num_rows > 0) {
            echo "<div class='alert alert-success'>✅ Promotion was done for year $view_year.</div>";
            echo "<table class='table table-bordered'>
                    <thead><tr><th>Year</th><th>Done At</th></tr></thead><tbody>";
            while ($row = $hist_res->fetch_assoc()) {
                echo "<tr><td>{$row['year']}</td><td>{$row['done_at']}</td></tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<div class='alert alert-danger'>❌ No promotion found for year $view_year.</div>";
        }
    }
    ?>
</div>
</body>
</html>
