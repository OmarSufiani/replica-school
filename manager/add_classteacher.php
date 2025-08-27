<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

// Only allow logged-in admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = intval($_POST['teacher_id']);
    $class_id   = intval($_POST['class_id']);

    if ($teacher_id && $class_id) {
        // Check if class already has a teacher
        $check = $conn->prepare("SELECT id FROM class_teachers WHERE class_id=? AND school_id=?");
        $check->bind_param("ii", $class_id, $school_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "<div class='alert alert-warning'>⚠️ This class already has a class teacher assigned!</div>";
        } else {
            // Get teacher name from teacher table
            $tq = $conn->prepare("SELECT name FROM teacher WHERE id=? AND school_id=?");
            $tq->bind_param("ii", $teacher_id, $school_id);
            $tq->execute();
            $tq->bind_result($teacher_name);
            $tq->fetch();
            $tq->close();

            if ($teacher_name) {
                $stmt = $conn->prepare("INSERT INTO class_teachers (name, class_id, school_id) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $teacher_name, $class_id, $school_id);
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>✅ Class teacher assigned successfully!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>❌ Error: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>❌ Teacher not found in this school.</div>";
            }
        }
        $check->close();
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Please select both teacher and class.</div>";
    }
}

// Fetch teachers of this school
$teachers = $conn->query("SELECT id, name FROM teacher WHERE school_id = $school_id ORDER BY name ASC");

// Fetch classes of this school
$classes = $conn->query("SELECT id, name FROM class WHERE school_id = $school_id ORDER BY name ASC");

// Fetch already assigned
$assigned = $conn->query("SELECT ct.id, ct.name AS teacher_name, c.name AS class_name
                          FROM class_teachers ct
                          JOIN class c ON ct.class_id = c.id
                          WHERE ct.school_id = $school_id");
?>
<!DOCTYPE html>

<head>
 <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add Class Teacher</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">


<div class="container mt-5">


    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
  
          <h5>Assign Class Teacher</h5>
        </div>
        <div class="card-body">
          <?= $message ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Select Teacher</label>
              <select name="teacher_id" class="form-select" required>
                <option value="">-- Choose Teacher --</option>
                <?php while($t = $teachers->fetch_assoc()){ ?>
                  <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Select Class</label>
              <select name="class_id" class="form-select" required>
                <option value="">-- Choose Class --</option>
                <?php while($c = $classes->fetch_assoc()){ ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success">Assign</button>
            </div>
          </form>
        </div>
      </div>

      <?php if ($assigned->num_rows > 0) { ?>
      <div class="card mt-4 shadow">
        <div class="card-header bg-secondary text-white text-center">
          <h6>Assigned Class Teachers</h6>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Teacher</th>
                <th>Class</th>
              </tr>
            </thead>
            <tbody>
              <?php $i=1; while($a = $assigned->fetch_assoc()){ ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($a['teacher_name']) ?></td>
                <td><?= htmlspecialchars($a['class_name']) ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php } ?>

    </div>
  </div>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</html>
