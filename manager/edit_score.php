<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    die("You are not logged in.");
}

$teacher_id = $_SESSION['user_id'];
$school_id  = $_SESSION['school_id'];

// Fetch only the class and subject the teacher teaches
$sql = "SELECT s.id, s.std_id, st.name AS student_name, s.subject_id, sub.subject_name, 
               s.term, s.exam_type, s.class_id, c.class_name, s.score, s.performance, 
               s.tcomments, s.school_id, s.created_at
        FROM score s
        JOIN student st ON s.std_id = st.admno
        JOIN subject sub ON s.subject_id = sub.id
        JOIN class c ON s.class_id = c.id
        WHERE s.school_id = ?
          AND s.class_id IN (
                SELECT DISTINCT class_id FROM teacher_classes 
                WHERE teacher_id = ?
            )
          AND s.subject_id IN (
                SELECT DISTINCT subject_id FROM teacher_subjects
                WHERE teacher_id = ?
            )
          AND s.created_by = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $school_id, $teacher_id, $teacher_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>Student</th>
            <th>Subject</th>
            <th>Term</th>
            <th>Exam Type</th>
            <th>Class</th>
            <th>Score</th>
            <th>Performance</th>
            <th>Comments</th>
            <th>Action</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['student_name']}</td>
                <td>{$row['subject_name']}</td>
                <td>{$row['term']}</td>
                <td>{$row['exam_type']}</td>
                <td>{$row['class_name']}</td>
                <td>{$row['score']}</td>
                <td>{$row['performance']}</td>
                <td>{$row['tcomments']}</td>
                <td><a href='edit_score.php?id={$row['id']}'>Edit</a></td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No scores found.";
}

$stmt->close();
$conn->close();
?>
