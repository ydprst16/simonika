<?php
session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../services/DashboardService.php';

if ($_SESSION['role'] !== 'viewer') {
    header("Location: login.php");
    exit();
}

$data = getKelurahanData($conn);
$totalKelurahan = getTotalKelurahan($conn);
$tahunGlobal = getTahunGlobal($conn);

$summary = getSummaryDashboard($conn);

$sudah_update = $summary['sudah'];
$belum_update = $summary['belum'];
$persentase = $summary['persen'];
$tahun_target = $summary['tahun_target'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Viewer</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .card {
            border-radius: 12px;
            height: 170px;
            display: flex;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .small-box {
            padding: 10px;
        }

        /* progress color */
        .progress-bar.low {
            background-color: #dc3545;
        }

        .progress-bar.medium {
            background-color: #ffc107;
        }

        .progress-bar.high {
            background-color: #28a745;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">

    <div class="wrapper">

                <?php include __DIR__ . '/../views/layout/header.php'; ?>
                <?php include __DIR__ . '/../views/layout/sidebar.php'; ?>

        <div class="content-wrapper">

            <section class="content-header">
                <div class="container-fluid">
                    <h4>Data Monografi Kelurahan</h4>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">

                    <!-- ================= SUMMARY ================= -->
                    <div class="row mb-3">

                        <div class="col-md-3">
                            <div class="small-box bg-primary text-center">
                                <div class="inner">
                                    <h4 class="count" data-target="<?= $totalKelurahan ?>">0</h4>
                                    <p>Total Kelurahan</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-success text-center">
                                <div class="inner">
                                    <h4 class="count" data-target="<?= $sudah_update ?>">0</h4>
                                    <p>Sudah Update</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-danger text-center">
                                <div class="inner">
                                    <h4 class="count" data-target="<?= $belum_update ?>">0</h4>
                                    <p>Belum Update</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-info text-center">
                                <div class="inner">
                                    <h4 class="progress-text">0%</h4>
                                    <p>Progress</p>

                                    <div class="progress mt-2" style="height:8px;">
                                        <div id="progressBar" class="progress-bar" style="width:0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ================= FILTER ================= -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <select id="globalTahun" class="form-control">
                                <option value="">Semua Tahun</option>
                                                    <?php foreach ($tahunGlobal as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                                    <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-10">
                            <input type="text" id="searchBox" class="form-control" placeholder="Cari kelurahan...">
                        </div>
                    </div>

                    <!-- ================= DATA ================= -->
                    <div id="gridContainer" class="grid-container">

                                                <?php foreach ($data as $row): ?>
                            <div class="card-wrapper">

                                <div class="card shadow-sm">
                                    <div class="card-body">

                                        <h6><?= htmlspecialchars($row['kelurahan']) ?></h6>

                                        <?php $tahunList = $row['tahun_list']; ?>

                                        <select class="form-control form-control-sm tahun-select"
                                            data-kelurahan="<?= htmlspecialchars($row['kelurahan']) ?>" <?= empty($tahunList) ? 'disabled' : '' ?>>

                                                                                        <?php if ($tahunList): ?>
                                                                                            <?php foreach ($tahunList as $t): ?>
                                                    <option value="<?= $t ?>"><?= $t ?></option>
                                                                                            <?php endforeach; ?>
                                                                                        <?php else: ?>
                                                <option>Tidak ada data</option>
                                                                                        <?php endif; ?>

                                        </select>

                                        <a href="#"
                                            class="btn btn-sm btn-outline-primary btn-lihat <?= empty($tahunList) ? 'disabled' : '' ?>">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>

                                    </div>
                                </div>

                            </div>
<?php endforeach; ?>
                    </div>

                </div>
            </section>

        </div>

                <?php include __DIR__ . '/../views/layout/footer.php'; ?>

    </div>

    <script>
        // COUNT UP
        document.querySelectorAll('.count').forEach(el => {
            let target = +el.dataset.target;
            let count = 0;
            let inc = target / 50;
            let update = () => {
                if (count < target) {
                    count += inc;
                    el.innerText = Math.ceil(count);
                    setTimeout(update, 20);
                } else {
                    el.innerText = target;
                }
            };
            update();
        });

        // PROGRESS
        let progress = <?= $persentase ?>;
        let bar = document.getElementById('progressBar');
        let text = document.querySelector('.progress-text');

        let i = 0;
        function animate() {
            if (i < progress) {
                i++;
                text.innerText = i + '%';
                bar.style.width = i + '%';
                setTimeout(animate, 15);
            } else {
                text.innerText = progress + '%';
            }
        }
        animate();

        // warna
        if (progress < 50) bar.classList.add('low');
        else if (progress < 80) bar.classList.add('medium');
        else bar.classList.add('high');

        // SEARCH
        document.getElementById('searchBox').addEventListener('keyup', function () {
            let v = this.value.toLowerCase();
            document.querySelectorAll('.card-wrapper').forEach(c => {
                c.style.display = c.innerText.toLowerCase().includes(v) ? '' : 'none';
            });
        });

        // LINK
        function updateLinks() {
            document.querySelectorAll('.card').forEach(card => {
                let s = card.querySelector('.tahun-select');
                let b = card.querySelector('.btn-lihat');
                if (s && b && !s.disabled) {
                    b.href = `monograph.php?kelurahan=${encodeURIComponent(s.dataset.kelurahan)}&tahun=${s.value}`;
                }
            });
        }
        updateLinks();

        // FILTER
        document.getElementById('globalTahun').addEventListener('change', function () {
            let val = this.value;
            document.querySelectorAll('.card-wrapper').forEach(w => {
                let s = w.querySelector('.tahun-select');
                if (!s || s.disabled) return;
                let found = [...s.options].some(o => o.value === val);
                w.style.display = (!val || found) ? '' : 'none';
                if (val && found) s.value = val;
            });
            updateLinks();
        });
    </script>

</body>

</html>