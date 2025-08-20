<?php

session_start(); // start the session
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Redirect to login if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Allow only Superadmin
if ($_SESSION['role'] !== 'Superadmin') {
    echo "<div style='text-align:center; padding-top:50px; font-family:sans-serif;'>
            <h2>Access Denied</h2>
            <p>You do not have permission to access this page.</p>
          </div>";
    exit;
}

include 'db.php';


$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['school_id'], $_POST['status'])) {
    $school_id = intval($_POST['school_id']);
    $new_status = intval($_POST['status']);

    $stmt = $conn->prepare("UPDATE school SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $school_id);

    if ($stmt->execute()) {
        $success = "School status updated successfully.";
    } else {
        $error = "Failed to update school status.";
    }
    $stmt->close();
}

// Get all schools
$result = $conn->query("SELECT id, school_name, school_code, address, phone, email, created_at, status FROM school ORDER BY id ASC");
$schools = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">
    &larr; Back to Dashboard
</a>
    <h2 class="mb-4">Manage School Status</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>School Name</th>
                    <th>School Code</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($schools as $school): ?>
                <tr>
                    <td><?= $school['id'] ?></td>
                    <td><?= htmlspecialchars($school['school_name']) ?></td>
                    <td><?= htmlspecialchars($school['school_code']) ?></td>
                    <td><?= htmlspecialchars($school['address']) ?></td>
                    <td><?= htmlspecialchars($school['phone']) ?></td>
                    <td><?= htmlspecialchars($school['email']) ?></td>
                    <td><?= $school['created_at'] ?></td>
                    <td>
                        <form method="POST" class="d-flex justify-content-center align-items-center gap-2">
                            <input type="hidden" name="school_id" value="<?= $school['id'] ?>">
                            <select name="status" class="form-select form-select-sm w-auto">
                                <option value="1" <?= $school['status'] == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $school['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                    </td>
                    <td>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
