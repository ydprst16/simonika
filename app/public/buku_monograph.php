<?php
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
set_time_limit(300);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    die("Unauthorized");
}

require_once __DIR__ . '/../services/MonografiService.php';
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

function cleanText($text)
{
    $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text); // hapus karakter kontrol
    return strip_tags($text);
}

function labeledText($section, $label, $value)
{
    $labelSafe = cleanText($label);
    $valueSafe = cleanText($value ?? '');
    $section->addText("{$labelSafe}: {$valueSafe}");
}

$kelurahan = isset($_GET['kelurahan']) ? trim($_GET['kelurahan']) : '';

if ($kelurahan === '') {
    die("Kelurahan tidak ditemukan.");
}

// Ambil data dari database
$stmt = $conn->prepare("SELECT * FROM wilayah WHERE kelurahan = ?");
$stmt->bind_param("s", $kelurahan);
$stmt->execute();
$wilayah = $stmt->get_result()->fetch_assoc();
if (!$wilayah)
    die("Data kelurahan tidak ditemukan.");

$wilayah_id = $wilayah['id'];

$tahun = $_GET['tahun'] ?? date('Y');
$monografi_id = getMonografiId($conn, $wilayah_id, $tahun);

$demografi = getData($conn, 'demografi', $monografi_id);
$batas_jarak = getData($conn, 'wilayah_batas_jarak', $monografi_id);
$sarana = getData($conn, 'sarana', $monografi_id);
$pendidikan = getData($conn, 'pendidikan', $monografi_id);
$program_bantuan = getData($conn, 'program_bantuan', $monografi_id);
$aparatur_lembaga = getData($conn, 'aparatur_lembaga', $monografi_id);

// Mulai membuat dokumen Word
$phpWord = new PhpWord();
$section = $phpWord->addSection();

// Judul
$section->addText("BUKU MONOGRAFI", ['bold' => true, 'size' => 16], ['alignment' => 'center']);
$bulan = $program_bantuan['bulan'] ?? '-';
$tahun = $program_bantuan['tahun'] ?? '-';

$section->addText(
    "KELURAHAN " . strtoupper(cleanText($kelurahan)),
    ['bold' => true, 'size' => 14],
    ['alignment' => 'center']
);
$section->addText(
    "KEADAAN BULAN " . strtoupper($bulan) . " TAHUN " . strtoupper($tahun),
    ['bold' => true, 'size' => 14],
    ['alignment' => 'center']
);

$section->addTextBreak(1);

$section->addText("I. Identitas Wilayah", ['bold' => true, 'size' => 12]);
labeledText($section, "Kelurahan", $wilayah['kelurahan'] ?? '');
labeledText($section, "Tahun Pembentukan", $wilayah['tahun_pembentukan'] ?? '');
labeledText($section, "Kecamatan", $wilayah['kecamatan'] ?? '');
labeledText($section, "Kota", $wilayah['kota'] ?? '');
labeledText($section, "Provinsi", $wilayah['provinsi'] ?? '');
labeledText($section, "Kode Pos", $wilayah['kode_pos'] ?? '');
labeledText($section, "Kode Kemendagri", $wilayah['kode_kemendagri'] ?? '');
$section->addTextBreak(1);

$section->addText("II. Data Umum", ['bold' => true, 'size' => 12]);
labeledText($section, "Tipologi Kelurahan", $batas_jarak['tipologi_kelurahan'] ?? '');
labeledText($section, "Luas Wilayah", ($batas_jarak['luas_wilayah'] ?? '') . ' km²');
$section->addTextBreak(1);
$section->addText("Batas Wilayah", ['bold' => true]);
labeledText($section, "Batas Utara", $batas_jarak['batas_wilayah_utara'] ?? '');
labeledText($section, "Batas Selatan", $batas_jarak['batas_wilayah_selatan'] ?? '');
labeledText($section, "Batas Barat", $batas_jarak['batas_wilayah_barat'] ?? '');
labeledText($section, "Batas Timur", $batas_jarak['batas_wilayah_timur'] ?? '');
$section->addTextBreak(1);
$section->addText("Jarak dari Pusat Pemerintahan", ['bold' => true]);
labeledText($section, "Jarak dari Pusat Pemerintahan Kecamatan", ($batas_jarak['jarak_pusat_pemerintahan_kecamatan'] ?? '') . ' km');
labeledText($section, "Jarak dari Pusat Pemerintahan Kota", ($batas_jarak['jarak_pusat_pemerintahan_kota'] ?? '') . ' km');
labeledText($section, "Jarak dari Pusat Pemerintahan Provinsi", ($batas_jarak['jarak_ibukota_provinsi'] ?? '') . ' km');
$section->addTextBreak(1);

