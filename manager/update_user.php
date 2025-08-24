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
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Start building update query
    $sql = "UPDATE users SET FirstName=?, LastName=?, email=?";
    $params = [$first, $last, $email];
    $types = "sss";

    // If password is provided
    if ($password) {
        $sql .= ", password=?";
        $params[] = $password;
        $types .= "s";
    }

    // Role: Only admin or superadmin can change
    if (in_array($_SESSION['role'], ['admin','Superadmin']) && isset($_POST['role'])) {
        $sql .= ", role=?";
        $params[] = $_POST['role'];
        $types .= "s";
    }

    // School: Only superadmin can change
    if ($_SESSION['role'] === 'Superadmin' && isset($_POST['school_id']) && $_POST['school_id'] !== "") {
        $sql .= ", school_id=?";
        $params[] = $_POST['school_id'];
        $types .= "i";
    }

    // Finish query
    $sql .= " WHERE id=?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

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
            <?php if (!empty($message)): ?>
                <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    setTimeout(function() {
                        let alert = document.getElementById('successAlert');
                        if (alert) {
                            alert.classList.remove('show');
                            alert.classList.add('fade');
                        }
                    }, 3000);

                    setTimeout(function() {
                        window.location.reload();
                    }, 3000);
                </script>
            <?php endif; ?>

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

                <!-- ROLE -->
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <?php if (in_array($_SESSION['role'], ['Superadmin'])): ?>
                        <select name="role" class="form-select" required>
                            <?php
                            $roles = ['user','student','teacher','dean','hoi','admin','superadmin'];
                            foreach ($roles as $r):
                            ?>
                                <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                        <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
                    <?php endif; ?>
                </div>

                <!-- SCHOOL -->
                <div class="mb-3">
                    <label class="form-label">School</label>
                    <?php if ($_SESSION['role'] === 'Superadmin'): ?>
                        <select name="school_id" class="form-select" required>
                            <option value="">Select School</option>
                            <?php mysqli_data_seek($schools, 0);
                            while ($school = mysqli_fetch_assoc($schools)): ?>
                                <option value="<?= $school['id'] ?>" <?= $user['school_id'] == $school['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($school['school_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['school_id']) ?>" readonly>
                        <input type="hidden" name="school_id" value="<?= htmlspecialchars($user['school_id']) ?>">
                    <?php endif; ?>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary">Update Details</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
