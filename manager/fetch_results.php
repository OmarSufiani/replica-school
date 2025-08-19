<?php
session_start();
include 'db.php';

if (!isset($_SESSION['school_id'])) {
    die("Unauthorized: School ID not set.");
}

$school_id = $_SESSION['school_id'];

$keyword = $_GET['q'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$keyword = mysqli_real_escape_string($conn, $keyword);
$search = "%$keyword%";

// ------------------------------
// COUNT TOTAL MATCHING RECORDS
// ------------------------------
$countQuery = "
    SELECT COUNT(*) AS total
    FROM student s
    JOIN score sc ON s.id = sc.std_id AND s.school_id = sc.school_id
    JOIN class c ON s.class_id = c.id AND s.school_id = c.school_id
    JOIN subject sub ON sc.subject_id = sub.id AND sc.school_id = sub.school_id
    WHERE 
        s.school_id = ? AND (
            s.firstname LIKE ? OR
            s.lastname LIKE ? OR
            c.name LIKE ? OR
            sub.name LIKE ? OR
            sc.term LIKE ? OR
            sc.exam_type LIKE ? OR
            sc.Score LIKE ? OR
            sc.performance LIKE ?
        )
";

$stmtCount = $conn->prepare($countQuery);
if (!$stmtCount) {
    die("Count query error: " . $conn->error);
}

$stmtCount->bind_param("sssssssss", $school_id, $search, $search, $search, $search, $search, $search, $search, $search);
$stmtCount->execute();
$resCount = $stmtCount->get_result();
$rowCount = $resCount->fetch_assoc();
$totalRows = $rowCount['total'];
$stmtCount->close();

$totalPages = ceil($totalRows / $limit);

// ------------------------------
// FETCH MATCHING STUDENT SCORES
// ------------------------------
$query = "
    SELECT 
        CONCAT(s.firstname, ' ', s.lastname) AS student_name,
        c.name,
        sub.name AS subject_name,
        sc.Score,
        sc.performance,
        sc.tcomments,
        sc.term,
        sc.exam_type
    FROM student s
    JOIN score sc ON s.id = sc.std_id AND s.school_id = sc.school_id
    JOIN class c ON s.class_id = c.id AND s.school_id = c.school_id
    JOIN subject sub ON sc.subject_id = sub.id AND sc.school_id = sub.school_id
    WHERE 
        s.school_id = ? AND (
            s.firstname LIKE ? OR
            s.lastname LIKE ? OR
            c.name LIKE ? OR
            sub.name LIKE ? OR
            sc.term LIKE ? OR
            sc.exam_type LIKE ? OR
            sc.Score LIKE ? OR
            sc.performance LIKE ?
        )
    ORDER BY s.firstname ASC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Main query error: " . $conn->error);
}

$stmt->bind_param("ssssssssssi", $school_id, $search, $search, $search, $search, $search, $search, $search, $search, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// ------------------------------
// DISPLAY RESULTS
// ------------------------------
if ($result->num_rows > 0) {
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-striped align-middle'>
            <thead class='table-dark'>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Term</th>
                    <th>Exam Type</th>
                    <th>Score</th>
                    <th>Performance</th>
                    <th>Teacher's Comment</th>
                </tr>
            </thead>
            <tbody>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['student_name']) . "</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['subject_name']) . "</td>
                <td>" . htmlspecialchars($row['term']) . "</td>
                <td>" . htmlspecialchars($row['exam_type']) . "</td>
                <td>" . htmlspecialchars($row['Score']) . "</td>
                <td>" . htmlspecialchars($row['performance']) . "</td>
                <td>" . htmlspecialchars($row['tcomments']) . "</td>
              </tr>";
    }

    echo "</tbody></table></div>";

    // ------------------------------
    // PAGINATION CONTROLS
    // ------------------------------
    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center">';

    if ($page > 1) {
        echo '<li class="page-item"><button class="page-link" onclick="changePage(' . ($page - 1) . ')">Back</button></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Back</span></li>';
    }

    echo '<li class="page-item disabled"><span class="page-link">Page ' . $page . ' of ' . $totalPages . '</span></li>';

    if ($page < $totalPages) {
        echo '<li class="page-item"><button class="page-link" onclick="changePage(' . ($page + 1) . ')">Next</button></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }

    echo '</ul></nav>';
} else {
    echo "<div class='alert alert-warning'>No results found for your search.</div>";
}

$stmt->close();
$conn->close();
?>

<script>
function changePage(page) {
    const searchInput = document.getElementById('searchInput');
    const query = searchInput ? searchInput.value : '';
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_results.php?q=' + encodeURIComponent(query) + '&page=' + page, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('resultsContainer').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
</script>