$section->addText("Jumlah Penduduk", ['bold' => true]);
labeledText($section, "Jumlah Penduduk Laki-laki", $demografi['jumlah_penduduk_laki_laki'] ?? '');
labeledText($section, "Jumlah Penduduk Perempuan", $demografi['jumlah_penduduk_perempuan'] ?? '');
labeledText($section, "Jumlah Usia 0-15 Tahun", $demografi['jumlah_penduduk_usia_0_15'] ?? '');
labeledText($section, "Jumlah Usia 15-65 Tahun", $demografi['jumlah_penduduk_usia_15_65'] ?? '');
labeledText($section, "Jumlah Usia 65+ Tahun", $demografi['jumlah_penduduk_usia_65_keatas'] ?? '');
$section->addTextBreak(1);
labeledText($section, "Mayoritas Pekerjaan", $demografi['mayoritas_pekerjaan'] ?? '');
$section->addTextBreak(1);
$section->addText("Jumlah Penduduk Miskin", ['bold' => true]);
labeledText($section, "Penduduk Miskin (KK)", $demografi['jumlah_penduduk_miskin_kk'] ?? '');
labeledText($section, "Penduduk Miskin (Jiwa)", $demografi['jumlah_penduduk_miskin_jiwa'] ?? '');
$section->addTextBreak(1);
labeledText($section, "UMR Kab/Kota", $demografi['umr_kabupaten_kota'] ?? '');
$section->addTextBreak(1);

$section->addText("Sarana Prasarana", ['bold' => true]);
labeledText($section, "Kantor Kelurahan", ($sarana['kantor_kelurahan'] ?? 0) > 0 ? $sarana['kantor_kelurahan'] . ' unit' : '-');
$section->addTextBreak(1);

$section->addText("Prasarana Kesehatan", ['bold' => true]);
labeledText($section, "Puskesmas", ($sarana['puskesmas'] ?? 0) > 0 ? $sarana['puskesmas'] . ' unit' : '-');
labeledText($section, "UKBM/Posyandu", ($sarana['ukbm_posyandu'] ?? 0) > 0 ? $sarana['ukbm_posyandu'] . ' unit' : '-');
labeledText($section, "Poliklinik", ($sarana['poliklinik'] ?? 0) > 0 ? $sarana['poliklinik'] . ' unit' : '-');
$section->addTextBreak(1);

$section->addText("Prasarana Pendidikan", ['bold' => true]);
labeledText($section, "Prasarana PAUD", ($pendidikan['prasarana_paud'] ?? 0) > 0 ? $pendidikan['prasarana_paud'] . ' unit' : '-');
labeledText($section, "Prasarana TK", ($pendidikan['prasarana_tk'] ?? 0) > 0 ? $pendidikan['prasarana_tk'] . ' unit' : '-');
labeledText($section, "Prasarana SD", ($pendidikan['prasarana_sd'] ?? 0) > 0 ? $pendidikan['prasarana_sd'] . ' unit' : '-');
labeledText($section, "Prasarana SMP", ($pendidikan['prasarana_smp'] ?? 0) > 0 ? $pendidikan['prasarana_smp'] . ' unit' : '-');
labeledText($section, "Prasarana SMA", ($pendidikan['prasarana_sma'] ?? 0) > 0 ? $pendidikan['prasarana_sma'] . ' unit' : '-');
labeledText($section, "Prasarana Perguruan Tinggi", ($pendidikan['prasarana_pt'] ?? 0) > 0 ? $pendidikan['prasarana_pt'] . ' unit' : '-');
$section->addTextBreak(1);

