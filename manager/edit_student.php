<?php
session_start();
include 'db.php';

// ✅ Only admin or dean can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','dean'])) {
    die("<div class='alert alert-danger'>Unauthorized access</div>");
}

$school_id = $_SESSION['school_id'];
$message = "";

// ✅ Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    $status     = $_POST['status'];

    $update_sql = "UPDATE student SET status=? WHERE id=? AND school_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sii", $status, $student_id, $school_id);
    if ($stmt->execute()) {
        $_SESSION['flash_msg'] = "<div class='alert alert-success' id='flashMsg'>✅ Status updated successfully!</div>";
    } else {
        $_SESSION['flash_msg'] = "<div class='alert alert-danger' id='flashMsg'>❌ Failed to update status.</div>";
    }

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ✅ Flash message
if (isset($_SESSION['flash_msg'])) {
    $message = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

// ✅ Fetch students belonging to this school + their class
$sql = "SELECT s.id, s.firstname, s.lastname, s.gender, s.dob, 
               s.guardian_name, s.guardian_phone, s.address, s.status,
               c.name AS class_name       -- ✅ get class name
        FROM student s
        LEFT JOIN class c ON s.class_id = c.id   -- ✅ join class table
        WHERE s.school_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Students List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light p-4">
<div class="container">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>
    <h2 class="mb-4">Students List</h2>

    <?= $message ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Class</th> <!-- ✅ Added Class column -->
                <th>Gender</th>
                <th>DOB</th>
                <th>Guardian</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['class_name'] ?? 'N/A') ?></td> <!-- ✅ Show class -->
                    <td><?= htmlspecialchars($row['gender']) ?></td>
                    <td><?= htmlspecialchars($row['dob']) ?></td>
                    <td><?= htmlspecialchars($row['guardian_name']) ?></td>
                    <td><?= htmlspecialchars($row['guardian_phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <!-- ✅ Open Modal Button -->
                        <button 
                            class="btn btn-sm btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal" 
                            data-id="<?= $row['id'] ?>" 
                            data-name="<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>" 
                            data-status="<?= $row['status'] ?>">
                            Edit Status
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- ✅ Bootstrap Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Edit Status for <span id="studentName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="student_id" id="studentId">
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" id="studentStatus" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="graduated">Graduated</option>
                    <option value="inactive">Inactive</option>
                    <option value="transferred">Transferred</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// ✅ Pass student data to modal
const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const status = button.getAttribute('data-status');

    document.getElementById('studentId').value = id;
    document.getElementById('studentName').innerText = name;
    document.getElementById('studentStatus').value = status;
});

// ✅ Auto-hide message after 3s
setTimeout(() => {
    let msg = document.getElementById('flashMsg');
    if (msg) {
        msg.style.transition = "opacity 1s";
        msg.style.opacity = "0";
        setTimeout(() => msg.remove(), 1000);
    }
}, 3000);
</script>
</body>
</html>
