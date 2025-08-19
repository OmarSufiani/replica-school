<?php
session_start();
include 'db.php'; // database connection
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: ../login.php');
    exit();
}
// Fetch all schools with number of users (excluding role='User')
$query = "
    SELECT s.*, 
           COUNT(u.id) AS user_count
    FROM school s
    LEFT JOIN users u 
           ON u.school_id = s.id AND u.role != 'User'
    GROUP BY s.id
    ORDER BY s.created_at ASC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Schools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">

<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">
    &larr; Back to Dashboard
</a>
    <h2 class="mb-4 text-center">List of Schools</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>School Name</th>
                    <th>School Code</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Number of Users</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($school = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $school['id'] ?></td>
                        <td><?= htmlspecialchars($school['school_name']) ?></td>
                        <td><?= htmlspecialchars($school['school_code']) ?></td>
                        <td><?= htmlspecialchars($school['address']) ?></td>
                        <td><?= htmlspecialchars($school['phone']) ?></td>
                        <td><?= htmlspecialchars($school['email']) ?></td>
                        <td><?= $school['user_count'] ?></td>
                        <td><?= $school['created_at'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