$section->addText("Prasarana Ibadah", ['bold' => true]);
labeledText($section, "Masjid", ($sarana['masjid'] ?? 0) > 0 ? $sarana['masjid'] . ' unit' : '-');
labeledText($section, "Mushola", ($sarana['mushola'] ?? 0) > 0 ? $sarana['mushola'] . ' unit' : '-');
labeledText($section, "Gereja", ($sarana['gereja'] ?? 0) > 0 ? $sarana['gereja'] . ' unit' : '-');
labeledText($section, "Pura", ($sarana['pura'] ?? 0) > 0 ? $sarana['pura'] . ' unit' : '-');
labeledText($section, "Vihara", ($sarana['vihara'] ?? 0) > 0 ? $sarana['vihara'] . ' unit' : '-');
labeledText($section, "Klenteng", ($sarana['klenteng'] ?? 0) > 0 ? $sarana['klenteng'] . ' unit' : '-');
$section->addTextBreak(1);

$section->addText("Prasarana Umum", ['bold' => true]);
labeledText($section, "Olahraga", ($sarana['olahraga'] ?? 0) > 0 ? $sarana['olahraga'] . ' unit' : '-');
labeledText($section, "Kesenian/Budaya", ($sarana['kesenian_budaya'] ?? 0) > 0 ? $sarana['kesenian_budaya'] . ' unit' : '-');
labeledText($section, "Balai Pertemuan", ($sarana['balai_pertemuan'] ?? 0) > 0 ? $sarana['balai_pertemuan'] . ' unit' : '-');
labeledText($section, "Sarana Lainnya", ($sarana['sarana_lainnya'] ?? 0) > 0 ? $sarana['sarana_lainnya'] . ' unit' : '-');
$section->addTextBreak(1);


