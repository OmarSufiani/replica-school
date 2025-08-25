<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

// Only allow logged-in admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$message = '';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
 

    if ($name === '') {
        $message = "❌ Class name cannot be empty.";
    } else {
        // Insert class with school_id and school_code
        $stmt = $conn->prepare("INSERT INTO class (name, school_id) VALUES ( ?, ?)");
        $stmt->bind_param("si", $name, $school_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Class added successfully!";
        } else {
            $_SESSION['message'] = "❌ Failed to add class. Please try again. Error: " . $stmt->error;
        }

        $stmt->close();
     header("Location: dashboard.php?page=add_class");
        exit();
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const alertBox = document.getElementById("message-box");
            if (alertBox) {
                setTimeout(() => alertBox.style.display = "none", 3000);
            }
        });
    </script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
           

            <h4 class="mb-4">Add New Class</h4>

            <?php if ($message): ?>
                <div id="message-box" class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="d-grid gap-3">
             
                <div class="form-group">
                    <label for="name" class="form-label">Class Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g., 7BLUE, 7RED" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Class</button>
            </form>
        </div>
    </div>
</div>
</body>

