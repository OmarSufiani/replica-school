<?php
// manage_users_content.php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

// Only allow logged-in users
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo '<div class="alert alert-danger">Unauthorized Access</div>';
    return;
}

// Handle Add/Edit without redirect
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname  = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $role      = mysqli_real_escape_string($conn, $_POST['role']);
    $school_id = intval($_POST['school_id']);

    if ($id > 0) {
        if ($password) {
            $query = "UPDATE users SET FirstName='$firstname', LastName='$lastname', email='$email', password='$password', role='$role', school_id='$school_id' WHERE id=$id";
        } else {
            $query = "UPDATE users SET FirstName='$firstname', LastName='$lastname', email='$email', role='$role', school_id='$school_id' WHERE id=$id";
        }
        $success = mysqli_query($conn, $query) ? "User updated successfully." : "Failed to update user: " . mysqli_error($conn);
    } else {
        $query = "INSERT INTO users (FirstName, LastName, email, password, role, school_id, created_at)
                  VALUES ('$firstname', '$lastname', '$email', '$password', '$role', '$school_id', NOW())";
        $success = mysqli_query($conn, $query) ? "User added successfully." : "Failed to add user: " . mysqli_error($conn);
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $success = mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id") ? "User deleted successfully." : "Failed to delete user: " . mysqli_error($conn);
}

// Fetch Edit user if requested
$edit_user = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM users WHERE id = $edit_id");
    $edit_user = mysqli_fetch_assoc($edit_result);
}

// Fetch users based on role
if ($_SESSION['role'] === 'Superadmin') {
    $query = "SELECT * FROM users WHERE role IN ('admin','dean') ORDER BY created_at ASC";
} elseif ($_SESSION['role'] === 'admin') {
    $school_id = $_SESSION['school_id'];
    $query = "SELECT * FROM users WHERE role='dean' AND school_id=$school_id ORDER BY created_at ASC";
} elseif ($_SESSION['role'] === 'dean') {
    $school_id = $_SESSION['school_id'];
    $query = "SELECT * FROM users WHERE role IN ('teacher','user') AND school_id=$school_id ORDER BY created_at ASC";
} else {
    echo '<div class="alert alert-danger">Unauthorized Access</div>';
    return;
}

$result = mysqli_query($conn, $query);
?>

<!-- Flash Messages -->
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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
                        <option value="user" <?= isset($edit_user['role']) && $edit_user['role']=='user'?'selected':'' ?>>User</option>
                        <option value="teacher" <?= isset($edit_user['role']) && $edit_user['role']=='teacher'?'selected':'' ?>>Teacher</option>
                        <?php if ($_SESSION['role'] !== 'dean'): ?>
                        <option value="dean" <?= isset($edit_user['role']) && $edit_user['role']=='dean'?'selected':'' ?>>Dean</option>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] === 'Superadmin'): ?>
                        <option value="admin" <?= isset($edit_user['role']) && $edit_user['role']=='admin'?'selected':'' ?>>Admin</option>
                        <option value="Superadmin" <?= isset($edit_user['role']) && $edit_user['role']=='Superadmin'?'selected':'' ?>>Superadmin</option>
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
                <a href="?page=manage_users" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Users Table -->
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
            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['FirstName']) ?></td>
                    <td><?= htmlspecialchars($user['LastName']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['school_id']) ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td>
                        <?php if (in_array($_SESSION['role'], ['dean','admin','Superadmin'])): ?>
                            <a href="?page=manage_users&edit_id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?page=manage_users&delete_id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
