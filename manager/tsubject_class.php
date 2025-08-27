<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = intval($_POST['teacher_id']);
    $subjects = $_POST['subject_ids'] ?? [];
    $classes = $_POST['class_ids'] ?? [];

    if (!empty($subjects) && !empty($classes)) {
        $inserted = 0;
        $skipped = 0;

        foreach ($subjects as $subject_id) {
            foreach ($classes as $class_id) {
                // Check if assignment already exists
                $check = $conn->prepare("SELECT 1 FROM tsubject_class 
                                         WHERE teacher_id=? AND subject_id=? AND class_id=? AND school_id=?");
                $check->bind_param("iiii", $teacher_id, $subject_id, $class_id, $school_id);
                $check->execute();
                $check->store_result();

                if ($check->num_rows == 0) {
                    // Insert new assignment
                    $stmt = $conn->prepare("INSERT INTO tsubject_class (teacher_id, subject_id, class_id, school_id) 
                                             VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiii", $teacher_id, $subject_id, $class_id, $school_id);
                    if ($stmt->execute()) {
                        $inserted++;
                    }
                    $stmt->close();
                } else {
                    $skipped++; // duplicate found
                }
                $check->close();
            }
        }

        if ($inserted > 0) {
            $success = "<div class='alert alert-success text-center'>✅ Assigned {$inserted} new records!</div>";
        }
        if ($skipped > 0) {
            $success .= "<div class='alert alert-warning text-center'>⚠️ Skipped {$skipped} duplicate records.</div>";
        }
    } else {
        $success = "<div class='alert alert-warning text-center'>⚠️ Please select at least one subject and one class.</div>";
    }
}

// Fetch data
$teachers = mysqli_query($conn, "SELECT id AS teacher_id, user_id, name 
                                 FROM teacher WHERE school_id = $school_id ORDER BY name ASC");
$subjects = mysqli_query($conn, "SELECT id, name FROM subject WHERE school_id = $school_id");
$classes = mysqli_query($conn, "SELECT id, name FROM class WHERE school_id = $school_id");
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
<div class="card shadow p-4">
    <h4 class="mb-4 text-center">Assign Teacher to Multiple Subjects & Classes</h4>

    <?= $success ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Teacher</label>
            <select name="teacher_id" class="form-select" required>
                <option value="">Select Teacher</option>
                <?php while ($t = mysqli_fetch_assoc($teachers)) { ?>
                    <option value="<?= $t['teacher_id'] ?>">
                        <?= htmlspecialchars($t['name']) ?> (User ID: <?= $t['user_id'] ?>)
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Subjects</label><br>
            <?php while ($s = mysqli_fetch_assoc($subjects)) { ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?= $s['id'] ?>" id="subject<?= $s['id'] ?>">
                    <label class="form-check-label" for="subject<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></label>
                </div>
            <?php } ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Classes</label><br>
            <?php while ($c = mysqli_fetch_assoc($classes)) { ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>" id="class<?= $c['id'] ?>">
                    <label class="form-check-label" for="class<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></label>
                </div>
            <?php } ?>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success">Assign</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
