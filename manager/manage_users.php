<?php
session_start();
include 'db.php';



// Only allow logged-in admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}
// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $school_id = intval($_POST['school_id']);

    if ($id > 0) {
        // Update user
        if ($password) {
            $query = "UPDATE users SET FirstName='$firstname', LastName='$lastname', email='$email', password='$password', role='$role', school_id='$school_id' WHERE id=$id";
        } else {
            $query = "UPDATE users SET FirstName='$firstname', LastName='$lastname', email='$email', role='$role', school_id='$school_id' WHERE id=$id";
        }
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "User updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update user: " . mysqli_error($conn);
        }
    } else {
        // Add new user
        $query = "INSERT INTO users (FirstName, LastName, email, password, role, school_id, created_at)
                  VALUES ('$firstname', '$lastname', '$email', '$password', '$role', '$school_id', NOW())";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "User added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add user: " . mysqli_error($conn);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if (mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id")) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user: " . mysqli_error($conn);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch Users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

// Handle Edit
$edit_user = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM users WHERE id = $edit_id");
    $edit_user = mysqli_fetch_assoc($edit_result);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
     <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Auto-hide alerts after 3 seconds
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(() => {
                let alerts = document.querySelectorAll(".alert");
                alerts.forEach(alert => {
                    alert.classList.add("fade");
                    setTimeout(() => alert.remove(), 500);
                });
            }, 3000);
        });
    </script>
</head>
<body class="bg-light">

<div class="container mt-5">

    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>
    <h3 class="mb-4">Manage Users</h3>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- User Form -->
    <div class="card mb-4">
        <div class="card-header"><?= $edit_user ? "Edit User" : "Add User" ?></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit_user['id'] ?? '' ?>">

                <div class="row mb-3">
                    <div class="col">
                        <label>First Name</label>
                        <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($edit_user['FirstName'] ?? '') ?>" required>
                    </div>
                    <div class="col">
                        <label>Last Name</label>
                        <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($edit_user['LastName'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label>Password <?= $edit_user ? "(Leave blank to keep current)" : "" ?></label>
                    <input type="password" name="password" class="form-control" <?= $edit_user ? "" : "required" ?>>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="user" <?= isset($edit_user['role']) && $edit_user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                            <option value="student" <?= isset($edit_user['role']) && $edit_user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="teacher" <?= isset($edit_user['role']) && $edit_user['role'] == 'teacher' ? 'selected' : '' ?>>Teacher</option>
                            <option value="dean" <?= isset($edit_user['role']) && $edit_user['role'] == 'dean' ? 'selected' : '' ?>>Dean</option>
                            <option value="admin" <?= isset($edit_user['role']) && $edit_user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Superadmin'): ?>
                                <option value="Superadmin" <?= isset($edit_user['role']) && $edit_user['role'] == 'Superadmin' ? 'selected' : '' ?>>Superadmin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label>School ID</label>
                        <input type="number" name="school_id" class="form-control" value="<?= htmlspecialchars($edit_user['school_id'] ?? '') ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?= $edit_user ? "Update User" : "Add User" ?></button>
                <?php if ($edit_user): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php


// Check role and adjust query
if ($_SESSION['role'] === 'Superadmin') {
    // Superadmin sees only Admin users from all schools
    $query = "SELECT * FROM users WHERE role = 'admin' ORDER BY created_at ASC";
} else if ($_SESSION['role'] === 'admin') {
    // Admin sees all users from their school EXCEPT Superadmin
    $school_id = $_SESSION['school_id'];
    $query = "SELECT * FROM users WHERE role != 'Superadmin' AND school_id = $school_id ORDER BY created_at ASC";
} else {
    die("Unauthorized Access");
}

$result = mysqli_query($conn, $query);
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>School ID</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['FirstName']) ?></td>
                    <td><?= htmlspecialchars($user['LastName']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['school_id']) ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td>
                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'Superadmin'])): ?>
                            <a href="?edit_id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete_id=<?= $user['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
