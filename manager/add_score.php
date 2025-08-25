<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

// ✅ Allow only teacher and admin
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['school_id']) || 
    ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')
) {
    die("❌ Unauthorized access");
}

$school_id = $_SESSION['school_id'];
$user_id   = $_SESSION['user_id'];



// Resolve teacher_id from teacher table using session user_id
$tq = $conn->prepare("SELECT id FROM teacher WHERE user_id = ? AND school_id = ?");
$tq->bind_param("ii", $user_id, $school_id);
$tq->execute();
$tq->bind_result($teacher_id);
$tq->fetch();
$tq->close();
if (!$teacher_id) {
    die("Teacher record not found for this user.");
}

$successMessage = "";
$errorMessage   = "";

// Selected filters (persist across postbacks)
$selected_class_id   = isset($_POST['class_id'])   ? (int)$_POST['class_id']   : 0;
$selected_subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$selected_term       = $_POST['term']      ?? "";
$selected_exam_type  = $_POST['exam_type'] ?? "";

// --- Load teacher's classes ---
$classes = [];
$stmt = $conn->prepare("
    SELECT DISTINCT c.id, c.name
    FROM tsubject_class tsc
    JOIN class c ON c.id = tsc.class_id
    WHERE tsc.teacher_id = ? AND tsc.school_id = ?
    ORDER BY c.name
");
$stmt->bind_param("ii", $teacher_id, $school_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $classes[] = $row;
$stmt->close();

// --- Load teacher's subjects for selected class (if any) ---
$subjects = [];
if ($selected_class_id) {
    $stmt = $conn->prepare("
        SELECT DISTINCT s.id, s.name
        FROM tsubject_class tsc
        JOIN subject s ON s.id = tsc.subject_id
        WHERE tsc.teacher_id = ? AND tsc.class_id = ? AND tsc.school_id = ?
        ORDER BY s.name
    ");
    $stmt->bind_param("iii", $teacher_id, $selected_class_id, $school_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $subjects[] = $row;
    $stmt->close();
}

// Holder for students to render in step 2
$student_rows = [];
$existing_map = []; // std_id => ['Score'=>..., 'tcomments'=>...]

// --- Step 1: Load students for chosen class/subject/term/exam ---
if (isset($_POST['load_students'])) {
    if ($selected_class_id && $selected_subject_id && $selected_term && $selected_exam_type) {
        // Only students in this class who do this subject
        $stmt = $conn->prepare("
            SELECT s.id, s.firstname, s.lastname, s.admno
            FROM student s
            JOIN student_subject ss ON ss.student_id = s.id
            WHERE ss.class_id = ? AND ss.subject_id = ? AND ss.school_id = ? AND s.school_id = ?
            ORDER BY s.firstname, s.lastname
        ");
        $stmt->bind_param("iiii", $selected_class_id, $selected_subject_id, $school_id, $school_id);
        $stmt->execute();
        $student_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Pull any existing scores (to prefill)
        if ($student_rows) {
            $stmt = $conn->prepare("
                SELECT std_id, Score, tcomments
                FROM score
                WHERE class_id = ? AND subject_id = ? AND term = ? AND exam_type = ?
                  AND school_id = ? AND teacher_id = ?
            ");
            $stmt->bind_param("iissii", $selected_class_id, $selected_subject_id, $selected_term, $selected_exam_type, $school_id, $teacher_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $existing_map[(int)$r['std_id']] = ['Score' => $r['Score'], 'tcomments' => $r['tcomments']];
            }
            $stmt->close();
        }
    } else {
        $errorMessage = "Please select class, subject, term, and exam type.";
    }
}

// --- Step 2: Save bulk scores ---
if (isset($_POST['save_scores'])) {
    $selected_class_id   = (int)$_POST['class_id'];
    $selected_subject_id = (int)$_POST['subject_id'];
    $selected_term       = $_POST['term'];
    $selected_exam_type  = $_POST['exam_type'];
    $scores   = $_POST['scores']   ?? [];
    $comments = $_POST['comments'] ?? [];
    $added = 0; $updated = 0;

    foreach ($scores as $std_id => $scoreVal) {
        $std_id = (int)$std_id;
        $scoreVal = trim($scoreVal);
        if ($scoreVal === "" || !is_numeric($scoreVal)) continue;
        $scoreNum = max(0, min(100, (float)$scoreVal));

        // Compute performance
        if ($scoreNum < 30) {
            $performance = "B.E";
            $autoComment = "Put more effort";
        } elseif ($scoreNum < 50) {
            $performance = "A.E";
            $autoComment = "Average";
        } elseif ($scoreNum < 70) {
            $performance = "M.E";
            $autoComment = "Good";
        } else {
            $performance = "E.E";
            $autoComment = "Excellent";
        }

        // Use provided tcomment if any, else auto
        $tcomment = trim($comments[$std_id] ?? "");
        if ($tcomment === "") $tcomment = $autoComment;
        if (strlen($tcomment) > 50) $tcomment = substr($tcomment, 0, 50);

        // Check if a record exists (same student/subject/class/term/exam/teacher/school)
        $chk = $conn->prepare("
            SELECT id FROM score
            WHERE std_id=? AND subject_id=? AND class_id=? AND term=? AND exam_type=?
              AND teacher_id=? AND school_id=?
            LIMIT 1
        ");
        $chk->bind_param("iiissii", $std_id, $selected_subject_id, $selected_class_id, $selected_term, $selected_exam_type, $teacher_id, $school_id);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            // Update existing
            $chk->bind_result($score_id);
            $chk->fetch();
            $upd = $conn->prepare("
                UPDATE score
                SET Score=?, performance=?, tcomments=?
                WHERE id=?
            ");
            $upd->bind_param("dssi", $scoreNum, $performance, $tcomment, $score_id);
            if ($upd->execute()) $updated++;
            $upd->close();
        } else {
            // Insert new
            $ins = $conn->prepare("
                INSERT INTO score (std_id, subject_id, term, exam_type, class_id, Score, performance, tcomments, school_id, teacher_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->bind_param("iissidssii", $std_id, $selected_subject_id, $selected_term, $selected_exam_type,
                                         $selected_class_id, $scoreNum, $performance, $tcomment, $school_id, $teacher_id);
            if ($ins->execute()) $added++;
            $ins->close();
        }
        $chk->close();
    }

    $successParts = [];
    if ($added)   $successParts[]  = "$added added";
    if ($updated) $successParts[]  = "$updated updated";
    $successMessage = $successParts ? "Scores saved: " . implode(", ", $successParts) . "." : "No scores were saved.";
    
    // After save, reload student list to show again (optional)
    $stmt = $conn->prepare("
        SELECT s.id, s.firstname, s.lastname, s.admno
        FROM student s
        JOIN student_subject ss ON ss.student_id = s.id
        WHERE ss.class_id = ? AND ss.subject_id = ? AND ss.school_id = ? AND s.school_id = ?
        ORDER BY s.firstname, s.lastname
    ");
    $stmt->bind_param("iiii", $selected_class_id, $selected_subject_id, $school_id, $school_id);
    $stmt->execute();
    $student_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Refresh existing map
    $existing_map = [];
    $stmt = $conn->prepare("
        SELECT std_id, Score, tcomments
        FROM score
        WHERE class_id = ? AND subject_id = ? AND term = ? AND exam_type = ?
          AND school_id = ? AND teacher_id = ?
    ");
    $stmt->bind_param("iissii", $selected_class_id, $selected_subject_id, $selected_term, $selected_exam_type, $school_id, $teacher_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $existing_map[(int)$r['std_id']] = ['Score' => $r['Score'], 'tcomments' => $r['tcomments']];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Scores (Bulk)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>

    <h3 class="mb-3">Add Scores - Bulk Entry</h3>

    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($successMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($errorMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Step 1: Filters -->
    <?php $onStep2 = !empty($student_rows); ?>
    <form method="POST" class="card p-3 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" required onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $selected_class_id == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $selected_subject_id == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Term</label>
                <select name="term" class="form-select" required>
                    <option value="">Select Term</option>
                    <?php
                    $terms = ["Term 1","Term 2","Term 3"];
                    foreach ($terms as $t) {
                        $sel = ($selected_term === $t) ? 'selected' : '';
                        echo "<option value=\"$t\" $sel>$t</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Exam Type</label>
                <select name="exam_type" class="form-select" required>
                    <option value="">Select Exam Type</option>
                    <?php
                    $exam_types = ["CAT","Mid Term","End Term"];
                    foreach ($exam_types as $e) {
                        $sel = ($selected_exam_type === $e) ? 'selected' : '';
                        echo "<option value=\"$e\" $sel>$e</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" name="load_students" class="btn btn-primary">Load Students</button>
        </div>
    </form>

    <!-- Step 2: Bulk Entry Table -->
    <?php if ($onStep2): ?>
        <form method="POST" class="card p-3">
            <input type="hidden" name="class_id" value="<?= htmlspecialchars($selected_class_id) ?>">
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($selected_subject_id) ?>">
            <input type="hidden" name="term" value="<?= htmlspecialchars($selected_term) ?>">
            <input type="hidden" name="exam_type" value="<?= htmlspecialchars($selected_exam_type) ?>">

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Adm No.</th>
                            <th style="width:140px;">Score</th>
                            <th style="width:260px;">Comment (tcomments)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach ($student_rows as $st): 
                            $prefScore = $existing_map[$st['id']]['Score']      ?? '';
                            $prefComm  = $existing_map[$st['id']]['tcomments'] ?? '';
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($st['firstname'].' '.$st['lastname']) ?></td>
                            <td><?= htmlspecialchars($st['admno']) ?></td>
                            <td>
                                <input type="number"
                                       class="form-control score-input"
                                       name="scores[<?= $st['id'] ?>]"
                                       min="0" max="100" step="0.01"
                                       value="<?= htmlspecialchars($prefScore) ?>">
                            </td>
                            <td>
                                <input type="text"
                                       class="form-control comment-input"
                                       name="comments[<?= $st['id'] ?>]"
                                       maxlength="50"
                                       placeholder="Auto if left blank"
                                       value="<?= htmlspecialchars($prefComm) ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" name="save_scores" class="btn btn-success">Save All Scores</button>
        </form>
        <small class="text-muted d-block mt-2">Tip: If comment is left blank, it’s auto-filled from the score (Put more effort / Average / Good / Excellent).</small>
    <?php elseif (isset($_POST['load_students'])): ?>
        <div class="alert alert-warning">No students found for that class & subject.</div>
    <?php endif; ?>
</div>

<script>
/* Optional helper: auto-suggest comment when typing score (does not overwrite existing text) */
document.addEventListener('input', function(e){
    if(!e.target.classList.contains('score-input')) return;
    const score = parseFloat(e.target.value);
    const row = e.target.closest('tr');
    if (!row) return;
    const commentInput = row.querySelector('.comment-input');
    if (!commentInput) return;
    if (commentInput.value.trim() !== '') return; // don't override teacher's text

    let suggestion = '';
    if (!isNaN(score)) {
        if (score < 30)       suggestion = 'Put more effort';
        else if (score < 50)  suggestion = 'Average';
        else if (score < 70)  suggestion = 'Good';
        else                  suggestion = 'Excellent';
        commentInput.placeholder = suggestion;
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
