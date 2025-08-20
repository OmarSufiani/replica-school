<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'manager/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // ✅ Check school status
        $school_id = $user['school_id'];
        $school_stmt = $conn->prepare("SELECT status FROM school WHERE id = ?");
        $school_stmt->bind_param("i", $school_id);
        $school_stmt->execute();
        $school_result = $school_stmt->get_result();
        $school = $school_result->fetch_assoc();

        if ($school && $school['status'] == 1) {
            // School active → allow login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['FirstName'] = $user['FirstName'];
            $_SESSION['LastName'] = $user['LastName'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['school_id'] = $user['school_id'];

            header('Location: manager/dashboard.php');
            exit;
        } else {
            // School disabled
            $error = "This school has been disabled due to pending payments. Please contact admin.";
        }
        $school_stmt->close();
    } else {
        $error = "Invalid Email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Jerzy Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4"> <!-- 👈 Container column -->
        <div class="card shadow p-4 rounded-4">
            <h2 class="text-center mb-4">Login</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php">Sign up</a></p>
            </div>
        </div>
    </div>
  </div>
</div>

</body>
</html>
