<!-- Main content -->
<main class="col-lg-10 col-md-9 ms-sm-auto px-4 py-4">
    <?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        $allowed_pages = ['dashboard', 'users', 'jobs', 'applications', 'settings']; // whitelist

        if (in_array($page, $allowed_pages)) {
            include $page . ".php"; 
        } else {
            echo "<div class='alert alert-danger'>Page not found!</div>";
        }
    } else {
        include "dashboard.php"; // default page
    }
    ?>
</main>


<a class="nav-link" href="?page=users">Manage Users</a>
