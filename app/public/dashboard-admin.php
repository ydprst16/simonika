<?php
session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/AdminController.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


$result = getDashboardAdmin($conn);

$data = $result['data'];
$total_kelurahan = $result['total_kelurahan'];
$sudah_update = $result['sudah_update'];
$belum_update = $result['belum_update'];
$persentase = $result['persentase'];
$tahun_target = $result['tahun_target'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />


    <style>
        .col-custom {
            width: 20%;
        }

        @media(max-width: 1200px) {
            .col-custom {
                width: 25%;
            }
        }

        @media(max-width: 768px) {
            .col-custom {
                width: 50%;
            }
        }

        .card-body {
            font-size: 13.5px;
        }
    </style>

</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">

    <div class="wrapper">

        <?php include __DIR__ . '/../views/layout/header.php'; ?>
        <?php include __DIR__ . '/../views/layout/sidebar.php'; ?>

        <!-- CONTENT -->
        <div class="content-wrapper">
            <section class="content p-3">

                <h4>Dashboard Admin</h4>

                <!-- SUMMARY -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h4><?= $sudah_update ?></h4>
                                <p>Update</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h4><?= $belum_update ?></h4>
                                <p>Belum</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h4><?= $persentase ?>%</h4>
                                <p>Progress</p>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="text" id="searchInput" class="form-control mb-3" placeholder="Cari...">

                <div class="d-flex flex-wrap" id="kelurahanList">

                    <?php foreach ($data as $row): ?>

                        <div class="col-custom p-1 d-flex">
                            <div class="card shadow-sm w-100">

                                <div class="card-body p-2 d-flex flex-column">

                                    <b><?= htmlspecialchars($row['kelurahan']) ?></b>
                                    <small><?= $row['kecamatan'] ?></small>

                                    <?php if ($row['status'] == 'sudah'): ?>
                                        <div class="text-success mt-1">✔ <?= $tahun_target ?></div>
                                    <?php else: ?>
                                        <div class="text-danger mt-1">⚠ belum ada tahun <?= $tahun_target ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($row['tahun'])): ?>
                                        <div class="d-flex align-items-center mt-2 mb-2">

                                            <select class="form-control form-control-sm tahun-select mr-1"
                                                data-kelurahan="<?= htmlspecialchars($row['kelurahan']) ?>">
                                                <?php foreach ($row['tahun'] as $i => $th): ?>
                                                    <option value="<?= $th ?>" <?= $i === 0 ? 'selected' : '' ?>>
                                                        <?= $th ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <a href="#" class="btn btn-sm btn-info btn-edit mr-1"
                                                data-kelurahan="<?= htmlspecialchars($row['kelurahan']) ?>">
                                                <i class="fas fa-pen"></i>
                                            </a>

                                            <a href="#" class="btn btn-sm btn-success btn-lihat"
                                                data-kelurahan="<?= htmlspecialchars($row['kelurahan']) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                        </div>
                                    <?php endif; ?>

                                    <a href="input-data.php?kelurahan=<?= urlencode($row['kelurahan']) ?>"
                                        class="btn btn-sm btn-primary mt-auto mb-2">
                                        + Tahun
                                    </a>

                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        </div>
        <!-- FOOTER  -->
        <?php include __DIR__ . '/../views/layout/footer.php'; ?>
    </div>

    <script>
        document.getElementById("searchInput").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll(".col-custom");

            cards.forEach(card => {
                card.style.display = card.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        });

        document.querySelectorAll('.card').forEach(card => {

            const select = card.querySelector('.tahun-select');
            const editBtn = card.querySelector('.btn-edit');
            const lihatBtn = card.querySelector('.btn-lihat');

            if (select && editBtn && lihatBtn) {
                function updateLink() {
                    const tahun = select.value;
                    const kelurahan = select.dataset.kelurahan;

                    editBtn.href = `input-data.php?kelurahan=${encodeURIComponent(kelurahan)}&tahun=${tahun}`;
                    lihatBtn.href = `monograph.php?kelurahan=${encodeURIComponent(kelurahan)}&tahun=${tahun}`;
                }

                updateLink();
                select.addEventListener('change', updateLink);
            }

        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>

</html>