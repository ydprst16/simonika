<?php

require_once __DIR__ . '/../helpers/helper.php';

function saveOrUpdate($conn, $table, $data, $monografi_id)
{
    $check = $conn->prepare("SELECT id FROM $table WHERE monografi_id = ?");
    $check->bind_param("i", $monografi_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->fetch_assoc()) {

        // ================= UPDATE =================
        $fields = [];
        foreach ($data as $key => $val) {
            $fields[] = "$key = ?";
        }

        $sql = "UPDATE $table SET " . implode(",", $fields) . " WHERE monografi_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare UPDATE gagal ($table): " . $conn->error);
        }

        $types = str_repeat("s", count($data)) . "i";
        $params = array_values($data);
        $params[] = $monografi_id;

        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception("UPDATE gagal ($table): " . $stmt->error);
        }

    } else {

        // ================= INSERT =================
        $data['monografi_id'] = $monografi_id;

        $fields = implode(",", array_keys($data));
        $placeholders = implode(",", array_fill(0, count($data), "?"));

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare INSERT gagal ($table): " . $conn->error);
        }

        $types = str_repeat("s", count($data));

        $stmt->bind_param($types, ...array_values($data));

        if (!$stmt->execute()) {
            throw new Exception("INSERT gagal ($table): " . $stmt->error);
        }
    }
}


