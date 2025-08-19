<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}

$role = $_SESSION['role'] ?? '';
$school_id = intval($_SESSION['school_id']); // use session school_id

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM student WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $delete_id, $school_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student deleted successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting student: " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle update
if (isset($_POST['update_student'])) {
    $id = intval($_POST['id']);
    $admno = trim($_POST['admno']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $guardian_name = trim($_POST['guardian_name']);
    $guardian_phone = trim($_POST['guardian_phone']);
    $address = trim($_POST['address']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE student 
            SET admno=?, firstname=?, lastname=?, gender=?, dob=?, guardian_name=?, guardian_phone=?, address=?, status=? 
            WHERE id=? AND school_id=?");
    $stmt->bind_param("ssssssssssi", $admno, $firstname, $lastname, $gender, $dob, $guardian_name, $guardian_phone, $address, $status, $id, $school_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student updated successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating student: " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// If editing, fetch student data
$edit_student = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_sql = "
        SELECT s.*, sch.school_name, c.name AS class_name
        FROM student s
        LEFT JOIN school sch ON s.school_id = sch.id
        LEFT JOIN class c ON s.class_id = c.id
        WHERE s.id = $edit_id AND s.school_id = $school_id
        LIMIT 1
    ";
    $edit_result = mysqli_query($conn, $edit_sql);
    $edit_student = mysqli_fetch_assoc($edit_result);
}

// Pagination setup
$limit = 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Fetch students for logged-in school only
$total_sql = "SELECT COUNT(*) as total FROM student WHERE school_id = $school_id";
$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_students = $total_row['total'];
$total_pages = ceil($total_students / $limit);

$student_sql = "
    SELECT s.*, sch.school_name, c.name AS class_name 
    FROM student s
    LEFT JOIN school sch ON s.school_id = sch.id
    LEFT JOIN class c ON s.class_id = c.id
    WHERE s.school_id = $school_id
    ORDER BY s.id DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $student_sql);
?>

<!DOCTYPE html>

<head>
    
    <title>Manage Students</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">&larr; Back to Dashboard</a>
    <h2 class="mb-4">Manage Students</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?=$_SESSION['msg_type']?> alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['msg_type']); ?>
    <?php endif; ?>

    <?php if ($edit_student): ?>
        <h4>Edit Student</h4>
        <form method="post" class="mb-4">
            <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Admission No</label>
                    <input type="text" name="admno" class="form-control" value="<?= htmlspecialchars($edit_student['admno']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>First Name</label>
                    <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($edit_student['firstname']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Last Name</label>
                    <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($edit_student['lastname']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Gender</label>
                    <select name="gender" class="form-control" required>
                        <option value="Male" <?= $edit_student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $edit_student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>DOB</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($edit_student['dob']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Guardian Name</label>
                    <input type="text" name="guardian_name" class="form-control" value="<?= htmlspecialchars($edit_student['guardian_name']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Guardian Phone</label>
                    <input type="text" name="guardian_phone" class="form-control" value="<?= htmlspecialchars($edit_student['guardian_phone']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($edit_student['address']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Active" <?= $edit_student['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $edit_student['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="update_student" class="btn btn-success">Update</button>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Cancel</a>
        </form>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Adm No</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Class</th>
                <th>DOB</th>
                <th>Guardian</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Status</th>
                <th>School</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['admno']) ?></td>
                <td><?= htmlspecialchars($row['firstname'] . " " . $row['lastname']) ?></td>
                <td><?= htmlspecialchars($row['gender']) ?></td>
                <td><?= htmlspecialchars($row['class_name']) ?></td>
                <td><?= htmlspecialchars($row['dob']) ?></td>
                <td><?= htmlspecialchars($row['guardian_name']) ?></td>
                <td><?= htmlspecialchars($row['guardian_phone']) ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['school_name']) ?></td>
                <td>
                    <a href="?edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?= ($page == $p) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

</body>
</html>
