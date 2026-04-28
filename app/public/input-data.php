<?php
session_start();

// ================= CONFIG & DEPENDENCY =================
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/InputDataController.php';

// ================= AUTH =================
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// ================= PARAM =================
$kelurahan = $_GET['kelurahan'] ?? '';
$tahun = $_GET['tahun'] ?? null;
$mode = $tahun ? 'edit' : 'create';

if (!$tahun) {
    $tahun = date('Y');
}

if (!$kelurahan) {
    die("ERROR: kelurahan kosong");
}

// ================= AMBIL DATA =================
$result = getInputData($conn, $kelurahan, $tahun);

// ⚠️ mapping WAJIB (biar HTML lama tetap jalan)
$wilayah = $result['wilayah'];
$wilayah_id = $result['wilayah_id'];
$data = $result['data'];

$batas_jarak = $data['batas'];
$demografi = $data['demografi'];
$sarana = $data['sarana'];
$program_bantuan = $data['program'];
$pendidikan = $data['pendidikan'];
$aparatur_lembaga = $data['aparatur'];

// ================= ROLE DASHBOARD =================
$role = $_SESSION['role'] ?? '';
$dashboard_url = ($role == 'admin') ? 'dashboard-admin.php' :
    (($role == 'operator') ? 'dashboard-operator.php' : '#');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Form Monografi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: #f4f6f9;
        }

        /* LAYOUT */
        .wrapper {
            display: flex;
        }

        /* SIDEBAR MONODARK */
        .sidebar {
            width: 260px;
            background: #111827;
            min-height: 100vh;
            padding: 20px;
            color: #e5e7eb;
        }

        .sidebar h5 {
            color: #f9fafb;
            margin-bottom: 25px;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9ca3af;
            border-radius: 8px;
            padding: 10px 12px;
            border: none;
            background: none;
            text-align: left;
        }

        .sidebar .nav-link:hover {
            background: #1f2937;
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: #374151;
            color: #fff;
        }

        /* CONTENT */
        .content {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        /* TOPBAR */
        .topbar {
            background: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        /* CARD */
        .card-custom {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .sticky-save {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 12px 20px;
            border-top: 1px solid #eee;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
            z-index: 10;
        }
        form {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .tab-content {
            flex: 1;
        }
    </style>
</head>

<body>

    <?php if (isset($_GET['status'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {

                <?php if ($_GET['status'] == 'success'): ?>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data berhasil disimpan',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        window.location.href = "<?= $dashboard_url ?>";
                    });

                <?php elseif ($_GET['status'] == 'error'): ?>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Data gagal disimpan',
                        confirmButtonColor: '#dc3545'
                    });

                <?php elseif ($_GET['status'] == 'duplicate'): ?>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Sudah Ada!',
                        text: 'Tahun ini sudah pernah diinput untuk kelurahan ini.',
                        confirmButtonColor: '#f59e0b'
                    });

                <?php endif; ?>

            });
        </script>
    <?php endif; ?>

    <div class="wrapper">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <h5>Monografi</h5>

            <div class="nav flex-column" id="formTab">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#identitas">
                    <i class="bi bi-person"></i> Identitas
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#batas">
                    <i class="bi bi-geo-alt"></i> Batas
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#demografi">
                    <i class="bi bi-people"></i> Demografi
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sarana">
                    <i class="bi bi-building"></i> Sarana
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#program">
                    <i class="bi bi-cash"></i> Program
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pendidikan">
                    <i class="bi bi-mortarboard"></i> Pendidikan
                </button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#aparatur">
                    <i class="bi bi-briefcase"></i> Aparatur
                </button>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">

            <!-- TOPBAR -->
            <div class="topbar d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Form Monografi
                                <span class="badge <?= $mode == 'edit' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                    <?= $mode == 'edit' ? 'EDIT' : 'INPUT BARU' ?>
                                </span>
                </h5>
                <div class="d-flex align-items-center gap-4">

                    <!-- USER INFO -->
                    <div class="d-flex align-items-center gap-2 text-muted">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span><?= $_SESSION['username'] ?? 'User' ?></span>
                    </div>

                    <!-- ACTION BUTTON -->
                    <div class="btn-group">
                        <a href="<?= $dashboard_url ?>"
                            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="monograph.php?kelurahan=<?= urlencode($wilayah['kelurahan'] ?? '') ?>" target="_blank"
                            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-printer"></i>
                            <span>Cetak</span>
                        </a>

                        <a href="logout.php" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </div>

                </div>
            </div>

            <!-- PROGRESS -->
            <div class="progress mb-3">
                <div id="progress-bar" class="progress-bar">0%</div>
            </div>

            <form action="simpan-data.php" method="POST">
                <input type="hidden" name="mode" value="<?= $mode ?>">
                <input type="hidden" name="wilayah_id" value="<?= $wilayah_id ?>">
                <input type="hidden" name="kelurahan" value="<?= htmlspecialchars($kelurahan) ?>">
                <label>Tahun</label>
