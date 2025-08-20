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

// Promote students
if (isset($_POST['promote'])) {
    $school_id   = $_SESSION['school_id'];
    $current_year = date("Y");
    $new_year     = $current_year + 1;

    $_SESSION['promoted_students'] = [];
    $_SESSION['graduated_students'] = [];

    $sql = "SELECT s.id, s.firstname, s.lastname, s.class_id, c.name as class_name 
            FROM student s 
            JOIN `class` c ON s.class_id=c.id 
            WHERE s.school_id=? AND s.status='active'";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Prepare failed: " . $conn->error); }
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($student = $result->fetch_assoc()) {
        $student_id = $student['id'];
        $class_id   = $student['class_id'];
        $class_name = $student['class_name'];
        $student_name = $student['firstname'] . ' ' . $student['lastname'];

        // Parse class name to increment number but keep suffix
        if (preg_match('/(\d+)([A-Z]*)/i', $class_name, $matches)) {
            $class_number = intval($matches[1]);
            $class_suffix = isset($matches[2]) ? $matches[2] : '';
            $next_class_name = ($class_number + 1) . $class_suffix;

            // Find next class by name
            $next_class_sql = "SELECT id, name FROM `class` WHERE name=? AND school_id=?";
            $nc_stmt = $conn->prepare($next_class_sql);
            if (!$nc_stmt) { die("Prepare failed: " . $conn->error); }
            $nc_stmt->bind_param("si", $next_class_name, $school_id);
            $nc_stmt->execute();
            $nc_result = $nc_stmt->get_result();

            if ($nc_result->num_rows > 0) {
                $next_class = $nc_result->fetch_assoc();
                $next_class_id = $next_class['id'];
                $next_class_name = $next_class['name'];

                // Update class
                $update_class_sql = "UPDATE student SET class_id=? WHERE id=?";
                $uc_stmt = $conn->prepare($update_class_sql);
                $uc_stmt->bind_param("ii", $next_class_id, $student_id);
                $uc_stmt->execute();

                // Copy subjects
                $sub_sql = "SELECT subject_id FROM student_subject WHERE student_id=? AND class_id=? AND year=?";
                $sub_stmt = $conn->prepare($sub_sql);
                $sub_stmt->bind_param("iii", $student_id, $class_id, $current_year);
                $sub_stmt->execute();
                $sub_result = $sub_stmt->get_result();

                while ($sub = $sub_result->fetch_assoc()) {
                    $subject_id = $sub['subject_id'];

                    $check_sql = "SELECT id FROM student_subject WHERE student_id=? AND subject_id=? AND class_id=? AND year=?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("iiii", $student_id, $subject_id, $next_class_id, $new_year);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows == 0) {
                        $insert_sql = "INSERT INTO student_subject (student_id, subject_id, class_id, year) VALUES (?,?,?,?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        $insert_stmt->bind_param("iiii", $student_id, $subject_id, $next_class_id, $new_year);
                        $insert_stmt->execute();
                    }
                }

                $_SESSION['promoted_students'][] = ['id' => $student_id, 'old_class' => $class_id];
                $promoted_list[] = [
                    'name' => $student_name,
                    'old_class' => $class_name,
                    'new_class' => $next_class_name
                ];

            } else {
                // No next class → graduate
                $grad_sql = "UPDATE student SET status='graduated' WHERE id=?";
                $grad_stmt = $conn->prepare($grad_sql);
                $grad_stmt->bind_param("i", $student_id);
                $grad_stmt->execute();

                $_SESSION['graduated_students'][] = $student_id;
                $graduated_list[] = $student_name;
            }

        } else {
            // Invalid class name → graduate
            $grad_sql = "UPDATE student SET status='graduated' WHERE id=?";
            $grad_stmt = $conn->prepare($grad_sql);
            $grad_stmt->bind_param("i", $student_id);
            $grad_stmt->execute();

            $_SESSION['graduated_students'][] = $student_id;
            $graduated_list[] = $student_name;
        }
    }

    $message = "<div class='alert alert-success mt-3'>🎓 Promotion complete!</div>";
}

// Cancel promotion
if (isset($_POST['cancel'])) {
    if (!empty($_SESSION['promoted_students'])) {
        foreach ($_SESSION['promoted_students'] as $student) {
            $reset_sql = "UPDATE student SET class_id=? WHERE id=?";
            $reset_stmt = $conn->prepare($reset_sql);
            $reset_stmt->bind_param("ii", $student['old_class'], $student['id']);
            $reset_stmt->execute();
        }
        unset($_SESSION['promoted_students']);
    }

    if (!empty($_SESSION['graduated_students'])) {
        $grad_ids = implode(",", array_map('intval', $_SESSION['graduated_students']));
        $reset_grad_sql = "UPDATE student SET status='active' WHERE id IN ($grad_ids)";
        $conn->query($reset_grad_sql);
        unset($_SESSION['graduated_students']);
    }

    $message = "<div class='alert alert-warning mt-3'>❌ Promotion cancelled. All changes reverted.</div>";
}
?>

<!DOCTYPE html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Promote Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>

    <h2 class="mb-4">Student Promotion</h2>

    <form method="post" class="mb-3">
        <button type="submit" name="promote" class="btn btn-primary me-2">Promote Students to Next Year</button>
        <button type="submit" name="cancel" class="btn btn-danger">Cancel Promotion</button>
    </form>

    <?php
    if (!empty($message)) echo $message;

    if (!empty($promoted_list)) {
        echo "<h4>Promoted Students</h4>
              <table class='table table-bordered'>
                  <thead><tr><th>Name</th><th>Old Class</th><th>New Class</th></tr></thead><tbody>";
        foreach ($promoted_list as $p) {
            echo "<tr>
                    <td>{$p['name']}</td>
                    <td>{$p['old_class']}</td>
                    <td>{$p['new_class']}</td>
                  </tr>";
        }
        echo "</tbody></table>";
    }

    if (!empty($graduated_list)) {
        echo "<h4>Graduated Students</h4><ul>";
        foreach ($graduated_list as $g) {
            echo "<li>$g</li>";
        }
        echo "</ul>";
    }
    ?>
</div>
</body>
</html>
