<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: ../login.php');
    exit();
}


$role = $_SESSION['role'] ?? '';

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if (mysqli_query($conn, "DELETE FROM student WHERE id = $delete_id")) {
        $_SESSION['message'] = "Student deleted successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting student: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?school_id=" . ($_GET['school_id'] ?? 0));
    exit;
}

// Handle school filter
$selected_school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;

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
            WHERE id=?");
    $stmt->bind_param("sssssssssi", $admno, $firstname, $lastname, $gender, $dob, $guardian_name, $guardian_phone, $address, $status, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student updated successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating student: " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?school_id=" . $selected_school_id);
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
        WHERE s.id = $edit_id
        LIMIT 1
    ";
    $edit_result = mysqli_query($conn, $edit_sql);
    $edit_student = mysqli_fetch_assoc($edit_result);
}

// Pagination setup
$limit = 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Fetch students only if a school is selected
$result = null;
$total_pages = 0;
if ($selected_school_id) {
    $total_sql = "SELECT COUNT(*) as total FROM student WHERE school_id = $selected_school_id";
    $total_result = mysqli_query($conn, $total_sql);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_students = $total_row['total'];
    $total_pages = ceil($total_students / $limit);

    $student_sql = "
        SELECT s.*, sch.school_name, c.name AS class_name 
        FROM student s
        LEFT JOIN school sch ON s.school_id = sch.id
        LEFT JOIN class c ON s.class_id = c.id
        WHERE s.school_id = $selected_school_id
        ORDER BY s.id DESC
        LIMIT $limit OFFSET $offset
    ";
    $result = mysqli_query($conn, $student_sql);
}

// Fetch all schools for dropdown
$schools = mysqli_query($conn, "SELECT id, school_name FROM school ORDER BY school_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">&larr; Back to Dashboard</a>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show" role="alert" id="msgAlert">
        <?= $_SESSION['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>
        setTimeout(function() {
            let alertEl = document.getElementById('msgAlert');
            if (alertEl) {
                alertEl.classList.remove('show');
                alertEl.classList.add('fade');
            }
        }, 3000);
    </script>
    <?php unset($_SESSION['message']); unset($_SESSION['msg_type']); ?>
<?php endif; ?>

<h3 class="mb-4">Student List</h3>

<!-- School Filter -->
<form method="GET" class="mb-3">
    <div class="row g-2">
        <div class="col-md-4">
            <select name="school_id" class="form-select" onchange="this.form.submit()">
                <option value="0">-- Select School --</option>
                <?php while ($school = mysqli_fetch_assoc($schools)): ?>
                    <option value="<?= $school['id'] ?>" <?= ($school['id'] == $selected_school_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($school['school_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
</form>

<!-- Edit Form -->
<?php if ($edit_student): ?>
<div class="card mb-4">
    <div class="card-header bg-warning"><strong>Edit Student</strong></div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
            <div class="row mb-2">
                <div class="col-md-3">
                    <label class="form-label">Adm No</label>
                    <input type="text" name="admno" class="form-control" value="<?= htmlspecialchars($edit_student['admno']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($edit_student['firstname']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($edit_student['lastname']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="Male" <?= $edit_student['gender']=='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= $edit_student['gender']=='Female'?'selected':'' ?>>Female</option>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label class="form-label">DOB</label>
                    <input type="date" name="dob" class="form-control" value="<?= $edit_student['dob'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Guardian Name</label>
                    <input type="text" name="guardian_name" class="form-control" value="<?= htmlspecialchars($edit_student['guardian_name']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Guardian Phone</label>
                    <input type="text" name="guardian_phone" class="form-control" value="<?= htmlspecialchars($edit_student['guardian_phone']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($edit_student['address']) ?>">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <input type="text" name="status" class="form-control" value="<?= htmlspecialchars($edit_student['status']) ?>">
                </div>
            </div>
            <button type="submit" name="update_student" class="btn btn-success">Update Student</button>
            <a href="<?= $_SERVER['PHP_SELF'].'?school_id='.$selected_school_id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($selected_school_id && $result && mysqli_num_rows($result) > 0): ?>
<div class="table-responsive">
    <table class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Adm No</th>
                <th>Name</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Guardian</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Status</th>
                <th>School</th>
                <th>Class</th>
                <th>Photo</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($student = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $student['id'] ?></td>
                    <td><?= htmlspecialchars($student['admno']) ?></td>
                    <td><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></td>
                    <td><?= $student['gender'] ?></td>
                    <td><?= $student['dob'] ?></td>
                    <td><?= htmlspecialchars($student['guardian_name']) ?></td>
                    <td><?= htmlspecialchars($student['guardian_phone']) ?></td>
                    <td><?= htmlspecialchars($student['address']) ?></td>
                    <td><?= $student['status'] ?></td>
                    <td><?= htmlspecialchars($student['school_name']) ?></td>
                    <td><?= htmlspecialchars($student['class_name']) ?></td>
                    <td>
                        <?php if (!empty($student['photo'])): ?>
                            <img src="uploads/students/<?= $student['photo'] ?>" width="50" height="50" alt="Photo">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= $_SERVER['PHP_SELF'].'?edit_id='.$student['id'].'&school_id='.$selected_school_id ?>" class="btn btn-sm btn-primary">Edit</a>
                        <?php if ($role === 'Superadmin' || $role === 'admin'): ?>
                            <a href="?delete_id=<?= $student['id'] ?>&school_id=<?= $selected_school_id ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">
    <?php for($p = 1; $p <= $total_pages; $p++): ?>
        <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&school_id=<?= $selected_school_id ?>"><?= $p ?></a>
        </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php elseif ($selected_school_id): ?>
    <div class="alert alert-info">No students found for the selected school.</div>
<?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