$section->addText("Tingkat Pendidikan Masyarakat", ['bold' => true]);
$section->addText("Lulusan Pendidikan Umum", ['bold' => true]);
labeledText($section, 'Taman Kanak-kanak', ($pendidikan['lulusan_tk'] ?? '') . ' orang');
labeledText($section, 'Sekolah Dasar', ($pendidikan['lulusan_sd'] ?? '') . ' orang');
labeledText($section, 'SMP', ($pendidikan['lulusan_smp'] ?? '') . ' orang');
labeledText($section, 'SMA/SMU', ($pendidikan['lulusan_sma'] ?? '') . ' orang');
labeledText($section, 'Akademi/D1-D3', ($pendidikan['lulusan_akademi'] ?? '') . ' orang');
labeledText($section, 'Sarjana', ($pendidikan['lulusan_sarjana'] ?? '') . ' orang');
labeledText($section, 'Pasca Sarjana', ($pendidikan['lulusan_pascasarjana'] ?? '') . ' orang');
$section->addTextBreak(1);
$section->addText("Lulusan Pendidikan Khusus", ['bold' => true]);
labeledText($section, 'Pondok Pesantren', ($pendidikan['lulusan_pondok_pesantren'] ?? '') . ' orang');
labeledText($section, 'Lulusan Pendidikan Keagamaan', ($pendidikan['lulusan_pendidikan_keagamaan'] ?? '') . ' orang');
labeledText($section, 'Lulusan SLB', ($pendidikan['lulusan_slb'] ?? '') . ' orang');
labeledText($section, 'Lulusan Kursus Keterampilan', ($pendidikan['lulusan_kursus_keterampilan'] ?? '') . ' orang');
$section->addTextBreak(1);
$section->addText("III. Data Personil", ['bold' => true, 'size' => 12]);
labeledText($section, 'Nama Lurah', $aparatur_lembaga['nama_lurah'] ?? '');
labeledText($section, 'Nama Sekretaris', $aparatur_lembaga['nama_sekretaris'] ?? '');
$section->addTextBreak(1);
$section->addText("Jumlah Aparat Kantor Kelurahan", ['bold' => true]);
labeledText($section, 'Jumlah Golongan I', ($aparatur_lembaga['golongan_i'] ?? '') . ' orang');
labeledText($section, 'Jumlah Golongan II', ($aparatur_lembaga['golongan_ii'] ?? '') . ' orang');
labeledText($section, 'Jumlah Golongan III', ($aparatur_lembaga['golongan_iii'] ?? '') . ' orang');
labeledText($section, 'Jumlah Golongan IV', ($aparatur_lembaga['golongan_iv'] ?? '') . ' orang');
$section->addTextBreak(1);
$section->addText("Data Kewenangan", ['bold' => true]);
$section->addText("Program yang diterima Kelurahan", ['bold' => true]);
labeledText($section, 'Pemerintah Pusat', $program_bantuan['program_pusat'] ?? '');
labeledText($section, 'Provinsi', $program_bantuan['program_provinsi'] ?? '');
labeledText($section, 'Kabupaten/Kota', $program_bantuan['program_kabupaten'] ?? '');
$section->addTextBreak(1);
$section->addText("Data Keuangan", ['bold' => true]);
labeledText($section, 'Jumlah Dana APBD', 'Rp. ' . number_format($program_bantuan['apbd'] ?? 0, 0, ',', '.'));
$skpd = isset($program_bantuan['skpd_sudah']) ? ($program_bantuan['skpd_sudah'] ? 'Sudah' : 'Belum') : '';
labeledText($section, 'Apakah SKPD Sudah Ada?', $skpd);
labeledText($section, 'Bantuan Pusat', 'Rp. ' . number_format($program_bantuan['bantuan_pusat'] ?? 0, 0, ',', '.'));
labeledText($section, 'Bantuan Provinsi', 'Rp. ' . number_format($program_bantuan['bantuan_provinsi'] ?? 0, 0, ',', '.'));
labeledText($section, 'Bantuan Kabupaten/Kota', 'Rp. ' . number_format($program_bantuan['bantuan_kab_kota'] ?? 0, 0, ',', '.'));
labeledText($section, 'Bantuan Luar Negeri', 'Rp. ' . number_format($program_bantuan['bantuan_luar_negeri'] ?? 0, 0, ',', '.'));
labeledText($section, 'Bantuan Gotong Royong', 'Rp. ' . number_format($program_bantuan['bantuan_gotong_royong'] ?? 0, 0, ',', '.'));
labeledText($section, 'Sumber Bantuan Lainnya', $program_bantuan['bantuan_sumber_lain'] ?? '');
$section->addTextBreak(1);
$section->addText("Kelembagaan", ['bold' => true]);
labeledText($section, 'LPM - Pengurus', $aparatur_lembaga['lpm_pengurus'] ?? '');
labeledText($section, 'LPM - Kegiatan', $aparatur_lembaga['lpm_kegiatan'] ?? '');
labeledText($section, 'LPM - Buku Administrasi', $aparatur_lembaga['lpm_buku_administrasi'] ?? '');
labeledText($section, 'LPM - Dana', 'Rp. ' . ($aparatur_lembaga['lpm_dana'] ?? ''));
labeledText($section, 'TP PKK - Pengurus', $aparatur_lembaga['tp_pkk_pengurus'] ?? '');
labeledText($section, 'TP PKK - Kegiatan', $aparatur_lembaga['tp_pkk_kegiatan'] ?? '');
labeledText($section, 'TP PKK - Buku', $aparatur_lembaga['tp_pkk_buku'] ?? '');
labeledText($section, 'TP PKK - Dana', 'Rp. ' . ($aparatur_lembaga['tp_pkk_dana'] ?? ''));
labeledText($section, 'Jumlah RT', ($aparatur_lembaga['rt'] ?? '') . ' RT');
// labeledText($section, 'Penghasilan RW', 'Rp. ' . ($aparatur_lembaga['penghasilan_rw'] ?? ''));
labeledText($section, 'Penghasilan RT', 'Rp. ' . ($aparatur_lembaga['penghasilan_rt'] ?? ''));
labeledText($section, 'Karang Taruna - Jumlah', $aparatur_lembaga['karang_taruna_jumlah'] ?? '');
labeledText($section, 'Karang Taruna - Pengurus', $aparatur_lembaga['karang_taruna_pengurus'] ?? '');
labeledText($section, 'Lembaga Adat', $aparatur_lembaga['lembaga_adat'] ?? '');
labeledText($section, 'Lembaga Lainnya', $aparatur_lembaga['lembaga_lainnya'] ?? '');
$section->addTextBreak(1);

while (ob_get_level()) {
    ob_end_clean();
}

// Matikan error display (biar tidak nyelip ke file)
ini_set('display_errors', 0);

// Header download
header("Content-Description: File Transfer");
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=Buku_Monografi_" . strtoupper($kelurahan) . ".docx");
header("Cache-Control: max-age=0");

// Output Word
$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save("php://output");
exit;