<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Start session if needed
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';
// Database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$successMessage = "";
$errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_name = trim($_POST['school_name']);
    $school_code = trim($_POST['school_code']);
    $address     = trim($_POST['address']);
    $phone       = trim($_POST['phone']);
    $email       = trim($_POST['email']);

    // Basic validation
    if (empty($school_name) || empty($school_code) || empty($email)) {
        $errorMessage = "School Name, School Code, and Email are required.";
    } else {
        // 1️⃣ Check if school already exists
        $checkSql = "SELECT id FROM school WHERE school_code = ? OR school_name = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $school_code, $school_name);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errorMessage = "This school is already registered!";
        } else {
            // 2️⃣ Insert new school
            $sql = "INSERT INTO school (school_name, school_code, address, phone, email) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("sssss", $school_name, $school_code, $address, $phone, $email);

            if ($stmt->execute()) {
                $successMessage = "School added successfully!";
            } else {
                $errorMessage = "Error: " . $conn->error;
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add School</title>
     <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body class="bg-light">


<div class="container mt-5">


    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">

        

            <h4 class="mb-0">Add School</h4>
        </div>
        <div class="card-body">
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= $successMessage ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= $errorMessage ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">School Name</label>
                    <input type="text" name="school_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">School Code</label>
                    <input type="text" name="school_code" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">Add School</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS --><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