function saveMonografi($conn, $post)
{
    // ================= NORMALISASI =================
    $post = array_map(function ($v) {
        return ($v === '') ? null : $v;
    }, $post);

    $wilayah_id = $post['wilayah_id'] ?? null;
    $tahun = $post['tahun'] ?? date('Y');
    $bulan = $post['bulan'] ?? '';

    if (!$wilayah_id) {
        throw new Exception("Wilayah tidak valid");
    }

    // ================= MONOGRAFI =================
    $monografi_id = createMonografiIfNotExist($conn, $wilayah_id, $tahun, $bulan);

    if (!$monografi_id) {
        throw new Exception("Gagal membuat monografi_id");
    }

    // ================= 1. BATAS =================
    saveOrUpdate($conn, 'wilayah_batas_jarak', [
        'tipologi_kelurahan' => $post['tipologi_kelurahan'] ?? null,
        'luas_wilayah' => $post['luas_wilayah'] ?? null,
        'batas_wilayah_utara' => $post['batas_wilayah_utara'] ?? null,
        'batas_wilayah_selatan' => $post['batas_wilayah_selatan'] ?? null,
        'batas_wilayah_barat' => $post['batas_wilayah_barat'] ?? null,
        'batas_wilayah_timur' => $post['batas_wilayah_timur'] ?? null,
        'jarak_pusat_pemerintahan_kecamatan' => $post['jarak_pusat_pemerintahan_kecamatan'] ?? null,
        'jarak_pusat_pemerintahan_kota' => $post['jarak_pusat_pemerintahan_kota'] ?? null,
        'jarak_ibukota_kabupaten' => $post['jarak_ibukota_kabupaten'] ?? null,
        'jarak_ibukota_provinsi' => $post['jarak_ibukota_provinsi'] ?? null,
    ], $monografi_id);

    // ================= 2. DEMOGRAFI =================
    saveOrUpdate($conn, 'demografi', [
        'jumlah_penduduk_laki_laki' => $post['jumlah_penduduk_laki_laki'] ?? null,
        'jumlah_penduduk_perempuan' => $post['jumlah_penduduk_perempuan'] ?? null,
        'jumlah_penduduk_usia_0_15' => $post['jumlah_penduduk_usia_0_15'] ?? null,
        'jumlah_penduduk_usia_15_65' => $post['jumlah_penduduk_usia_15_65'] ?? null,
        'jumlah_penduduk_usia_65_keatas' => $post['jumlah_penduduk_usia_65_keatas'] ?? null,
        'mayoritas_pekerjaan' => $post['mayoritas_pekerjaan'] ?? null,
        'jumlah_penduduk_miskin_kk' => $post['jumlah_penduduk_miskin_kk'] ?? null,
        'jumlah_penduduk_miskin_jiwa' => $post['jumlah_penduduk_miskin_jiwa'] ?? null,
        'umr_kabupaten_kota' => $post['umr_kabupaten_kota'] ?? null,
    ], $monografi_id);

    // ================= 3. SARANA =================
    saveOrUpdate($conn, 'sarana', [
        'kantor_kelurahan' => $post['kantor_kelurahan'] ?? null,
        'puskesmas' => $post['puskesmas'] ?? null,
        'ukbm_posyandu' => $post['ukbm_posyandu'] ?? null,
        'poliklinik' => $post['poliklinik'] ?? null,
        'masjid' => $post['masjid'] ?? 0,
        'mushola' => $post['mushola'] ?? 0,
        'gereja' => $post['gereja'] ?? 0,
        'pura' => $post['pura'] ?? 0,
        'vihara' => $post['vihara'] ?? 0,
        'klenteng' => $post['klenteng'] ?? 0,
    ], $monografi_id);

    // ================= 4. PENDIDIKAN =================
    saveOrUpdate($conn, 'pendidikan', [
        'prasarana_paud' => $post['prasarana_paud'] ?? 0,
        'prasarana_sd' => $post['prasarana_sd'] ?? 0,
        'prasarana_smp' => $post['prasarana_smp'] ?? 0,
        'prasarana_sma' => $post['prasarana_sma'] ?? 0,
    ], $monografi_id);

    // ================= 5. PROGRAM =================
    saveOrUpdate($conn, 'program_bantuan', [
        'skpd_sudah' => $post['skpd_sudah'] ?? null,
        'program_pusat' => $post['program_pusat'] ?? null,
        'program_provinsi' => $post['program_provinsi'] ?? null,
        'program_kabupaten' => $post['program_kabupaten'] ?? null,
        'apbd' => $post['apbd'] ?? null,
        'bantuan_pusat' => $post['bantuan_pusat'] ?? null,
        'bantuan_provinsi' => $post['bantuan_provinsi'] ?? null,
        'bantuan_kab_kota' => $post['bantuan_kab_kota'] ?? null,
        'bantuan_luar_negeri' => $post['bantuan_luar_negeri'] ?? null,
        'bantuan_gotong_royong' => $post['bantuan_gotong_royong'] ?? null,
        'bantuan_sumber_lain' => $post['bantuan_sumber_lain'] ?? null,
        'bulan' => $bulan,
        'tahun' => $tahun,
    ], $monografi_id);

    // ================= 6. APARATUR =================
    saveOrUpdate($conn, 'aparatur_lembaga', [
        'nama_lurah' => $post['nama_lurah'] ?? null,
        'nama_sekretaris' => $post['nama_sekretaris'] ?? null,

        'golongan_i' => $post['golongan_i'] ?? null,
        'golongan_ii' => $post['golongan_ii'] ?? null,
        'golongan_iii' => $post['golongan_iii'] ?? null,
        'golongan_iv' => $post['golongan_iv'] ?? null,

        'lpm_pengurus' => $post['lpm_pengurus'] ?? null,
        'lpm_kegiatan' => $post['lpm_kegiatan'] ?? null,
        'lpm_buku_administrasi' => $post['lpm_buku_administrasi'] ?? null,
        'lpm_dana' => $post['lpm_dana'] ?? null,

        'tp_pkk_pengurus' => $post['tp_pkk_pengurus'] ?? null,
        'tp_pkk_kegiatan' => $post['tp_pkk_kegiatan'] ?? null,
        'tp_pkk_buku' => $post['tp_pkk_buku'] ?? null,
        'tp_pkk_dana' => $post['tp_pkk_dana'] ?? null,

        'rt' => $post['rt'] ?? null,
        'penghasilan_rt' => $post['penghasilan_rt'] ?? null,
        'karang_taruna_jumlah' => $post['karang_taruna_jumlah'] ?? null,
        'karang_taruna_pengurus' => $post['karang_taruna_pengurus'] ?? null,

        'lembaga_adat' => $post['lembaga_adat'] ?? null,
        'lembaga_lainnya' => $post['lembaga_lainnya'] ?? null,
    ], $monografi_id);

    return true;
}