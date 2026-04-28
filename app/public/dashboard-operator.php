<?php

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SESSION['role'] !== 'operator') {
    header("Location: login.php");
    exit();
}

$kelurahan = $_SESSION['kelurahan'];

/* ================= DATA ================= */
$stmt = $conn->prepare("SELECT * FROM wilayah WHERE kelurahan = ?");
$stmt->bind_param("s", $kelurahan);
$stmt->execute();
$result = $stmt->get_result();
$data_wilayah = $result->fetch_assoc();
$wilayah_id = $data_wilayah['id'] ?? null;

/* ================= TAHUN ================= */
$tahun_list = [];

if ($wilayah_id) {
    $stmt2 = $conn->prepare("
        SELECT tahun 
        FROM monografi_tahun 
        WHERE wilayah_id = ?
        ORDER BY tahun DESC
    ");
    $stmt2->bind_param("i", $wilayah_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    while ($row2 = $res2->fetch_assoc()) {
        $tahun_list[] = $row2['tahun'];
    }
}

$total_tahun = count($tahun_list);
$tahun_terbaru = $tahun_list[0] ?? 0;

/* ================= NOTIF ================= */
$tahun_sekarang = date('Y');
$belum_update = !in_array($tahun_sekarang, $tahun_list);

/* ================= CHART ================= */
$chart_labels = [];
$chart_laki = [];
$chart_perempuan = [];
$chart_total = [];
$chart_miskin = [];

if ($wilayah_id) {
    $stmt3 = $conn->prepare("
        SELECT mt.tahun,
               IFNULL(d.jumlah_penduduk_laki_laki,0) AS laki,
               IFNULL(d.jumlah_penduduk_perempuan,0) AS perempuan,
               IFNULL(d.jumlah_penduduk_miskin_jiwa,0) AS miskin
        FROM monografi_tahun mt
        LEFT JOIN demografi d ON d.monografi_id = mt.id
        WHERE mt.wilayah_id = ?
        ORDER BY mt.tahun ASC
    ");
    $stmt3->bind_param("i", $wilayah_id);
    $stmt3->execute();
    $res3 = $stmt3->get_result();

    while ($row3 = $res3->fetch_assoc()) {
        $chart_labels[] = $row3['tahun'];

        $laki = (int) $row3['laki'];
        $perempuan = (int) $row3['perempuan'];

        $chart_laki[] = $laki;
        $chart_perempuan[] = $perempuan;
        $chart_total[] = $laki + $perempuan;
        $chart_miskin[] = (int) $row3['miskin'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Operator</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f4f6f9;
        }

        .container-boxed {
            max-width: 1200px;
            margin: auto;
        }

        .card {
            border: 0;
            border-radius: 10px;
        }

        .card:hover {
            transform: translateY(-2px);
            transition: 0.2s;
        }
    </style>

</head>

<body class="hold-transition layout-top-nav layout-navbar-fixed">

    <div class="wrapper">

        <!-- NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light shadow-sm">
            <span class="navbar-brand font-weight-bold">
                Dashboard Operator
            </span>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- CONTENT -->
        <div class="content-wrapper">
            <section class="content pt-4 pb-4">

                <div class="container-boxed">

                    <!-- NOTIF -->
                    <?php if ($belum_update): ?>
                        <div class="alert alert-warning d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <b>⚠️ Tahun <?= $tahun_sekarang ?> belum diinput</b>
                            </div>
                            <a href="input-data.php?kelurahan=<?= urlencode($kelurahan) ?>"
                                class="btn btn-dark btn-sm px-3">
                                Input Sekarang
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- INFO -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <b>Kelurahan:</b> <?= htmlspecialchars($kelurahan) ?>
                        </div>
                    </div>

                    <!-- STATS -->
                    <div class="row">

                        <div class="col-md-6">
                            <div class="small-box bg-info shadow-sm">
                                <div class="inner">
                                    <h3 class="counter" data-target="<?= $total_tahun ?>">0</h3>
                                    <p>Total Tahun</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small-box bg-success shadow-sm">
                                <div class="inner">
                                    <h3 class="counter" data-target="<?= $tahun_terbaru ?>">0</h3>
                                    <p>Tahun Terbaru</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- CHART -->
                    <div class="row">

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header"><b>Penduduk</b></div>
                                <div class="card-body">
                                    <canvas id="chartPenduduk"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header"><b>Kemiskinan</b></div>
                                <div class="card-body">
                                    <canvas id="chartMiskin"></canvas>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- LIST -->
                    <div class="card shadow-sm">
                        <div class="card-header d-flex align-items-center">
                            <b>Data Monografi</b>
                            <a href="input-data.php?kelurahan=<?= urlencode($kelurahan) ?>"
                                class="btn btn-primary btn-sm px-3 ml-auto">
                                + Tahun
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                <?php foreach ($tahun_list as $index => $th): ?>
                                    <div class="col-md-4 mb-3">

                                        <div class="card shadow-sm p-3">

                                            <div class="d-flex justify-content-between">
                                                <b><?= $th ?></b>
                                                <?php if ($index === 0): ?>
                                                    <span class="badge badge-success">Terbaru</span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mt-2 d-flex">
                                                <a href="input-data.php?kelurahan=<?= urlencode($kelurahan) ?>&tahun=<?= $th ?>"
                                                    class="btn btn-sm btn-info mr-1 w-100">
                                                    Edit
                                                </a>

                                                <a href="monograph.php?kelurahan=<?= urlencode($kelurahan) ?>&tahun=<?= $th ?>"
                                                    class="btn btn-sm btn-success w-100">
                                                    Lihat
                                                </a>
                                            </div>

                                        </div>

                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <?php include __DIR__ . '/../views/layout/footer.php'; ?>

    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // COUNTER ANIMATION
        document.querySelectorAll('.counter').forEach(counter => {
            const update = () => {
                const target = +counter.getAttribute('data-target');
                const current = +counter.innerText;
                const increment = target / 40;

                if (current < target) {
                    counter.innerText = Math.ceil(current + increment);
                    setTimeout(update, 30);
                } else {
                    counter.innerText = target;
                }
            };
            update();
        });

        // CHART
        new Chart(document.getElementById('chartPenduduk'), {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [
                    { label: 'Laki-laki', data: <?= json_encode($chart_laki) ?> },
                    { label: 'Perempuan', data: <?= json_encode($chart_perempuan) ?> },
                    { label: 'Total', data: <?= json_encode($chart_total) ?> }
                ]
            }
        });

        new Chart(document.getElementById('chartMiskin'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{ label: 'Miskin', data: <?= json_encode($chart_miskin) ?> }]
            }
        });
    </script>

</body>

</html>