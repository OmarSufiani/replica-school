<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function getStudentProgress($student_id, $school_id, $conn) {
    $sql = "
        SELECT YEAR(created_at) as yr, term, exam_type, AVG(Score) as avgScore
        FROM score
        WHERE std_id = ? AND school_id = ?
        GROUP BY YEAR(created_at), term, exam_type
        ORDER BY yr ASC, term ASC, exam_type ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $progress = [];
    while ($row = $result->fetch_assoc()) {
        $year = $row['yr'];
        $term = 'Term' . $row['term']; // Term1, Term2, Term3
        $examType = $row['exam_type']; // e.g., CAT, Mid-Term, End-Term
        $progress[$year][$term][$examType] = round($row['avgScore'], 0);
    }

    $stmt->close();
    return $progress;
}
function generateProgressChart($progress) {
    if (empty($progress)) return null;

    $examOrder = ['CAT', 'Mid Term', 'End Term'];
    $points = [];
    $labels = [];

    // Collect points and labels
    foreach ($progress as $year => $terms) {
        foreach ($terms as $term => $exams) {
            foreach ($examOrder as $exam) {
                $labels[] = "$year $exam " . str_replace("Term", "", $term);
                $points[] = isset($exams[$exam]) ? $exams[$exam] : null;
            }
        }
    }

    $totalPoints = count($points);
    $maxWidthUnits = 27; // max visual width units for spacing (~1cm per exam type)

    // Calculate dynamic horizontal spacing
    $spacing = $maxWidthUnits / max($totalPoints - 1, 1); // divide available width by points-1

    // Main dataset with dynamic horizontal spacing
    $dataWithX = [];
    foreach ($points as $i => $point) {
        $dataWithX[] = ["x" => $i * $spacing, "y" => $point];
    }

    $dataset = [
        "label" => "Student Progress",
        "data" => $dataWithX,
        "borderColor" => "blue",
        "backgroundColor" => "blue",
        "fill" => false,
        "tension" => 0.3,
        "pointRadius" => 5,
        "spanGaps" => true
    ];

    // Dummy dataset to force y-axis 0–100 fully
    $dummyDataset = [
        "label" => "",
        "data" => array_merge([0], array_fill(0, $totalPoints, null), [100]),
        "borderColor" => "rgba(0,0,0,0)",
        "backgroundColor" => "rgba(0,0,0,0)",
        "pointRadius" => 0,
        "fill" => false
    ];

    $chartConfig = [
        "type" => "line",
        "data" => [
            "labels" => $labels,
            "datasets" => [$dataset, $dummyDataset]
        ],
        "options" => [
            "plugins" => [
                "title" => [
                    "display" => true,
                    "text" => "Student Progress (CAT → Mid Term → End Term)"
                ]
            ],
            "scales" => [
                "x" => [
                    "type" => "linear",
                    "title" => ["display" => true, "text" => "Exams"],
                    "ticks" => [
                        "stepSize" => 1,
                        "font" => ["size" => 10]
                    ]
                ],
                "y" => [
                    "type" => "linear",
                    "beginAtZero" => true,
                    "min" => 0,
                    "max" => 100,
                    "ticks" => [
                        "stepSize" => 10,
                        "callback" => "function(value) { return value; }",
                        "font" => ["size" => 10]
                    ],
                    "title" => ["display" => true, "text" => "Score"]
                ]
            ]
        ]
    ];

    $encoded = urlencode(json_encode($chartConfig));
    $url = "https://quickchart.io/chart.png?c={$encoded}";

    $png = @file_get_contents($url);
    if (!$png) return null;

    return "data:image/png;base64," . base64_encode($png);
}




