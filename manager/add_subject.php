<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$message = '';

$available_subjects = [
    'ENGLISH',
    'KISWAHILI',
    'MATHEMATICS',
    'PRE-TECHNICALS',
    'INTERGRATED-SCIENCE',
    'SOCIAL STUDIES',
    'AGRICULTURE',
    'CREATIVE ARTS & SPORTS',
    'CRE',
    'IRE'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjects_selected = $_POST['subjects'] ?? [];

    if (empty($subjects_selected)) {
        $message = "❌ Please select at least one subject.";
    } else {
        $added = 0;
        foreach ($subjects_selected as $name) {
            // Determine if subject is compulsory
            $is_compulsory = ($name === 'CRE' || $name === 'IRE') ? 0 : 1;

            // Prevent duplicates
            $stmt_check = $conn->prepare("SELECT id FROM subject WHERE name=? AND school_id=?");
            $stmt_check->bind_param("si", $name, $school_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO subject (name, school_id, is_compulsory) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $name, $school_id, $is_compulsory);
                if ($stmt->execute()) {
                    $added++;
                }
                $stmt->close();
            }
            $stmt_check->close();
        }

        $message = "✅ Added {$added} new subject(s)!";
    }

    $_SESSION['message'] = $message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
<body class="container py-4">

<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">&larr; Back to Dashboard</a>

<h3 class="mb-3">Add Subjects</h3>

<?php if ($message): ?>
    <div id="message-box" class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-danger' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>
<form method="POST" class="border p-4 rounded bg-light shadow-sm">
    <div class="mb-3">
        <label class="form-label">Select Subjects Offered</label><br>

        <!-- Select All Checkbox -->
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="selectAll">
            <label class="form-check-label" for="selectAll"><strong>Select All Subjects</strong></label>
        </div>

        <?php foreach ($available_subjects as $sub): ?>
            <div class="form-check">
                <input class="form-check-input subject-checkbox" type="checkbox" name="subjects[]" value="<?= htmlspecialchars($sub) ?>" id="<?= htmlspecialchars($sub) ?>">
                <label class="form-check-label" for="<?= htmlspecialchars($sub) ?>">
                    <?= htmlspecialchars($sub) ?> <?= ($sub === 'CRE' || $sub === 'IRE') ? '(Optional Subjects)' : '' ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="submit" class="btn btn-success">Add Selected Subjects</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.subject-checkbox');

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    });

    // Optional: if all individual boxes are checked/unchecked, update "Select All"
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
        });
    });
});
</script>
