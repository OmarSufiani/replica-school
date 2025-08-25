<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$success = '';
$error = '';

// ✅ Fetch school code
$schoolQuery = $conn->prepare("SELECT school_code FROM school WHERE id = ?");
$schoolQuery->bind_param("i", $school_id);
$schoolQuery->execute();
$schoolResult = $schoolQuery->get_result();
$schoolRow = $schoolResult->fetch_assoc();
$school_code = strtoupper($schoolRow['school_code']);
$schoolQuery->close();

// ✅ Show success after redirect
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $date_hired = $_POST['date_hired'];

    if (empty($user_id) || empty($name)) {
        $error = "❌ All fields are required.";
    } else {
        // ✅ Prevent duplicate teacher for same user_id + school
        $checkSql = $conn->prepare("SELECT id FROM teacher WHERE user_id = ? AND school_id = ?");
        $checkSql->bind_param("ii", $user_id, $school_id);
        $checkSql->execute();
        $checkResult = $checkSql->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "❌ This teacher already exists.";
        } else {
            // ✅ Get last enrolment number
            $lastSql = $conn->prepare("SELECT enrolment_no FROM teacher WHERE school_id = ? ORDER BY id DESC LIMIT 1");
            $lastSql->bind_param("i", $school_id);
            $lastSql->execute();
            $lastResult = $lastSql->get_result();
            $lastRow = $lastResult->fetch_assoc();

            if ($lastRow) {
                $lastNum = intval(substr($lastRow['enrolment_no'], strlen($school_code)));
                $newNum = $lastNum + 1;
            } else {
                $newNum = 1;
            }
            $lastSql->close();

            $enrolment_no = $school_code . str_pad($newNum, 3, "0", STR_PAD_LEFT);

            // ✅ Insert new teacher
            $stmt = $conn->prepare("INSERT INTO teacher (user_id, name, school_id, enrolment_no, date_hired) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isiss", $user_id, $name, $school_id, $enrolment_no, $date_hired);

            if ($stmt->execute()) {
                $_SESSION['success'] = "✅ Teacher added successfully with Enrolment No: $enrolment_no";
                header("Location: dashboard.php?page=add_teacher");
                exit();
            } else {
                $error = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $checkSql->close();
    }
}

// ✅ Fetch only users with role = 'teacher' for current school
$users = mysqli_query($conn, "
    SELECT id, FirstName, LastName 
    FROM users 
    WHERE school_id = $school_id AND role = 'teacher'
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const userSelect = document.getElementById("user_id");
            const nameInput = document.getElementById("name");

            userSelect.addEventListener("change", function () {
                const selectedOption = this.options[this.selectedIndex];
                const fullname = selectedOption.getAttribute("data-fullname") || "";
                nameInput.value = fullname;
            });

            const alertBox = document.querySelector(".alert");
            if (alertBox) {
                setTimeout(() => alertBox.style.display = "none", 3000);
            }
        });
    </script>
</head>
<body class="container py-4">

<h3 class="mb-3">Add Teacher</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="border p-4 rounded bg-light shadow-sm">

    <div class="mb-3">
        <label for="user_id" class="form-label">User Account</label>
        <select name="user_id" id="user_id" class="form-select" required>
            <option value="">Select User</option>
            <?php while ($row = mysqli_fetch_assoc($users)) { 
                $fullName = $row['FirstName'] . ' ' . $row['LastName'];
            ?>
                <option value="<?= $row['id'] ?>" data-fullname="<?= htmlspecialchars($fullName) ?>">
                    <?= htmlspecialchars($fullName) ?> (ID: <?= $row['id'] ?>)
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Teacher Name</label>
        <input type="text" name="name" id="name" class="form-control" placeholder="Teacher's full name" readonly required>
    </div>

    <div class="mb-3">
        <label for="enrolment_no" class="form-label">Enrolment Number (Auto)</label>
        <input type="text" class="form-control" value="<?= $school_code ?>..." readonly>
        <small class="text-muted">Will be generated automatically (e.g., <?= $school_code ?>001)</small>
    </div>
    
    <div class="mb-3">
        <label>Date Hired:</label>
        <input type="date" name="date_hired" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success">Add Teacher</button>
</form>

</body>
</html>
