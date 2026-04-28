<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <a class="nav-link" data-widget="pushmenu">
        <i class="fas fa-bars"></i>
    </a>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</nav>