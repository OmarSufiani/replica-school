<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$successMessage = '';
$errorMessage = '';

// Fetch all schools for the dropdown
$schools = mysqli_query($conn, "SELECT id, school_name FROM school");

// Show messages from session (for PRG)
if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}
if (isset($_SESSION['errorMessage'])) {
    $errorMessage = $_SESSION['errorMessage'];
    unset($_SESSION['errorMessage']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['FirstName']);
    $last = trim($_POST['LastName']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $selected_school_id = $_POST['school_id'] ?? null;

    // Validate required fields
    if (empty($first) || empty($last) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['errorMessage'] = "❌ Please fill all required fields.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Check if email already exists
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        $_SESSION['errorMessage'] = "❌ A user with this Email already registered. Please use another email.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $checkStmt->close();

    if ($role === 'superadmin') {
        // Superadmin does NOT belong to a school
        $sql = "INSERT INTO users (FirstName, LastName, email, password, role) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $first, $last, $email, $password, $role);
    } else {
        // For user/admin, school_id must be selected
        if (empty($selected_school_id)) {
            $_SESSION['errorMessage'] = "❌ Please select a school.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        $sql = "INSERT INTO users (FirstName, LastName, email, password, role, school_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $first, $last, $email, $password, $role, $selected_school_id);
    }

    if ($stmt->execute()) {
        $_SESSION['successMessage'] = "✅ User registered!";
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['errorMessage'] = "❌ Error: " . $stmt->error;
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Dashboard</a>

    <?php if ($successMessage): ?>
        <div id="success-alert" class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div id="error-alert" class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4>Register New User</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="FirstName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="LastName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" id="role-select" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
                <div class="mb-3" id="school-select-container">
                    <label class="form-label">School</label>
                    <select name="school_id" class="form-select">
                        <option value="">Select School</option>
                        <?php while ($school = mysqli_fetch_assoc($schools)): ?>
                            <option value="<?= $school['id'] ?>"><?= htmlspecialchars($school['school_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Register</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Hide school select if superadmin is selected
    const roleSelect = document.getElementById('role-select');
    const schoolContainer = document.getElementById('school-select-container');

    function toggleSchoolSelect() {
        if (roleSelect.value === 'superadmin') {
            schoolContainer.style.display = 'none';
            schoolContainer.querySelector('select').required = false;
        } else {
            schoolContainer.style.display = 'block';
            schoolContainer.querySelector('select').required = true;
        }
    }

    roleSelect.addEventListener('change', toggleSchoolSelect);
    window.onload = toggleSchoolSelect;
</script>

<!-- Auto-hide alerts -->
<script>
    setTimeout(() => {
        const successAlert = document.getElementById('success-alert');
        if (successAlert) successAlert.style.display = 'none';
        const errorAlert = document.getElementById('error-alert');
        if (errorAlert) errorAlert.style.display = 'none';
    }, 4000);
</script>

</body>
</html>
