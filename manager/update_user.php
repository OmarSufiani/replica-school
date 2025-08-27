<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>Unauthorized access</div>";
    return;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch all schools for dropdown
$schools = mysqli_query($conn, "SELECT id, school_name FROM school");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = $_POST['FirstName'];
    $last = $_POST['LastName'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Build update query
    $sql = "UPDATE users SET FirstName=?, LastName=?, email=?";
    $params = [$first, $last, $email];
    $types = "sss";

    if ($password) {
        $sql .= ", password=?";
        $params[] = $password;
        $types .= "s";
    }

    // Role update
    if (in_array($_SESSION['role'], ['admin','Superadmin']) && isset($_POST['role'])) {
        $sql .= ", role=?";
        $params[] = $_POST['role'];
        $types .= "s";
    }

    // School update
    if ($_SESSION['role'] === 'Superadmin' && isset($_POST['school_id']) && $_POST['school_id'] !== "") {
        $sql .= ", school_id=?";
        $params[] = $_POST['school_id'];
        $types .= "i";
    }

    $sql .= " WHERE id=?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['flash_msg'] = "<div class='alert alert-success' id='flashMsg'>✅ User details updated!</div>";
        $stmt->close();

        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $_SESSION['flash_msg'] = "<div class='alert alert-danger' id='flashMsg'>❌ Error: {$stmt->error}</div>";
    }

    // Stay on the page after submission
    header("Location: dashboard.php?page=update_user");
    exit();
}

// Flash message
if (isset($_SESSION['flash_msg'])) {
    $message = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <title>Document</title>
</head>
<body>
    
</body>
</html>
<div class="container py-4">
    <h2 class="mb-4">Edit Profile</h2>

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

        <button type="submit" class="btn btn-primary">Update Details</button>
    </form>
</div>

<script>
// Auto-hide flash message
setTimeout(() => {
    const msg = document.getElementById('flashMsg');
    if (msg) {
        msg.style.transition = "opacity 1s";
        msg.style.opacity = "0";
        setTimeout(() => msg.remove(), 1000);
    }
}, 3000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
