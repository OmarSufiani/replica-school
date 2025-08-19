<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fetch all schools for dropdown
$schools = mysqli_query($conn, "SELECT id, school_name FROM school");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = $_POST['FirstName'];
    $last = $_POST['LastName'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $school_id = $_POST['school_id'] ?? null;

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "UPDATE users SET FirstName=?, LastName=?, email=?, password=?, role=?, school_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $first, $last, $email, $password, $role, $school_id, $user_id);
    } else {
        $sql = "UPDATE users SET FirstName=?, LastName=?, email=?, role=?, school_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $first, $last, $email, $role, $school_id, $user_id);
    }

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">User details updated!</div>';
        $stmt->close();

        // Refresh user data
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
     <title>Edit User</title>
    <!-- Bootstrap 5 CDN -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-outline-primary mb-3">&larr; Back to Dashboard</a>

    <div class="card">
        <div class="card-header">
            <h4>Edit Profile</h4>
        </div>
        <div class="card-body">
            <?= $message ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="FirstName" class="form-control" value="<?= htmlspecialchars($user['FirstName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="LastName" class="form-control" value="<?= htmlspecialchars($user['LastName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>

                         <?php if ($_SESSION['role'] === 'Superadmin'): ?>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                <option value="dean" <?= $user['role'] === 'dean' ? 'selected' : '' ?>>Dean</option>
                                <option value="hoi" <?= $user['role'] === 'hoi' ? 'selected' : '' ?>>HOI</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>>SuperAdmin</option>
                            </select>
                        </div>
                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                <option value="dean" <?= $user['role'] === 'dean' ? 'selected' : '' ?>>Dean</option>
                                <option value="hoi" <?= $user['role'] === 'hoi' ? 'selected' : '' ?>>HOI</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>

                            </select>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] !== 'Superadmin'): ?>
                        <div class="mb-3">
                            <label class="form-label">School</label>
                            <select name="school_id" class="form-select" required>
                                <option value="">Select School</option>
                                <?php
                                mysqli_data_seek($schools, 0); // reset pointer if needed
                                while ($school = mysqli_fetch_assoc($schools)): ?>
                                    <option value="<?= $school['id'] ?>" <?= $user['school_id'] == $school['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($school['school_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    <?php endif; ?>


            <!-- submit button -->
            <button type="submit" class="btn btn-primary">Update Details</button>

            </form>
        </div>
    </div>
</div>
</body>
</html>