<input type="number" class="form-control mb-2"
    name="tahun"
    value="<?= $tahun ?>" <?= $mode == 'edit' ? 'readonly' : '' ?>
                    required>

                <div class="tab-content">

                    <!-- IDENTITAS -->
                    <div class="tab-pane fade show active p-4" id="identitas">

                        <input type="hidden" name="kelurahan" value="<?= $wilayah['kelurahan'] ?? '' ?>">

                        <label>Kelurahan</label>
                        <input class="form-control mb-2" value="<?= $wilayah['kelurahan'] ?? '' ?>" readonly>

                        <label>Kecamatan</label>
                        <input class="form-control mb-2" value="<?= $wilayah['kecamatan'] ?? '' ?>" readonly>

                        <label>Kota</label>
                        <input class="form-control mb-2" value="<?= $wilayah['kota'] ?? '' ?>" readonly>

                        <label>Provinsi</label>
                        <input class="form-control mb-2" value="<?= $wilayah['provinsi'] ?? '' ?>" readonly>

                        <label>Bulan</label>
                        <input class="form-control mb-2" name="bulan" value="<?= $program_bantuan['bulan'] ?? '' ?>">

                        <div class="text-end">
                            <button type="button" class="btn btn-primary btn-next">Lanjut</button>
                        </div>

                    </div>

                    <!-- BATAS -->
                    <div class="tab-pane fade p-4" id="batas">

                        <div class="card card-custom p-4">

                            <h5 class="mb-3">Batas Wilayah</h5>

                            <div class="row">

                                <!-- KIRI -->
                                <div class="col-md-6">

                                    <label>Tipologi Kelurahan</label>
                                    <input type="text" class="form-control mb-3" name="tipologi_kelurahan"
                                        value="<?= htmlspecialchars($batas_jarak['tipologi_kelurahan'] ?? '') ?>">

                                    <label>Luas Wilayah (km²)</label>
                                    <input type="number" step="0.01" class="form-control mb-3" name="luas_wilayah"
                                        value="<?= isset($batas_jarak['luas_wilayah']) ? floatval($batas_jarak['luas_wilayah']) : '' ?>">

                                    <label>Batas Utara</label>
                                    <input type="text" class="form-control mb-3" name="batas_wilayah_utara"
                                        value="<?= htmlspecialchars($batas_jarak['batas_wilayah_utara'] ?? '') ?>">

                                    <label>Batas Selatan</label>
                                    <input type="text" class="form-control mb-3" name="batas_wilayah_selatan"
                                        value="<?= htmlspecialchars($batas_jarak['batas_wilayah_selatan'] ?? '') ?>">

                                    <label>Batas Barat</label>
                                    <input type="text" class="form-control mb-3" name="batas_wilayah_barat"
                                        value="<?= htmlspecialchars($batas_jarak['batas_wilayah_barat'] ?? '') ?>">

                                    <label>Batas Timur</label>
                                    <input type="text" class="form-control mb-3" name="batas_wilayah_timur"
                                        value="<?= htmlspecialchars($batas_jarak['batas_wilayah_timur'] ?? '') ?>">

                                </div>

                                <!-- KANAN -->
                                <div class="col-md-6">

                                    <label>Jarak ke Pusat Pemerintahan Kecamatan (km)</label>
                                    <input type="number" step="0.01" class="form-control mb-3"
                                        name="jarak_pusat_pemerintahan_kecamatan"
                                        value="<?= htmlspecialchars($batas_jarak['jarak_pusat_pemerintahan_kecamatan'] ?? '') ?>">

                                    <label>Jarak ke Pusat Pemerintahan Kota (km)</label>
                                    <input type="number" step="0.01" class="form-control mb-3"
                                        name="jarak_pusat_pemerintahan_kota"
                                        value="<?= htmlspecialchars($batas_jarak['jarak_pusat_pemerintahan_kota'] ?? '') ?>">

                                    <label>Jarak ke Ibu Kota Kabupaten (km)</label>
                                    <input type="number" step="0.01" class="form-control mb-3"
                                        name="jarak_ibukota_kabupaten"
                                        value="<?= htmlspecialchars($batas_jarak['jarak_ibukota_kabupaten'] ?? '') ?>">

                                    <label>Jarak ke Ibu Kota Provinsi (km)</label>
                                    <input type="number" step="0.01" class="form-control mb-3"
                                        name="jarak_ibukota_provinsi"
                                        value="<?= htmlspecialchars($batas_jarak['jarak_ibukota_provinsi'] ?? '') ?>">

                                </div>

                            </div>

                            <!-- NAV -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary btn-prev">
                                    <i class="bi bi-arrow-left"></i> Sebelumnya
                                </button>

                                <button type="button" class="btn btn-primary btn-next">
                                    Lanjut <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                        </div>

                    </div>

                    <!-- DEMOGRAFI -->
                    <div class="tab-pane fade p-4" id="demografi" role="tabpanel">

                        <div class="card card-custom p-4">

                            <h5 class="mb-3">Demografi</h5>

                            <div class="row">

                                <!-- KIRI -->
                                <div class="col-md-6">

                                    <label>Jumlah Penduduk Laki-laki</label>
                                    <input type="number" class="form-control mb-3" name="jumlah_penduduk_laki_laki"
                                        value="<?= htmlspecialchars($demografi['jumlah_penduduk_laki_laki'] ?? '') ?>">

                                    <label>Jumlah Penduduk Perempuan</label>
                                    <input type="number" class="form-control mb-3" name="jumlah_penduduk_perempuan"
                                        value="<?= htmlspecialchars($demografi['jumlah_penduduk_perempuan'] ?? '') ?>">

                                    <label>Jumlah Penduduk Usia 0–15 Tahun</label>
                                    <input type="number" class="form-control mb-3" name="jumlah_penduduk_usia_0_15"
                                        value="<?= htmlspecialchars($demografi['jumlah_penduduk_usia_0_15'] ?? '') ?>">

                                    <label>Jumlah Penduduk Usia 15–65 Tahun</label>
                                    <input type="number" class="form-control mb-3" name="jumlah_penduduk_usia_15_65"
                                        value="<?= htmlspecialchars($demografi['jumlah_penduduk_usia_15_65'] ?? '') ?>">

                                    <label>Jumlah Penduduk Usia di atas 65 Tahun</label>
                                    <input type="number" class="form-control mb-3" name="jumlah_penduduk_usia_65_keatas"
                                        value="<?= htmlspecialchars($demografi['jumlah_penduduk_usia_65_keatas'] ?? '') ?>">

                                </div>

                                <!-- KANAN -->
                                <div class="col-md-6">

                                    <label>Mayoritas Pekerjaan</label>
                                    <input type="text" class="form-control mb-3" name="mayoritas_pekerjaan"
                                        value="<?= htmlspecialchars($demografi['mayoritas_pekerjaan'] ?? '') ?>">

                                    <label>Jumlah Penduduk Miskin (KK)</label>
                                    <input type="number" class="form-control mb-3" name="jumlah_penduduk_miskin_kk"
                                        value="<?= htmlspecialchars($demografi['jumlah_penduduk_miskin_kk'] ?? '') ?>">

                                    <label>Jumlah Penduduk Miskin (Jiwa)</label>
                                    <input type="number" class="form-control mb-3" name="jumlah_penduduk_miskin_jiwa"
                                        value="<?= htmlspecialchars($demografi['jumlah_penduduk_miskin_jiwa'] ?? '') ?>">

                                    <label>UMR Kabupaten/Kota (Rp)</label>
                                    <input type="text" class="form-control mb-3" name="umr_kabupaten_kota"
                                        value="<?= htmlspecialchars($demografi['umr_kabupaten_kota'] ?? '') ?>">

                                </div>

                            </div>

                            <!-- NAV -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary btn-prev">
                                    <i class="bi bi-arrow-left"></i> Sebelumnya
                                </button>

                                <button type="button" class="btn btn-primary btn-next">
                                    Lanjut <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                        </div>

                    </div>
                    <!-- SARANA -->
                    <div class="tab-pane fade p-4" id="sarana" role="tabpanel">

                        <div class="card card-custom p-4">

                            <h5 class="mb-4">Sarana & Prasarana</h5>

                            <!-- ================= BASIC ================= -->
                            <h6 class="text-muted mb-3">Fasilitas Umum</h6>

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label>Kantor Kelurahan</label>
                                    <select name="kantor_kelurahan" class="form-control">
                                        <?php
                                        $selected = $sarana['kantor_kelurahan'] ?? '';
                                        $result = $conn->query("SHOW COLUMNS FROM sarana LIKE 'kantor_kelurahan'");
                                        if ($row = $result->fetch_assoc()) {
                                            preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
                                            echo '<option value="">--- Pilih ---</option>';
                                            foreach (explode(",", str_replace("'", "", $matches[1])) as $val) {
                                                echo '<option value="' . $val . '"' . ($val == $selected ? ' selected' : '') . '>' . $val . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Puskesmas</label>
                                    <select name="puskesmas" class="form-control">
                                        <?php
                                        $selected = $sarana['puskesmas'] ?? '';
                                        $result = $conn->query("SHOW COLUMNS FROM sarana LIKE 'puskesmas'");
                                        if ($row = $result->fetch_assoc()) {
                                            preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
                                            echo '<option value="">--- Pilih ---</option>';
                                            foreach (explode(",", str_replace("'", "", $matches[1])) as $val) {
                                                echo '<option value="' . $val . '"' . ($val == $selected ? ' selected' : '') . '>' . $val . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Posyandu</label>
                                    <input type="text" class="form-control" name="ukbm_posyandu"
                                        value="<?= htmlspecialchars($sarana['ukbm_posyandu'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Poliklinik</label>
                                    <input type="text" class="form-control" name="poliklinik"
                                        value="<?= htmlspecialchars($sarana['poliklinik'] ?? '') ?>">
                                </div>

                            </div>

                            <hr>

                            <!-- ================= DINAMIS ================= -->
                            <h6 class="text-muted mb-3">Sarana Detail</h6>

                            <?php
                            $sarana_fields = [
                                'masjid' => 'Masjid',
                                'mushola' => 'Mushola',
                                'gereja' => 'Gereja',
                                'pura' => 'Pura',
                                'vihara' => 'Vihara',
                                'klenteng' => 'Klenteng',
                                'olahraga' => 'Sarana Olahraga',
                                'kesenian_budaya' => 'Kesenian/Budaya',
                                'balai_pertemuan' => 'Balai Pertemuan'
                            ];

                            foreach ($sarana_fields as $key => $label):
                                $jumlah = $sarana[$key] ?? 0;
                                ?>

                                <div class="mb-4 p-3 border rounded">

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><?= $label ?></strong>

                                        <input type="number" class="form-control w-auto" style="max-width:100px"
                                            id="jumlah-<?= $key ?>" name="<?= $key ?>"
                                            value="<?= htmlspecialchars($jumlah) ?>" min="0"
                                            oninput="syncFields('<?= $key ?>')">
                                    </div>

                                    <div id="<?= $key ?>-container">
                                        <?php if (isset($sarana_detail[$key])): ?>
                                            <?php foreach ($sarana_detail[$key] as $detail): ?>
                                                <div class="<?= $key ?>-input input-group mb-2">
                                                    <input type="text" name="<?= $key ?>_nama[]" class="form-control"
                                                        value="<?= htmlspecialchars($detail) ?>" placeholder="Nama <?= $label ?>">

                                                    <button type="button" class="btn btn-outline-danger"
                                                        onclick="removeField(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                                        onclick="addField('<?= $key ?>')">
                                        + Tambah <?= $label ?>
                                    </button>

                                </div>

                            <?php endforeach; ?>

                            <!-- SARANA LAIN -->
                            <div class="mb-3">
                                <label>Sarana Lainnya</label>
                                <input type="text" class="form-control" name="sarana_lainnya"
                                    value="<?= htmlspecialchars($sarana['sarana_lainnya'] ?? '') ?>">
                            </div>

                            <!-- NAV -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary btn-prev">
                                    <i class="bi bi-arrow-left"></i> Sebelumnya
                                </button>

                                <button type="button" class="btn btn-primary btn-next">
                                    Lanjut <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                        </div>
                    </div>


                    <!-- PROGRAM BANTUAN -->
                    <div class="tab-pane fade p-4" id="program" role="tabpanel">

                        <div class="card card-custom p-4">

                            <h5 class="mb-4">Program Bantuan</h5>

                            <!-- STATUS -->
                            <div class="mb-4">
                                <label>Apakah SKPD Sudah?</label>
                                <select class="form-select" name="skpd_sudah">
                                    <option value="1" <?= (($program_bantuan['skpd_sudah'] ?? '') == '1') ? 'selected' : '' ?>>Sudah</option>
                                    <option value="0" <?= (($program_bantuan['skpd_sudah'] ?? '') == '0') ? 'selected' : '' ?>>Belum</option>
                                </select>
                            </div>

                            <hr>

                            <!-- PROGRAM -->
                            <h6 class="text-muted mb-3">Program Pemerintah</h6>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label>Program Pusat</label>
                                    <textarea class="form-control" name="program_pusat"
                                        rows="4"><?= htmlspecialchars($program_bantuan['program_pusat'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Program Provinsi</label>
                                    <textarea class="form-control" name="program_provinsi"
                                        rows="4"><?= htmlspecialchars($program_bantuan['program_provinsi'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Program Kabupaten</label>
                                    <textarea class="form-control" name="program_kabupaten"
                                        rows="4"><?= htmlspecialchars($program_bantuan['program_kabupaten'] ?? '') ?></textarea>
                                </div>

                            </div>

                            <hr>

                            <!-- DANA -->
                            <h6 class="text-muted mb-3">Sumber Dana</h6>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label>Dana APBD (Rp)</label>
                                    <input type="number" class="form-control" name="apbd"
                                        value="<?= htmlspecialchars($program_bantuan['apbd'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Bantuan Pusat (Rp)</label>
                                    <input type="number" step="0.01" class="form-control" name="bantuan_pusat"
                                        value="<?= htmlspecialchars($program_bantuan['bantuan_pusat'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Bantuan Provinsi (Rp)</label>
                                    <input type="number" step="0.01" class="form-control" name="bantuan_provinsi"
                                        value="<?= htmlspecialchars($program_bantuan['bantuan_provinsi'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Bantuan Kabupaten/Kota (Rp)</label>
                                    <input type="number" step="0.01" class="form-control" name="bantuan_kab_kota"
                                        value="<?= htmlspecialchars($program_bantuan['bantuan_kab_kota'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Bantuan Luar Negeri (Rp)</label>
                                    <input type="number" step="0.01" class="form-control" name="bantuan_luar_negeri"
                                        value="<?= htmlspecialchars($program_bantuan['bantuan_luar_negeri'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Bantuan Gotong Royong (Rp)</label>
                                    <input type="number" step="0.01" class="form-control" name="bantuan_gotong_royong"
                                        value="<?= htmlspecialchars($program_bantuan['bantuan_gotong_royong'] ?? '') ?>">
                                </div>

                            </div>

                            <!-- LAINNYA -->
                            <div class="mb-3">
                                <label>Sumber Bantuan Lainnya</label>
                                <input type="text" class="form-control" name="bantuan_sumber_lain"
                                    value="<?= htmlspecialchars($program_bantuan['bantuan_sumber_lain'] ?? '') ?>">
                            </div>

                            <!-- NAV -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary btn-prev">
                                    <i class="bi bi-arrow-left"></i> Sebelumnya
                                </button>

                                <button type="button" class="btn btn-primary btn-next">
                                    Lanjut <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- PENDIDIKAN -->
                    <div class="tab-pane fade p-4" id="pendidikan" role="tabpanel">

                        <div class="card card-custom p-4">

                            <h5 class="mb-4">Data Pendidikan</h5>

                            <!-- ================= LULUSAN ================= -->
                            <h6 class="text-muted mb-3">Jumlah Lulusan</h6>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan TK</label>
                                    <input type="number" class="form-control" name="lulusan_tk"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_tk'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan SD</label>
                                    <input type="number" class="form-control" name="lulusan_sd"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_sd'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan SMP</label>
                                    <input type="number" class="form-control" name="lulusan_smp"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_smp'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan SMA</label>
                                    <input type="number" class="form-control" name="lulusan_sma"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_sma'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan Akademi</label>
                                    <input type="number" class="form-control" name="lulusan_akademi"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_akademi'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan Sarjana</label>
                                    <input type="number" class="form-control" name="lulusan_sarjana"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_sarjana'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan Pasca Sarjana</label>
                                    <input type="number" class="form-control" name="lulusan_pasca_sarjana"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_pasca_sarjana'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan Pondok Pesantren</label>
                                    <input type="number" class="form-control" name="lulusan_pondok_pesantren"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_pondok_pesantren'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Pendidikan Keagamaan</label>
                                    <input type="number" class="form-control" name="lulusan_pendidikan_keagamaan"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_pendidikan_keagamaan'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Lulusan SLB</label>
                                    <input type="number" class="form-control" name="lulusan_slb"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_slb'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Kursus Keterampilan</label>
                                    <input type="number" class="form-control" name="lulusan_kursus_keterampilan"
                                        value="<?= htmlspecialchars($pendidikan['lulusan_kursus_keterampilan'] ?? '') ?>">
                                </div>

                            </div>

                            <hr>

                            <!-- ================= PRASARANA ================= -->
                            <h6 class="text-muted mb-3">Prasarana Pendidikan</h6>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label>PAUD</label>
                                    <input type="number" class="form-control" name="prasarana_paud"
                                        value="<?= htmlspecialchars($pendidikan['prasarana_paud'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>TK</label>
                                    <input type="number" class="form-control" name="prasarana_tk"
                                        value="<?= htmlspecialchars($pendidikan['prasarana_tk'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>SD</label>
                                    <input type="number" class="form-control" name="prasarana_sd"
                                        value="<?= htmlspecialchars($pendidikan['prasarana_sd'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>SMP</label>
                                    <input type="number" class="form-control" name="prasarana_smp"
                                        value="<?= htmlspecialchars($pendidikan['prasarana_smp'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>SMA</label>
                                    <input type="number" class="form-control" name="prasarana_sma"
                                        value="<?= htmlspecialchars($pendidikan['prasarana_sma'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Perguruan Tinggi</label>
                                    <input type="number" class="form-control" name="prasarana_pt"
                                        value="<?= htmlspecialchars($pendidikan['prasarana_pt'] ?? '') ?>">
                                </div>

                            </div>

                            <!-- NAV -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary btn-prev">
                                    <i class="bi bi-arrow-left"></i> Sebelumnya
                                </button>

                                <button type="button" class="btn btn-primary btn-next">
                                    Lanjut <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- APARATUR -->
                    <div class="tab-pane fade p-4" id="aparatur" role="tabpanel">

                        <div class="card card-custom p-4">

                            <h5 class="mb-4">Aparatur & Kelembagaan</h5>

                            <!-- ================= PIMPINAN ================= -->
                            <h6 class="text-muted mb-3">Pimpinan Kelurahan</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nama Lurah</label>
                                    <input type="text" class="form-control" name="nama_lurah"
                                        value="<?= htmlspecialchars($aparatur_lembaga['nama_lurah'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Nama Sekretaris</label>
                                    <input type="text" class="form-control" name="nama_sekretaris"
                                        value="<?= htmlspecialchars($aparatur_lembaga['nama_sekretaris'] ?? '') ?>">
                                </div>
                            </div>

                            <hr>

                            <!-- ================= GOLONGAN ================= -->
                            <h6 class="text-muted mb-3">Golongan Pegawai</h6>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label>Golongan I</label>
                                    <input type="number" class="form-control" name="golongan_i"
                                        value="<?= htmlspecialchars($aparatur_lembaga['golongan_i'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Golongan II</label>
                                    <input type="number" class="form-control" name="golongan_ii"
                                        value="<?= htmlspecialchars($aparatur_lembaga['golongan_ii'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Golongan III</label>
                                    <input type="number" class="form-control" name="golongan_iii"
                                        value="<?= htmlspecialchars($aparatur_lembaga['golongan_iii'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Golongan IV</label>
                                    <input type="number" class="form-control" name="golongan_iv"
                                        value="<?= htmlspecialchars($aparatur_lembaga['golongan_iv'] ?? '') ?>">
                                </div>
                            </div>

                            <hr>

                            <!-- ================= LPM ================= -->
                            <h6 class="text-muted mb-3">LPM</h6>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label>Pengurus</label>
                                    <input type="number" class="form-control" name="lpm_pengurus"
                                        value="<?= htmlspecialchars($aparatur_lembaga['lpm_pengurus'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Kegiatan</label>
                                    <input type="number" class="form-control" name="lpm_kegiatan"
                                        value="<?= htmlspecialchars($aparatur_lembaga['lpm_kegiatan'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Buku Administrasi</label>
                                    <input type="number" class="form-control" name="lpm_buku_administrasi"
                                        value="<?= htmlspecialchars($aparatur_lembaga['lpm_buku_administrasi'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Dana</label>
                                    <input type="number" step="0.01" class="form-control" name="lpm_dana"
                                        value="<?= htmlspecialchars($aparatur_lembaga['lpm_dana'] ?? '') ?>">
                                </div>
                            </div>

                            <hr>

                            <!-- ================= PKK ================= -->
                            <h6 class="text-muted mb-3">TP PKK</h6>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label>Pengurus</label>
                                    <input type="number" class="form-control" name="tp_pkk_pengurus"
                                        value="<?= htmlspecialchars($aparatur_lembaga['tp_pkk_pengurus'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Kegiatan</label>
                                    <input type="number" class="form-control" name="tp_pkk_kegiatan"
                                        value="<?= htmlspecialchars($aparatur_lembaga['tp_pkk_kegiatan'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Buku</label>
                                    <input type="number" class="form-control" name="tp_pkk_buku"
                                        value="<?= htmlspecialchars($aparatur_lembaga['tp_pkk_buku'] ?? '') ?>">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Dana</label>
                                    <input type="number" step="0.01" class="form-control" name="tp_pkk_dana"
                                        value="<?= htmlspecialchars($aparatur_lembaga['tp_pkk_dana'] ?? '') ?>">
                                </div>
                            </div>

                            <hr>

                            <!-- ================= WILAYAH ================= -->
                            <h6 class="text-muted mb-3">Wilayah & Sosial</h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Jumlah RT</label>
                                    <input type="number" class="form-control" name="rt"
                                        value="<?= htmlspecialchars($aparatur_lembaga['rt'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Penghasilan RT (Rp)</label>
                                    <input type="number" step="0.01" class="form-control" name="penghasilan_rt"
                                        value="<?= htmlspecialchars($aparatur_lembaga['penghasilan_rt'] ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Karang Taruna</label>
                                    <input type="number" class="form-control" name="karang_taruna_jumlah"
                                        value="<?= htmlspecialchars($aparatur_lembaga['karang_taruna_jumlah'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Pengurus Karang Taruna</label>
                                    <input type="number" class="form-control" name="karang_taruna_pengurus"
                                        value="<?= htmlspecialchars($aparatur_lembaga['karang_taruna_pengurus'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Lembaga Adat</label>
                                    <input type="text" class="form-control" name="lembaga_adat"
                                        value="<?= htmlspecialchars($aparatur_lembaga['lembaga_adat'] ?? '') ?>">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label>Lembaga Lainnya</label>
                                    <input type="text" class="form-control" name="lembaga_lainnya"
                                        value="<?= htmlspecialchars($aparatur_lembaga['lembaga_lainnya'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- NAV -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary btn-prev">
                                    <i class="bi bi-arrow-left"></i> Sebelumnya
                                </button>

                            </div>

                        </div>

                    </div>
                </div>
<div class="sticky-save d-flex justify-content-between align-items-center">

    <span class="text-muted small">
        Pastikan semua data sudah terisi dengan benar
    </span>

    <button type="submit" class="btn btn-success px-4">
        <i class="bi bi-check-circle"></i> Simpan Data
    </button>

</div>
        </div>

    </div>
    </form>

    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const tabs = document.querySelectorAll('.sidebar .nav-link');
        const tabTriggers = Array.from(tabs).map(t => new bootstrap.Tab(t));

        function updateProgress(i) {
            let p = Math.round((i + 1) / tabs.length * 100);
            let bar = document.getElementById('progress-bar');
            bar.style.width = p + '%';
            bar.innerText = p + '%';
        }

        tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                updateProgress(i);
            });
        });

        document.querySelectorAll('.btn-next').forEach((btn, i) => {
            btn.addEventListener('click', () => {
                tabTriggers[i + 1].show();
                tabs[i + 1].click();
            });
        });

        document.querySelectorAll('.btn-prev').forEach((btn, i) => {
            btn.addEventListener('click', () => {
                tabTriggers[i].show();
                tabs[i].click();
            });
        });

        updateProgress(0);

        function addField(prefix) {
            const container = document.getElementById(`${prefix}-container`);
            const div = document.createElement('div');
            div.className = `${prefix}-input input-group mb-2`;
            div.innerHTML = `
        <input type="text" name="${prefix}_nama[]" class="form-control" placeholder="Nama">
        <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
            container.appendChild(div);
            updateJumlah(prefix);
        }

        function removeField(button) {
            const container = button.closest('div');
            const prefix = container.parentElement.id.replace('-container', '');
            container.remove();
            updateJumlah(prefix);
        }

        function syncFields(prefix) {
            const jumlahInput = parseInt(document.getElementById(`jumlah-${prefix}`).value) || 0;
            const container = document.getElementById(`${prefix}-container`);
            const current = container.querySelectorAll(`.${prefix}-input`).length;

            if (jumlahInput > current) {
                for (let i = current; i < jumlahInput; i++) addField(prefix);
            } else {
                for (let i = current; i > jumlahInput; i--) container.lastElementChild.remove();
            }
        }

        function updateJumlah(prefix) {
            const jumlahInput = document.getElementById(`jumlah-${prefix}`);
            const container = document.getElementById(`${prefix}-container`);
            jumlahInput.value = container.querySelectorAll(`.${prefix}-input`).length;
        }

        function validateForm() {
            const tahun = document.querySelector('[name="tahun"]').value;
            const wilayah = document.querySelector('[name="wilayah_id"]').value;

            if (!tahun || !wilayah) {
                alert("Tahun / wilayah belum terisi");
                return false;
            }

            return true;
        }
        
    </script>

</body>

</html>