<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Example: role comes from session after login
// Possible values: 'superadmin', 'admin', 'user'
$userRole = $_SESSION['role'] ?? 'user';

$uploadDir = 'uploads/';

// Recursive function to get all files inside a directory and its subdirectories
function getFiles($dir) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $files = array_merge($files, getFiles($path));
        } else {
            $files[] = $path;
        }
    }
    return $files;
}

$files = getFiles($uploadDir);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Ramzy School System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body class="bg-light">

<div class="container py-5">  
  <a href="dashboard.php" class="btn btn-outline-primary me-2">← Back to Dashboard</a>



  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">📁 Admin Dashboard - File Manager</h1>
    <div>
    
      <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>
  </div>

  <h3 class="mb-3">Uploaded Files</h3>

  <?php if (count($files) === 0): ?>
    <div class="alert alert-info">No files uploaded yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-secondary">
          <tr>
            <th>#</th>
            <th>File Name</th>
            <th>Size (KB)</th>
            <th>Download</th>
            <?php if ($userRole === 'Superadmin'): ?>
              <th>Delete</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        foreach ($files as $filePath) {
            $relativePath = substr($filePath, strlen($uploadDir));
            $fileSize = round(filesize($filePath) / 1024, 2);
            $downloadLink = $filePath;

            echo "<tr>
                    <td>{$i}</td>
                    <td>" . htmlspecialchars($relativePath) . "</td>
                    <td>{$fileSize}</td>
                    <td><a class='btn btn-sm btn-outline-primary' href='{$downloadLink}' download>Download</a></td>";

            // Show delete button only if superadmin
            if ($userRole === 'Superadmin') {
                echo "<td>
                        <form action='delete.php' method='get' onsubmit=\"return confirm('Are you sure you want to delete this file?');\">
                          <input type='hidden' name='file' value='" . htmlspecialchars($relativePath, ENT_QUOTES) . "'>
                          <button type='submit' class='btn btn-sm btn-danger'>Delete</button>
                        </form>
                      </td>";
            }

            echo "</tr>";
            $i++;
        }
        ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
