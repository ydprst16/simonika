<?php
session_start();
require_once __DIR__ . '/../config/conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM user_log ORDER BY created_at DESC LIMIT 200");

/* ======================
FILTER
====================== */

$search = $_GET['search'] ?? '';
$user_filter = $_GET['user'] ?? '';
$date = $_GET['date'] ?? '';

$where = [];

if ($search) {
    $where[] = "aktivitas LIKE '%" . $conn->real_escape_string($search) . "%'";
}

if ($user_filter) {
    $where[] = "username='" . $conn->real_escape_string($user_filter) . "'";
}

if ($date) {
    $where[] = "DATE(created_at)='" . $conn->real_escape_string($date) . "'";
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT * FROM user_log $where_sql ORDER BY created_at DESC LIMIT 300";

$result = $conn->query($query);

/* ======================
STATISTIK
====================== */

$login_success = $conn->query("
SELECT COUNT(*) as total 
FROM user_log 
WHERE aktivitas LIKE '%Login berhasil%'
")->fetch_assoc()['total'];

$login_failed = $conn->query("
SELECT COUNT(*) as total 
FROM user_log 
WHERE aktivitas LIKE '%Login gagal%'
")->fetch_assoc()['total'];

$password_change = $conn->query("
SELECT COUNT(*) as total 
FROM user_log 
WHERE aktivitas LIKE '%password%'
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Log Aktivitas</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">

</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">

    <div class="wrapper">

        <?php include __DIR__ . '/../views/layout/header.php'; ?>
        <?php include __DIR__ . '/../views/layout/sidebar.php'; ?>

        <!-- Content -->

        <div class="content-wrapper">

            <section class="content-header">

                <div class="container-fluid">
                    <h1>Log Aktivitas Sistem</h1>
                </div>

            </section>

            <section class="content">

                <div class="container-fluid">

                    <!-- Statistik -->

                    <div class="row mb-3">

                        <div class="col-md-4">

                            <div class="small-box bg-success">

                                <div class="inner">
                                    <h3><?php echo $login_success; ?></h3>
                                    <p>Login Berhasil</p>
                                </div>

                                <div class="icon">
                                    <i class="fas fa-check"></i>
                                </div>

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="small-box bg-danger">

                                <div class="inner">
                                    <h3><?php echo $login_failed; ?></h3>
                                    <p>Login Gagal</p>
                                </div>

                                <div class="icon">
                                    <i class="fas fa-times"></i>
                                </div>

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="small-box bg-info">

                                <div class="inner">
                                    <h3><?php echo $password_change; ?></h3>
                                    <p>Perubahan Password</p>
                                </div>

                                <div class="icon">
                                    <i class="fas fa-key"></i>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Filter -->

                    <form method="GET" class="mb-3">

                        <div class="row">

                            <div class="col-md-3">

                                <input type="text" name="search" class="form-control" placeholder="Cari aktivitas..."
                                    value="<?php echo htmlspecialchars($search); ?>">

                            </div>

                            <div class="col-md-3">

                                <select name="user" class="form-control">

                                    <option value="">Semua User</option>

                                    <?php
                                    $users = $conn->query("SELECT DISTINCT username FROM user_log");

                                    while ($u = $users->fetch_assoc()):
                                        ?>

                                        <option value="<?php echo $u['username']; ?>" <?php if ($user_filter == $u['username'])
                                               echo 'selected'; ?>>
                                            <?php echo $u['username']; ?>
                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>


                            <div class="col-md-3">

                                <input type="date" name="date" class="form-control"
                                    value="<?php echo htmlspecialchars($date); ?>">

                            </div>

                            <div class="col-md-3">

                                <button class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>

                                <a href="log-aktivitas.php" class="btn btn-secondary">
                                    Reset
                                </a>

                            </div>

                        </div>

                    </form>

                    <!-- Table -->

                    <div class="card">

                        <div class="card-header">
                            <h3 class="card-title">Riwayat Aktivitas</h3>
                        </div>

                        <div class="card-body">

                            <table id="logTable" class="table table-bordered table-hover">

                                <thead>

                                    <tr>

                                        <th>No</th>
                                        <th>Waktu</th>
                                        <th>User</th>
                                        <th>Aktivitas</th>
                                        <th>IP</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php
                                    $no = 1;

                                    while ($row = $result->fetch_assoc()):
                                        ?>

                                        <tr>

                                            <td><?php echo $no++; ?></td>

                                            <td><?php echo $row['created_at']; ?></td>

                                            <td><?php echo htmlspecialchars($row['username']); ?></td>

                                            <td><?php echo htmlspecialchars($row['aktivitas']); ?></td>

                                            <td><?php echo $row['ip_address'] ?? '-'; ?></td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </section>

        </div>

        <!-- FOOTER  -->
        <?php include __DIR__ . '/../views/layout/footer.php'; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>


    <script>
        $(function () {
            $('#logTable').DataTable({
                "pageLength": 10, // 🔥 ini kunci utama
                "lengthMenu": [10, 25, 50, 100],
                "ordering": true,
                "searching": true,
                "responsive": true,
                "autoWidth": false
            });
        });
    </script>

</body>

</html>