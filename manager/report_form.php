<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if (isset($_GET['error'])) $error = htmlspecialchars($_GET['error']);
if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

$selected_school_id = $school_id;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Generate Report Card</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-primary">Generate Report Card</h2>

    

    <form method="GET" action="generate_report_card.php" class="card p-4 shadow-sm">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="term" class="form-label">Select Term:</label>
                <select name="term" id="term" class="form-select" required>
                    <option value="Term 1">Term 1</option>
                    <option value="Term 2">Term 2</option>
                    <option value="Term 3">Term 3</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="exam_type" class="form-label">Select Exam Type:</label>
                <select name="exam_type" id="exam_type" class="form-select" required>
                     <option value="CAT">CAT</option>
                    <option value="Mid Term">Mid Term</option>
                    <option value="End Term">End Term</option>
                   
                </select>
            </div>

            <div class="col-md-4">
                <label for="year" class="form-label">Select Year:</label>
                <input type="number" name="year" id="year" class="form-control" value="<?= date('Y'); ?>" required />
            </div>
        </div>

        <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2">Select Report Type</legend>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="student_id" class="form-label">Generate by Student:</label>
                    <select name="id" id="student_id" class="form-select">
                        <option value="">-- Select Student --</option>
                        <?php
                        $studentSql = "SELECT id, firstname, admno FROM student WHERE school_id = $school_id ORDER BY id ASC";
                        $student_result = $conn->query($studentSql);
                        while ($row = $student_result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>" . htmlspecialchars($row['firstname']) . " (Adm: " . htmlspecialchars($row['admno']) . ")</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="class_id" class="form-label">Or Generate for Whole Class:</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">-- Select Class --</option>
                        <?php
                        $classSql = "
                            SELECT DISTINCT c.id AS class_id, c.name AS class_name
                            FROM student_subject ss
                            JOIN class c ON ss.class_id = c.id
                            WHERE ss.school_id = $school_id
                            ORDER BY c.name ASC
                        ";
                        $class_result = $conn->query($classSql);
                        if ($class_result && $class_result->num_rows > 0) {
                            while ($row = $class_result->fetch_assoc()) {
                                echo "<option value='{$row['class_id']}'>" . htmlspecialchars($row['class_name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
