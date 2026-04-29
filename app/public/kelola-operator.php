<?php
session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/UserController.php';

// 🔐 HANYA ADMIN
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard-operator.php");
    exit();
}

// HANDLE REQUEST
$edit_data = handleUserRequest($conn);

// DATA DROPDOWN
$kelurahan_list = $conn->query("SELECT * FROM wilayah ORDER BY kelurahan ASC");

// DATA USER (operator + viewer)
$result = $conn->query("SELECT * FROM users WHERE role IN ('operator','viewer')");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />


    <style>
        .dt-buttons .btn {
            margin-left: 5px;
        }

        #operatorTable td:first-child {
            text-align: center;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">

        <?php include __DIR__ . '/../views/layout/header.php'; ?>
        <?php include __DIR__ . '/../views/layout/sidebar.php'; ?>

        <div class="content-wrapper">
            <section class="content p-3">
                <div class="container-fluid">

                    <?php if (isset($_GET['status'])): ?>

                        <script>
                            document.addEventListener("DOMContentLoaded", function () {

                                let type = "<?= $_GET['status'] ?>";

                                let config = {
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: ''
                                };

                                if (type === 'created') {
                                    config.text = 'User berhasil ditambahkan';
                                }

                                if (type === 'updated') {
                                    config.text = 'User berhasil diupdate';
                                }

                                if (type === 'deleted') {
                                    config.icon = 'warning';
                                    config.text = 'User berhasil dihapus';
                                }

                                Swal.fire(config);

                            });
                        </script>

                    <?php endif; ?>

                    <!-- ========================= -->
                    <!-- FORM -->
                    <!-- ========================= -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><?= $edit_data ? 'Edit User' : 'Tambah User' ?></h3>
                        </div>

                        <form method="POST">
                            <div class="card-body">

                                <?php if ($edit_data): ?>
                                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                                <?php endif; ?>

                                <input type="text" name="username" class="form-control mb-2" placeholder="Username"
                                    value="<?= $edit_data['username'] ?? '' ?>" required>

                                <input type="password" name="password" class="form-control mb-2" placeholder="Password">

                                <!-- ROLE -->
                                <select name="role" class="form-control mb-2" required>
                                    <option value="operator" <?= ($edit_data['role'] ?? '') === 'operator' ? 'selected' : '' ?>>Operator</option>
                                    <option value="viewer" <?= ($edit_data['role'] ?? '') === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                </select>

                                <!-- KELURAHAN -->
                                <div id="kelurahanField">
                                    <select name="kelurahan" class="form-control mb-2">
                                        <option value="">-- Pilih Kelurahan --</option>
                                        <?php foreach ($kelurahan_list as $k): ?>
                                            <option value="<?= $k['kelurahan'] ?>" <?= (isset($edit_data['kelurahan']) && $edit_data['kelurahan'] == $k['kelurahan']) ? 'selected' : '' ?>>
                                                <?= $k['kelurahan'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" name="<?= $edit_data ? 'update' : 'tambah' ?>"
                                    class="btn btn-primary">
                                    <?= $edit_data ? 'Update' : 'Tambah' ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ========================= -->
                    <!-- TABLE -->
                    <!-- ========================= -->
                    <div class="card">
                        <div class="card-body">

                            <table id="operatorTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Kelurahan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>

                                            <td>
                                                <span
                                                    class="badge <?= $row['role'] === 'viewer' ? 'badge-info' : 'badge-success' ?>">
                                                    <?= $row['role'] ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?= $row['role'] === 'viewer'
                                                    ? '<i>Semua Kelurahan</i>'
                                                    : htmlspecialchars($row['kelurahan']) ?>
                                            </td>

                                            <td>
                                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus?')"
                                                    class="btn btn-danger btn-sm">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </section>
        </div>

        <?php include __DIR__ . '/../views/layout/footer.php'; ?>

    </div>

    <!-- ========================= -->
    <!-- JS -->
    <!-- ========================= -->

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function () {
            $('#operatorTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                columnDefs: [{ targets: 0, orderable: false }],
                order: [[1, 'asc']],
                drawCallback: function () {
                    var api = this.api();
                    var start = api.page.info().start;
                    api.column(0).nodes().each(function (cell, i) {
                        cell.innerHTML = start + i + 1;
                    });
                }
            });
        });

        // 🔥 TOGGLE KELURAHAN
        const roleSelect = document.querySelector('[name="role"]');
        const kelField = document.getElementById('kelurahanField');

        function toggleKelurahan() {
            if (roleSelect.value === 'viewer') {
                kelField.style.display = 'none';
            } else {
                kelField.style.display = 'block';
            }
        }

        roleSelect.addEventListener('change', toggleKelurahan);
        toggleKelurahan();
    </script>

</body>

</html>