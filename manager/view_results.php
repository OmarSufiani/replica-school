<?php
session_start();
include 'db.php';

if (!isset($_SESSION['school_id'])) {
    die("Access denied. Please login.");
}
$school_id = $_SESSION['school_id'];

// Fetch classes and subjects for the school
$classes = $conn->query("SELECT id, name FROM class WHERE school_id = $school_id ORDER BY name");
$subjects = $conn->query("SELECT id, name FROM subject WHERE school_id = $school_id ORDER BY name");

// Initialize variables
$results = [];
$class_id = $subject_id = $term = $exam_type = $year = null;

if (isset($_POST['filter']) || isset($_POST['download_csv'])) {
    $params = [];
    $types = '';
    $conditions = "s.school_id = ?"; // always filter by school
    $params[] = $school_id;
    $types .= 'i';

    // Optional filters
    if (!empty($_POST['class_id'])) {
        $conditions .= " AND s.class_id = ?";
        $params[] = $_POST['class_id'];
        $types .= 'i';
        $class_id = $_POST['class_id'];
    }
    if (!empty($_POST['term'])) {
        $conditions .= " AND s.term = ?";
        $params[] = $_POST['term'];
        $types .= 's';
        $term = $_POST['term'];
    }
    if (!empty($_POST['exam_type'])) {
        $conditions .= " AND s.exam_type = ?";
        $params[] = $_POST['exam_type'];
        $types .= 's';
        $exam_type = $_POST['exam_type'];
    }
    if (!empty($_POST['subject_id'])) {
        $conditions .= " AND s.subject_id = ?";
        $params[] = $_POST['subject_id'];
        $types .= 'i';
        $subject_id = $_POST['subject_id'];
    }
    if (!empty($_POST['year'])) {
        $conditions .= " AND YEAR(s.created_at) = ?";
        $params[] = $_POST['year'];
        $types .= 'i';
        $year = $_POST['year'];
    }

    // Prepare query: join student, subject, class and rank by Score DESC
    $sql = "
        SELECT CONCAT(st.firstname, ' ', st.lastname) AS student_name,
               sub.name AS subject_name,
               c.name AS class_name,
               s.term, s.exam_type, s.Score, s.performance, s.tcomments
        FROM score s
        JOIN student st ON s.std_id = st.id
        JOIN subject sub ON s.subject_id = sub.id
        JOIN class c ON s.class_id = c.id
        WHERE $conditions
        ORDER BY s.Score DESC
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("SQL error: " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();

    // CSV download
    if (isset($_POST['download_csv'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="results.csv"');
        $output = fopen("php://output", "w");
        fputcsv($output, ['#','Student Name','Class','Subject','Term','Exam Type','Score','Performance','Teacher Comment']);
        $rank = 1;
        while ($row = $results->fetch_assoc()) {
            fputcsv($output, [
                $rank++,
                $row['student_name'],
                $row['class_name'],
                $row['subject_name'],
                $row['term'],
                $row['exam_type'],
                $row['Score'],
                $row['performance'],
                $row['tcomments']
            ]);
        }
        fclose($output);
        exit();
    }
}
?>

<!DOCTYPE html>

<head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">&larr; Back to Dashboard</a>

    <h3 class="mb-4">Teacher - View & Download Results</h3>

    <!-- Filter Form -->
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <option value="">--All Classes--</option>
                <?php while($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= (isset($class_id) && $class_id==$c['id']) ? 'selected' : '' ?>>
                        <?= $c['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" value="<?= isset($year)?$year:date('Y') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Term</label>
            <select name="term" class="form-select">
                <option value="">--All Terms--</option>
                <option value="Term 1" <?= (isset($term) && $term=='Term 1')?'selected':'' ?>>Term 1</option>
                <option value="Term 2" <?= (isset($term) && $term=='Term 2')?'selected':'' ?>>Term 2</option>
                <option value="Term 3" <?= (isset($term) && $term=='Term 3')?'selected':'' ?>>Term 3</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-select">
                <option value="">--All Subjects--</option>
                <?php while($s = $subjects->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>" <?= (isset($subject_id) && $subject_id==$s['id'])?'selected':'' ?>>
                        <?= $s['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Exam Type</label>
            <select name="exam_type" class="form-select">
                <option value="">--All Types--</option>
                <option value="CAT" <?= (isset($exam_type) && $exam_type=='CAT')?'selected':'' ?>>CAT</option>
                <option value="Mid Term" <?= (isset($exam_type) && $exam_type=='Mid Term')?'selected':'' ?>>Mid Term</option>
                <option value="End Term" <?= (isset($exam_type) && $exam_type=='End Term')?'selected':'' ?>>End Term</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="filter" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Results Table -->
    <?php if (!empty($results) && $results->num_rows > 0): ?>
        <form method="post">
            <input type="hidden" name="class_id" value="<?= $class_id ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <input type="hidden" name="term" value="<?= $term ?>">
            <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
            <input type="hidden" name="exam_type" value="<?= $exam_type ?>">
            <button type="submit" name="download_csv" class="btn btn-success mb-3">Download CSV</button>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Rank</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Term</th>
                    <th>Exam Type</th>
                    <th>Score</th>
                    <th>Performance</th>
                    <th>Teacher Comments</th>
                </tr>
            </thead>
            <tbody>
            <?php $rank = 1; ?>
            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?= $rank++ ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                    <td><?= htmlspecialchars($row['term']) ?></td>
                    <td><?= htmlspecialchars($row['exam_type']) ?></td>
                    <td><?= htmlspecialchars($row['Score']) ?></td>
                    <td><?= htmlspecialchars($row['performance']) ?></td>
                    <td><?= htmlspecialchars($row['tcomments']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php elseif(isset($_POST['filter'])): ?>
        <div class="alert alert-warning">No results found for selected filters.</div>
    <?php endif; ?>
</div>

</body>
</html>