function generateStudentReport($student_id, $term, $exam_type, $year, $conn) {
    // Fetch student info and class via student_subject
    $stu_q = $conn->prepare("
        SELECT s.id, s.firstname, s.lastname, s.admno, ss.class_id, ss.school_id
        FROM student_subject ss
        JOIN student s ON s.id = ss.student_id
        WHERE s.id = ?
        LIMIT 1
    ");
    if (!$stu_q) die("Prepare failed (student query): " . $conn->error);
    $stu_q->bind_param("i", $student_id);
    $stu_q->execute();
    $stu_q->bind_result($id, $firstname, $lastname, $admno, $class_id, $school_id);
    if (!$stu_q->fetch()) {
        $stu_q->close();
        return false;
    }
    $stu_q->close();

    $student = [
        'id' => $id,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'admno' => $admno,
        'class_id' => $class_id,
        'school_id' => $school_id
    ];

    // Get class and school name
    $class_q = $conn->prepare("
        SELECT c.name AS class_name, sch.school_name AS school_name 
        FROM class c 
        JOIN school sch ON c.school_id = sch.id
        WHERE c.id = ?
    ");
    if (!$class_q) die("Prepare failed (class query): " . $conn->error);
    $class_q->bind_param("i", $class_id);
    $class_q->execute();
    $class = $class_q->get_result()->fetch_assoc();
    $class_q->close();

    $class_name = $class['class_name'] ?? 'Unknown';
    $school_name = $class['school_name'] ?? 'Unknown School';

    // Extract stream number (e.g., "7" from "7B")
    preg_match('/\d+/', $class_name, $matches);
    $stream_number = $matches[0] ?? null;

    // Get student scores
    $sql = "
        SELECT s.id AS subject_id, s.name AS subject_name, sc.Score, sc.performance, sc.tcomments
        FROM score AS sc
        JOIN subject AS s ON sc.subject_id = s.id
        WHERE sc.std_id = ? AND sc.term = ? AND sc.exam_type = ? AND YEAR(sc.created_at) = ? AND sc.school_id = ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare failed (scores query): " . $conn->error);
    $stmt->bind_param("issii", $student_id, $term, $exam_type, $year, $school_id);
    $stmt->execute();
    $scores = $stmt->get_result();
    $stmt->close();

    if ($scores->num_rows === 0) return false;

    $studentScores = [];
    while ($row = $scores->fetch_assoc()) {
        $studentScores[] = $row;
    }

    // === CLASS (Stream) RANK ===
    $classAvgSql = "
        SELECT sc.std_id, AVG(sc.Score) as avgScore 
        FROM score sc
        JOIN student_subject ss ON sc.std_id = ss.student_id
        WHERE ss.class_id = ? AND sc.term = ? AND sc.exam_type = ? 
          AND YEAR(sc.created_at) = ? AND sc.school_id = ?
        GROUP BY sc.std_id
        ORDER BY avgScore DESC
    ";
    $classAvgStmt = $conn->prepare($classAvgSql);
    $classAvgStmt->bind_param("issii", $class_id, $term, $exam_type, $year, $school_id);
    $classAvgStmt->execute();
    $classAvgResult = $classAvgStmt->get_result();
    $classAvgStmt->close();

    $studentClassRank = null;
    $totalStudentsInClass = 0;
    $rankPos = 1;
    $prevAvg = null;

    while ($row = $classAvgResult->fetch_assoc()) {
        $totalStudentsInClass++;
        if ($prevAvg !== null && floatval($row['avgScore']) < $prevAvg) {
            $rankPos = $totalStudentsInClass; 
        }
        if ($row['std_id'] == $student_id) {
            $studentClassRank = $rankPos;
        }
        $prevAvg = floatval($row['avgScore']);
    }
    if ($studentClassRank === null) $studentClassRank = $totalStudentsInClass;

    // === GRADE (All Streams) RANK ===
    $gradeRankSql = "
        SELECT sc.std_id, AVG(sc.Score) as avgScore 
        FROM score sc
        JOIN student_subject ss ON sc.std_id = ss.student_id
        JOIN class c ON ss.class_id = c.id
        WHERE sc.term = ? AND sc.exam_type = ? 
          AND YEAR(sc.created_at) = ? AND c.name LIKE CONCAT(?, '%') 
          AND sc.school_id = ?
        GROUP BY sc.std_id
        ORDER BY avgScore DESC
    ";
    $stmt = $conn->prepare($gradeRankSql);
    $stmt->bind_param("ssisi", $term, $exam_type, $year, $stream_number, $school_id);
    $stmt->execute();
    $gradeRankResult = $stmt->get_result();
    $stmt->close();

    $studentGradeRank = null;
    $totalInGrade = 0;
    $rankPos = 1;
    $prevAvg = null;

    while ($row = $gradeRankResult->fetch_assoc()) {
        $totalInGrade++;
        if ($prevAvg !== null && floatval($row['avgScore']) < $prevAvg) {
            $rankPos = $totalInGrade;
        }
        if ($row['std_id'] == $student_id) {
            $studentGradeRank = $rankPos;
        }
        $prevAvg = floatval($row['avgScore']);
    }
    if ($studentGradeRank === null) $studentGradeRank = $totalInGrade;

    // --- SUBJECT RANKS (per subject) ---
    $subjectRanks = [];
    foreach ($studentScores as $row) {
        $subjectId = $row['subject_id'];
        $sql = "
            SELECT std_id, Score 
            FROM score 
            WHERE subject_id = ? 
              AND class_id = ? 
              AND term = ? 
              AND exam_type = ? 
              AND YEAR(created_at) = ?
            ORDER BY Score DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissi", $subjectId, $student['class_id'], $term, $exam_type, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $rank = 0;
        $totalStudents = $result->num_rows;
        $studentRank = null;

        while ($r = $result->fetch_assoc()) {
            $rank++;
            if ($r['std_id'] == $student_id) {
                $studentRank = $rank;
                break;
            }
        }

        $subjectRanks[$subjectId] = [
            'rank' => $studentRank ?? $rank,
            'total' => $totalStudents
        ];
    }

//ASSIGN TEACHERS NAME,

$teacher_name = "";
$teacherComment = ""; // your existing variable

$stmt = $conn->prepare("
    SELECT name 
    FROM class_teachers 
    WHERE class_id = ? AND school_id = ? 
    LIMIT 1
");
$stmt->bind_param("ii", $class_id, $school_id);
$stmt->execute();
$stmt->bind_result($teacher_name);
$stmt->fetch();
$stmt->close();

if (!$teacher_name) {
    $teacher_name = "Class Teacher"; // fallback
}

// --- Get HOI (Dean) Name ---
$hoi_name = "";
$sql2 = "SELECT CONCAT(FirstName, ' ', LastName) 
         FROM users 
         WHERE role = 'admin' AND school_id = ? 
         LIMIT 1";

if ($stmt2 = $conn->prepare($sql2)) {
    $stmt2->bind_param("i", $school_id);
    $stmt2->execute();
    $stmt2->bind_result($hoi_name);
    $stmt2->fetch();
    $stmt2->close();
}

if (!$hoi_name) {
    $hoi_name = "Head of Institution"; // fallback
}



    // === Compute overall average for student ===
    $totalScore = 0;
    $subjectCount = count($studentScores);
    foreach ($studentScores as $row) {
        $totalScore += floatval($row['Score']);
    }
    $overallAvg = $subjectCount > 0 ? $totalScore / $subjectCount : 0;

    // === Generate Teacher Comment based on overall average ===
    if ($overallAvg >= 70) {
        $teacherComment = "Excellent performance, keep it up!";
    } elseif ($overallAvg >= 60) {
        $teacherComment = "Good performance, but there is room for improvement.";
    } elseif ($overallAvg >= 50) {
        $teacherComment = "Average performance, strive to do better.";
    } else {
        $teacherComment = "Poor performance, more effort is needed.";
    }

$watermark = "<div style='position: fixed; top: 40%; left: 20%; width: 60%; 
    text-align: center; opacity: 0.08; font-size: 70px; color: gray; 
    transform: rotate(-30deg); z-index: -1;'>"
    . htmlspecialchars($school_name, ENT_QUOTES) . "</div>";

$html = "
<div style='width: 100%; height: 100%; padding: 15px; font-family: Arial, sans-serif; 
            font-size: 13px; transform: scale(0.9); transform-origin: top left; box-sizing: border-box;'>

    $watermark

    <h1 style='text-align: center; color: #090a0cff; margin-bottom: 0.2em; font-size:16px;'>
        " . htmlspecialchars($school_name, ENT_QUOTES) . "
    </h1>
    <h2 style='text-align: center; color: #070808ff; margin-top: 0.2em; font-size: 13px;'>
        STUDENT REPORT FORM
    </h2>

    <p><strong>STUDENT:</strong> " . htmlspecialchars($student['firstname'] . ' ' . $student['lastname'], ENT_QUOTES) . 
    " (Adm: " . htmlspecialchars($student['admno'], ENT_QUOTES) . ")</p>
    <p><strong>CLASS:</strong> " . htmlspecialchars($class_name, ENT_QUOTES) . "</p>
    <p><strong>TERM:</strong> " . htmlspecialchars($term, ENT_QUOTES) . ", 
       <strong>EXAM Type:</strong> " . htmlspecialchars($exam_type, ENT_QUOTES) . ", 
       <strong>YEAR:</strong> " . htmlspecialchars($year, ENT_QUOTES) . "</p>

    <table border='1' cellpadding='3' cellspacing='0' 
           style='width: 100%; border-collapse: collapse; font-size: 12px;'>
        <tr style='background-color: #f2f2f2;'>
            <th>#</th>
            <th>Subject</th>
            <th>Score</th>
            <th>Performance</th>
            <th>Teacher Comments</th>
            <th>Subject Rank</th>
        </tr>";

// Subject scores
$count = 1;
foreach ($studentScores as $row) {
    $subjectId = $row['subject_id'];
    $rankInfo = $subjectRanks[$subjectId] ?? ['rank' => '-', 'total' => '-'];
    $html .= "<tr>
                <td>" . $count++ . "</td>
                <td>" . htmlspecialchars($row['subject_name'], ENT_QUOTES) . "</td>
                <td>" . htmlspecialchars($row['Score'], ENT_QUOTES) . "</td>
                <td>" . htmlspecialchars($row['performance'], ENT_QUOTES) . "</td>
                <td>" . htmlspecialchars($row['tcomments'], ENT_QUOTES) . "</td>
                <td>{$rankInfo['rank']}/{$rankInfo['total']}</td>
              </tr>";
}

$html .= "</table>";

// Horizontal row: Left = Class + Teacher Comment, Right = Grading System
// Horizontal row: Left = Class + Teacher Comment, Right = Grading System
$html .= "
<div style='width: 100%; display: flex; justify-content: space-between; margin-top:10px; align-items: flex-start;'>

    <!-- Left side -->
    <div style='width: 65%;'>
        <p><strong>Class Position:</strong> {$studentClassRank}/{$totalStudentsInClass}</p>
        <p><strong>Overall Position:</strong> {$studentGradeRank}/{$totalInGrade}</p>




        <h3 style='margin-top: 5px;'>Teacher:{$teacher_name}'s:</h3>
        <p style='font-size: 15px; margin-bottom: 0;'>$teacherComment</p>
    </div> <br>

    <!-- Right side -->
    <div style='width: 100%; display: flex; justify-content: center;'>
        <table border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse; font-size:11px;'>
            <thead style='background-color: #f2f2f2;'>
                <tr>
                    <th>Performance</th>
                    <th>Meaning</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>M.E</td><td>Meeting Expectation</td></tr>
                <tr><td>A.E</td><td>Approaching Expectation</td></tr>
                <tr><td>B.E</td><td>Below Expectation</td></tr>
                <tr><td>E.E</td><td>Exceeding Expectation</td></tr>
            </tbody>
        </table>
    </div>

</div>";


// Progress chart
$progress = getStudentProgress($student_id, $school_id, $conn);
$chartUrl = generateProgressChart($progress);

if ($chartUrl) {
   $html .= "
    <div style='margin: 0;'>
        <img src='{$chartUrl}' style='width:100%; max-width:450px; height:auto; display:block;' />
    </div>";

}

// Signatures at bottom
$html .= "
<div style='margin-top: 20px; font-size: 13px; clear: both;'>
    <p>HOI's name ............{$hoi_name}........ .............Signature .............................................................................</p>
    <p>Teacher's name .......{$teacher_name}................... Signature ............................................................................</p>
</div>
</div>"; // end main container


    // PDF generation
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = sys_get_temp_dir() . "/report_{$student_id}_{$term}_{$exam_type}_{$year}.pdf";
    file_put_contents($filename, $dompdf->output());

    return $filename;
}

// --- Handle GET request ---
$currentYear = date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['id']) || isset($_GET['class_id']))) {
    $term = $_GET['term'] ?? 'Term 1';
    $exam_type = $_GET['exam_type'] ?? 'Midterm';
    $year = intval($_GET['year'] ?? $currentYear);

    if (isset($_GET['id']) && $_GET['id'] !== "") {
        $student_id = intval($_GET['id']);
        $filename = generateStudentReport($student_id, $term, $exam_type, $year, $conn);
        if (!$filename || !file_exists($filename)) die("No scores found for the selected student, term, and year.");
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        unlink($filename);
        exit;
    }

    if (isset($_GET['class_id']) && $_GET['class_id'] !== "") {
        $class_id = intval($_GET['class_id']);
        $stmt = $conn->prepare("SELECT s.id FROM student_subject ss JOIN student s ON ss.student_id = s.id WHERE ss.class_id = ?");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $students = $stmt->get_result();
        $stmt->close();

        if (!class_exists('ZipArchive')) die('ZipArchive PHP extension is not enabled.');
        $zip = new ZipArchive();
        $zipFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "Class_{$class_id}_Reports_{$term}_{$exam_type}_{$year}.zip";
        if ($zip->open($zipFilename, ZipArchive::CREATE) !== true) die("Cannot create ZIP file.");

        $reportsGenerated = 0;
        $generatedFiles = [];
        while ($stu = $students->fetch_assoc()) {
            $filename = generateStudentReport($stu['id'], $term, $exam_type, $year, $conn);
            if ($filename && file_exists($filename)) {
                $zip->addFile($filename, basename($filename));
                $generatedFiles[] = $filename;
                $reportsGenerated++;
            }
        }
        $zip->close();

        if ($reportsGenerated === 0) {
            if (file_exists($zipFilename)) unlink($zipFilename);
            die("No scores found for any students in the selected class, term, and exam type.");
        }

        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFilename) . '"');
        header('Content-Length: ' . filesize($zipFilename));
        flush();
        readfile($zipFilename);
        unlink($zipFilename);
        foreach ($generatedFiles as $file) if (file_exists($file)) unlink($file);
        exit;
    }
}
?>
