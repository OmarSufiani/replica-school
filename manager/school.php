<?php
session_start();

// Start with a dummy session value for testing if needed
// $_SESSION['school_id'] = 1;
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: ../login.php');
    exit();
}
$success_msg = $error_msg = "";

// Ensure session has school_id
if (!isset($_SESSION['school_id'])) {
    $error_msg = "Access denied: school_id not set in session.";
} else {
    $school_id = $_SESSION['school_id'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "your_database_name");
    if ($conn->connect_error) {
        $error_msg = "Connection failed: " . $conn->connect_error;
    }

    // Form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $school_name = $conn->real_escape_string($_POST['school_name']);
        $school_code = $conn->real_escape_string($_POST['school_code']);
        $address = $conn->real_escape_string($_POST['address']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $created_at = date("Y-m-d H:i:s");

        // Prepare and execute insert
        $stmt = $conn->prepare("INSERT INTO schools (school_id, school_name, school_code, address, phone, email, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $school_id, $school_name, $school_code, $address, $phone, $email, $created_at);

        if ($stmt->execute()) {
            $success_msg = "School details inserted successfully.";
        } else {
            $error_msg = "Insert error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insert School Details</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4>Enter School Details</h4>
        </div>
        <div class="card-body">
            <!-- Alert Messages -->
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- Form -->
            <form method="post" action="">
                <div class="mb-3">
                    <label for="school_name" class="form-label">School Name</label>
                    <input type="text" class="form-control" name="school_name" required>
                </div>

                <div class="mb-3">
                    <label for="school_code" class="form-label">School Code</label>
                    <input type="text" class="form-control" name="school_code" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <button type="submit" class="btn btn-success">Submit</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
