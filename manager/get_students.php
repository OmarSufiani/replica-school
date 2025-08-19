<?php
include 'db.php';

$class_id   = $_GET['class_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;

if ($class_id && $subject_id) {
    $stmt = $conn->prepare("SELECT st.admno, st.firstname, st.lastname 
                            FROM student st
                            JOIN student_subject ss ON ss.std_id = st.admno
                            WHERE ss.class_id = ? AND ss.subject_id = ?");
    $stmt->bind_param("ii", $class_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['admno']}'>{$row['firstname']} {$row['lastname']}</option>";
    }
}
