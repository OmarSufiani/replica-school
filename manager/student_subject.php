<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$success = '';
$error = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Fetch students for this school
    $students = mysqli_query($conn,
        "SELECT id, firstname, lastname, admno FROM student WHERE school_id = $school_id"
    );

    // Fetch optional subjects only for this school
    $subjects = mysqli_query($conn,
        "SELECT id, name FROM subject WHERE school_id = $school_id AND is_compulsory = 0"
    );

    // Fetch classes for this school
    $classes = mysqli_query($conn,
        "SELECT id, name FROM class WHERE school_id = $school_id ORDER BY name"
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['student_id'], $_POST['subject_ids'], $_POST['class_id'])) {

        $student_id = intval($_POST['student_id']);
        $class_id = intval($_POST['class_id']);
        $selected_subjects = $_POST['subject_ids'];
        $assigned_count = 0;
        $skipped_count = 0;

        foreach ($selected_subjects as $subject_id) {
            $subject_id = intval($subject_id);

            // Check if assignment already exists for this class
            $check = $conn->prepare("SELECT id FROM student_subject WHERE student_id=? AND subject_id=? AND class_id=? AND school_id=?");
            $check->bind_param("iiii", $student_id, $subject_id, $class_id, $school_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $skipped_count++;
            } else {
                $sql = "INSERT INTO student_subject (student_id, school_id, subject_id, class_id) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiii", $student_id, $school_id, $subject_id, $class_id);
                $stmt->execute();
                $stmt->close();
                $assigned_count++;
            }
            $check->close();
        }

        if ($assigned_count > 0) {
            $success = "✅ Assigned $assigned_count subject(s) successfully.";
        }
        if ($skipped_count > 0) {
            $error .= " ⚠ $skipped_count subject(s) were already assigned to this class.";
        }

        $_SESSION['message'] = trim($success . ' ' . $error);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

} catch (mysqli_sql_exception $e) {
    $_SESSION['message'] = "❌ Error: " . htmlspecialchars($e->getMessage());
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Show message from session
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Assign Optional Subjects to Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script>
      document.addEventListener("DOMContentLoaded", function () {
          const alertBox = document.getElementById("message-box");
          if (alertBox) {
              setTimeout(() => alertBox.style.opacity = "0", 2500);
              setTimeout(() => alertBox.remove(), 3000);
          }
      });
  </script>
</head>
<body class="bg-light">

<div class="container mt-5">
  <a href="dashboard.php" class="btn btn-outline-primary mb-4">&larr; Back to Dashboard</a>
  
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-4 text-primary">Assign Optional Subjects to Student</h4>

      <?php if ($message): ?>
        <div id="message-box" class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-warning' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <!-- Student Dropdown -->
        <div class="mb-3">
          <label for="student_id" class="form-label">Student</label>
          <select id="student_id" name="student_id" class="form-select" required>
            <option value="">Select Student</option>
            <?php mysqli_data_seek($students, 0); while ($row = mysqli_fetch_assoc($students)): ?>
              <option value="<?= $row['id'] ?>">
                <?= htmlspecialchars("{$row['firstname']} {$row['lastname']} (Adm: {$row['admno']})") ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Class Dropdown -->
        <div class="mb-3">
          <label for="class_id" class="form-label">Class</label>
          <select id="class_id" name="class_id" class="form-select" required>
            <option value="">Select Class</option>
            <?php mysqli_data_seek($classes, 0); while ($cls = mysqli_fetch_assoc($classes)): ?>
              <option value="<?= $cls['id'] ?>"><?= htmlspecialchars($cls['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Optional Subjects Checkboxes -->
        <div class="mb-3">
          <label class="form-label">Select Optional Subjects</label><br>
          <?php mysqli_data_seek($subjects, 0); while ($sub = mysqli_fetch_assoc($subjects)): ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?= $sub['id'] ?>" id="sub<?= $sub['id'] ?>">
              <label class="form-check-label" for="sub<?= $sub['id'] ?>">
                <?= htmlspecialchars($sub['name']) ?>
              </label>
            </div>
          <?php endwhile; ?>
        </div>
        
        <button type="submit" class="btn btn-success w-100">Assign Selected Optional Subjects</button>
      </form>
      
    </div>
  </div>
</div>

</body>
</html>
