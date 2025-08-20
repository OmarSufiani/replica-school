<?php
session_start();
include 'manager/db.php';

$error = '';
$success = '';
$schools = [];

// Fetch ONLY active schools for dropdown
$school_query = $conn->query("SELECT id, school_name FROM school WHERE status = 1 ORDER BY school_name ASC");
while ($row = $school_query->fetch_assoc()) {
    $schools[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $firstName = $conn->real_escape_string(trim($_POST['firstName']));
    $lastName = $conn->real_escape_string(trim($_POST['lastName']));
    $school_id = (int) $_POST['school_id'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $role = 'user';

    if (empty($email) || empty($firstName) || empty($lastName) || empty($password) || empty($confirm) || empty($school_id)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // ✅ Check email only within the same school
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND school_id = ?");
        $check->bind_param("si", $email, $school_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email is already registered for this school.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (email, firstName, lastName, password, role, school_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $email, $firstName, $lastName, $hashed, $role, $school_id);

            if ($stmt->execute()) {
                $success = "Registration successful!";
            } else {
                $error = "Error: Unable to register. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Bandari Maritime Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="text-center mb-4">📝 SIGN UP</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="firstName" class="form-control" placeholder="First Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="lastName" class="form-control" placeholder="Last Name" required>
                        </div>
                        <div class="mb-3">
                            <select name="school_id" class="form-select" required>
                                <option value="">-- Select School --</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?= $school['id'] ?>"><?= htmlspecialchars($school['school_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="confirm" class="form-control" placeholder="Confirm Password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        Already have an account? <a href="login.php">Login</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
