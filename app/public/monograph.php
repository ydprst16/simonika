<?php
session_start();

// ================= CONFIG =================
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/MonographController.php';
require_once __DIR__ . '/../config/config.php';

// ================= PARAM =================
$kelurahan = $_GET['kelurahan'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

if (!$kelurahan) {
    die("Kelurahan tidak dipilih");
}

// ================= DATA =================
$result = getMonographData($conn, $kelurahan, $tahun);

// mapping (WAJIB biar HTML lama tetap jalan)
$wilayah = $result['wilayah'];
$data = $result['data'];

$batas_jarak = $data['batas'];
$demografi = $data['demografi'];
$sarana = $data['sarana'];
$pendidikan = $data['pendidikan'];
$program_bantuan = $data['program'];
$aparatur_lembaga = $data['aparatur'];


// ================= HELPER VIEW =================
function tf($label, $value)
{
    $value = ($value === null || $value === '') ? '-' : $value;
    ?>
    <div class="flex justify-between border-b py-1 px-1 hover:bg-gray-50 rounded">
        <span><?= $label ?></span>
        <span><?= htmlspecialchars($value) ?></span>
    </div>
    <?php
}

function val($arr, $key, $suffix = '')
{
    if (!is_array($arr))
        return '-';

    return isset($arr[$key]) && $arr[$key] !== ''
        ? $arr[$key] . $suffix
        : '-';
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Monografi</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            overflow: visible !important;
        }

        h3 {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 2px;
            margin-top: 10px;
        }

        @media print {
            @page {
                size: A3 landscape;
                margin: 0.5cm;
            }

            /* matikan semua efek modern */
            * {
                background: white !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
            }

            /* overflow wajib dimatikan */
            .overflow-y-auto {
                overflow: visible !important;
            }

            /* hilangkan tombol */
            button {
                display: none !important;
            }

        }
    </style>
</head>

<!-- <body class="bg-gray-100 text-[11px] leading-tight"> -->

<body class="text-[11.5px] leading-tight bg-[linear-gradient(135deg,#f1f5f9,#e0f2fe,#f8fafc)]">

    <!-- HEADER -->
    <div
        class="flex justify-between items-center px-6 py-4 bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200">

        <div class="flex items-center gap-3">
            <img src="<?= BASE_URL ?>assets/images/logo.png" class="w-12 h-14">

            <div>
                <div class="font-bold text-base tracking-wide">
                    LAPORAN MONOGRAFI
                </div>
                <div class="text-sm text-gray-600">
                    Kelurahan
                    <?= strtoupper($wilayah['kelurahan']) ?>
                </div>
                <div class="text-[11px] text-gray-400">
                    <?= $program_bantuan['bulan'] ?? '-' ?>
                    <?= $program_bantuan['tahun'] ?? '-' ?>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="window.print()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow hover:bg-blue-700 transition">
                Cetak PDF
            </button>

            <form action="buku_monograph.php" method="get" target="_blank">
                <input type="hidden" name="kelurahan" value="<?= htmlspecialchars($kelurahan) ?>">
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium shadow hover:bg-green-700 transition">
                    Unduh Word
                </button>
            </form>
        </div>

    </div>

    <!-- GRID -->
    <div class="grid grid-cols-4 gap-3 px-3 pt-3">

        <!-- ================= KOLOM 1 ================= -->
        <div
            class="bg-white/90 backdrop-blur-sm p-3 rounded-xl shadow-md border border-gray-200 h-full flex flex-col hover:shadow-lg transition">
            <div class="overflow-y-auto pr-2 space-y-2">

                <h3 class="font-bold uppercase mb-1">Identitas</h3>
                <?php
                tf('Kelurahan', $wilayah['kelurahan']);
                tf('Tahun Pembentukan', $wilayah['tahun_pembentukan']);
                tf('Kecamatan', $wilayah['kecamatan']);
                tf('Kota', $wilayah['kota']);
                tf('Provinsi', $wilayah['provinsi']);
                tf('Kode Pos', $wilayah['kode_pos']);
                tf('Kode Kemendagri', $wilayah['kode_kemendagri']);
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Data Umum</h3>
                <?php
                tf('Tipologi', val($batas_jarak, 'tipologi_kelurahan'));
                tf('Luas', isset($batas_jarak['luas_wilayah'])
                    ? floatval($batas_jarak['luas_wilayah']) . ' km²'
                    : '-');
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Batas</h3>
                <?php
                tf('Utara', val($batas_jarak, 'batas_wilayah_utara'));
                tf('Selatan', val($batas_jarak, 'batas_wilayah_selatan'));
                tf('Barat', val($batas_jarak, 'batas_wilayah_barat'));
                tf('Timur', val($batas_jarak, 'batas_wilayah_timur'));
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Jarak dari Pusat Pemerintahan</h3>
                <?php
                tf('Kecamatan', val($batas_jarak, 'jarak_pusat_pemerintahan_kecamatan', ' km'));
                tf('Kota', val($batas_jarak, 'jarak_pusat_pemerintahan_kota', ' km'));
                tf('Provinsi', val($batas_jarak, 'jarak_ibukota_provinsi', ' km'));
                ?>

                <h3 class="font-bold uppercase mb-1">Jumlah Penduduk</h3>
                <?php
                tf('Laki-laki', val($demografi, 'jumlah_penduduk_laki_laki', ' org'));
                tf('Perempuan', val($demografi, 'jumlah_penduduk_perempuan', ' org'));
                tf('0-15', val($demografi, 'jumlah_penduduk_usia_0_15', ' org'));
                tf('15-65', val($demografi, 'jumlah_penduduk_usia_15_65', ' org'));
                tf('>65', val($demografi, 'jumlah_penduduk_usia_65_keatas', ' org'));
                tf('Mayoritas Pekerjaan', val($demografi, 'mayoritas_pekerjaan'));
                ?>
            </div>
        </div>

        <!-- ================= KOLOM 2 ================= -->
        <div
            class="bg-white/90 backdrop-blur-sm p-3 rounded-xl shadow-md border border-gray-200 h-full flex flex-col hover:shadow-lg transition">
            <div class="overflow-y-auto pr-2 space-y-2">
                <h3 class="mb-1 font-bold uppercase">Kemiskinan</h3>
                <?php
                $kk = val($demografi, 'jumlah_penduduk_miskin_kk', ' KK');
                $jiwa = val($demografi, 'jumlah_penduduk_miskin_jiwa', ' Jiwa');
                tf('Penduduk Miskin', ($kk === '-' && $jiwa === '-') ? '-' : "$kk / $jiwa");

                tf('UMR', val($demografi, 'umr_kabupaten_kota'));
                ?>

                <h3 class="mt-2 font-bold uppercase mb-1">Sarana Prasarana</h3>
                <?php
                tf('Kantor Kelurahan', val($sarana, 'kantor_kelurahan'));
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Prasarana Kesehatan</h3>
                <?php
                tf('Puskesmas', val($sarana, 'puskesmas'));
                tf('Posyandu', val($sarana, 'ukbm_posyandu'));
                tf('Poliklinik', val($sarana, 'poliklinik'));
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Prasarana Ibadah</h3>
                <?php
                tf('Masjid', val($sarana, 'masjid'));
                tf('Mushola', val($sarana, 'mushola'));
                tf('Gereja', val($sarana, 'gereja'));
                tf('Pura', val($sarana, 'pura'));
                tf('Vihara', val($sarana, 'vihara'));
                tf('Klenteng', val($sarana, 'klenteng'));
                ?>

                <h3 class="font-bold uppercase mb-1">Prasarana Pendidikan</h3>
                <?php
                tf('PAUD', val($pendidikan, 'prasarana_paud'));
                tf('TK', val($pendidikan, 'prasarana_tk'));
                tf('SD', val($pendidikan, 'prasarana_sd'));
                tf('SMP', val($pendidikan, 'prasarana_smp'));
                tf('SMA', val($pendidikan, 'prasarana_sma'));
                tf('Perguruan Tinggi', val($pendidikan, 'prasarana_pt'));
                ?>

                <h3 class="font-bold uppercase mb-1">Prasarana Umum</h3>
                <?php
                tf('Olahraga', val($sarana, 'olahraga'));
                tf('Balai', val($sarana, 'balai_pertemuan'));
                tf('Budaya', val($sarana, 'kesenian_budaya'));
                //tf('Lainnya', $sarana['sarana_lainnya']);
                ?>
            </div>
        </div>

        <!-- ================= KOLOM 3 ================= -->
        <div
            class="bg-white/90 backdrop-blur-sm p-3 rounded-xl shadow-md border border-gray-200 h-full flex flex-col hover:shadow-lg transition">
            <div class="overflow-y-auto pr-2 space-y-2">
                <h3 class="font-bold uppercase mb-1">Lulusan Pendidikan Umum</h3>
                <?php
                tf('TK', val($pendidikan, 'lulusan_tk', ' org'));
                tf('SD', val($pendidikan, 'lulusan_sd', ' org'));
                tf('SMP', val($pendidikan, 'lulusan_smp', ' org'));
                tf('SMA', val($pendidikan, 'lulusan_sma', ' org'));
                tf('Akademi', val($pendidikan, 'lulusan_akademi', ' org'));
                tf('Sarjana', val($pendidikan, 'lulusan_sarjana', ' org'));
                tf('Pasca Sarjana', val($pendidikan, 'lulusan_pascasarjana', ' org'));
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Lulusan Pendidikan Khusus</h3>
                <?php
                tf('Pondok Pesantren', ($pendidikan['lulusan_pondok_pesantren'] ?? '') . ' orang');
                tf('Lulusan Pendidikan Keagamaan', ($pendidikan['lulusan_pendidikan_keagamaan'] ?? '') . ' orang');
                tf('Lulusan SLB', ($pendidikan['lulusan_slb'] ?? '') . ' orang');
                tf('Lulusan Kursus Keterampilan', ($pendidikan['lulusan_kursus_keterampilan'] ?? '') . ' orang');
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Aparatur</h3>
                <?php
                tf('Lurah', val($aparatur_lembaga, 'nama_lurah'));
                tf('Sekretaris', val($aparatur_lembaga, 'nama_sekretaris'));
                tf('Gol I', val($aparatur_lembaga, 'golongan_i', ' org'));
                tf('Gol II', val($aparatur_lembaga, 'golongan_ii', ' org'));
                tf('Gol III', val($aparatur_lembaga, 'golongan_iii', ' org'));
                tf('Gol IV', val($aparatur_lembaga, 'golongan_iv', ' org'));
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Program</h3>
                <?php
                tf('Pusat', val($program_bantuan, 'program_pusat'));
                tf('Provinsi', val($program_bantuan, 'program_provinsi'));
                tf('Kab/Kota', val($program_bantuan, 'program_kabupaten'));
                ?>
            </div>
        </div>
        <!-- ================= KOLOM 4 ================= -->
        <div
            class="bg-white/90 backdrop-blur-sm p-3 rounded-xl shadow-md border border-gray-200 h-full flex flex-col hover:shadow-lg transition">
            <div class="overflow-y-auto pr-2 space-y-2">
                <h3 class="font-bold uppercase mb-1">Keuangan</h3>
                <?php
                $skpd = isset($program_bantuan['skpd_sudah']) ? ($program_bantuan['skpd_sudah'] ? 'Sudah' : 'Belum') : '-';

                tf('APBD', number_format($program_bantuan['apbd'] ?? 0, 0, ',', '.'));
                tf('SKPD', $skpd);
                tf('Pusat', number_format($program_bantuan['bantuan_pusat'] ?? 0, 0, ',', '.'));
                tf('Provinsi', number_format($program_bantuan['bantuan_provinsi'] ?? 0, 0, ',', '.'));
                tf('Kab/Kota', number_format($program_bantuan['bantuan_kab_kota'] ?? 0, 0, ',', '.'));
                tf('Luar Negeri', number_format($program_bantuan['bantuan_luar_negeri'] ?? 0, 0, ',', '.'));
                tf('Gotong Royong', number_format($program_bantuan['bantuan_gotong_royong'] ?? 0, 0, ',', '.'));
                //tf('Lainnya', $program_bantuan['bantuan_sumber_lain']);
                ?>

                <h3 class="mt-2 mb-1 font-bold uppercase">Kelembagaan</h3>
                <?php
                tf('LPM Pengurus', val($aparatur_lembaga, 'lpm_pengurus'));
                tf('LPM Kegiatan', val($aparatur_lembaga, 'lpm_kegiatan'));
                tf('LPM Buku', val($aparatur_lembaga, 'lpm_buku_administrasi'));
                tf('LPM Dana', val($aparatur_lembaga, 'lpm_dana'));
                tf('PKK Pengurus', val($aparatur_lembaga, 'tp_pkk_pengurus'));
                tf('PKK Kegiatan', val($aparatur_lembaga, 'tp_pkk_kegiatan'));
                tf('PKK Buku', val($aparatur_lembaga, 'tp_pkk_buku'));
                tf('PKK Dana', val($aparatur_lembaga, 'tp_pkk_dana'));
                tf('RT', val($aparatur_lembaga, 'rt', ' RT'));
                tf('Penghasilan RT', val($aparatur_lembaga, 'penghasilan_rt'));
                tf('Karang Taruna', val($aparatur_lembaga, 'karang_taruna_jumlah'));
                tf('Pengurus', val($aparatur_lembaga, 'karang_taruna_pengurus'));
                tf('Adat', val($aparatur_lembaga, 'lembaga_adat'));
                tf('Lainnya', val($aparatur_lembaga, 'lembaga_lainnya'));
                ?>
            </div>
        </div>
</body>

</html>