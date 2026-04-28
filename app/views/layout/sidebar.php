<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <a href="dashboard-admin.php" class="brand-link text-center">
        <span class="brand-text font-weight-light">SiMonika</span>
    </a>

    <div class="sidebar">
        <nav>
            <ul class="nav nav-pills nav-sidebar flex-column">

                <li class="nav-item">
                    <a href="dashboard-admin.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard-admin.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Monografi</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola-operator.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'kelola-operator.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Operator</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="log-aktivitas.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'log-aktivitas.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Log Aktivitas</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>

</aside>