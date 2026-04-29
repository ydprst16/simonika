<?php

/**
 * Ambil data kelurahan + tahun
 */
function getKelurahanData($conn)
{
    $conn->query("SET SESSION group_concat_max_len = 1000000");

    $query = "
        SELECT w.id, w.kelurahan,
               GROUP_CONCAT(m.tahun ORDER BY m.tahun DESC) as tahun_list
        FROM wilayah w
        LEFT JOIN monografi_tahun m ON w.id = m.wilayah_id
        GROUP BY w.id
        ORDER BY w.kelurahan ASC
    ";

    $result = $conn->query($query);

    if (!$result) {
        die("Query error (Kelurahan): " . $conn->error);
    }

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['tahun_list'] = $row['tahun_list']
            ? explode(',', $row['tahun_list'])
            : [];

        $data[] = $row;
    }

    return $data;
}


/**
 * Total kelurahan
 */
function getTotalKelurahan($conn)
{
    $result = $conn->query("SELECT COUNT(*) as total FROM wilayah");

    if (!$result) {
        die("Query error (Total): " . $conn->error);
    }

    return $result->fetch_assoc()['total'] ?? 0;
}


/**
 * Tahun global
 */
function getTahunGlobal($conn)
{
    $result = $conn->query("
        SELECT DISTINCT tahun 
        FROM monografi_tahun 
        ORDER BY tahun DESC
    ");

    if (!$result) {
        die("Query error (Tahun): " . $conn->error);
    }

    $tahun = [];

    while ($row = $result->fetch_assoc()) {
        $tahun[] = $row['tahun'];
    }

    return $tahun;
}


/**
 * SUMMARY (FIX PALING PENTING)
 */
function getSummaryDashboard($conn)
{
    $tahun_target = date('Y');

    // 🔥 TANPA GROUP_CONCAT (lebih aman di Railway)
    $query = "
        SELECT 
            w.id,
            COUNT(CASE WHEN m.tahun = $tahun_target THEN 1 END) as sudah
        FROM wilayah w
        LEFT JOIN monografi_tahun m ON w.id = m.wilayah_id
        GROUP BY w.id
    ";

    $result = $conn->query($query);

    if (!$result) {
        die("Query error (Summary): " . $conn->error);
    }

    $sudah = 0;
    $belum = 0;

    while ($row = $result->fetch_assoc()) {
        if ($row['sudah'] > 0) {
            $sudah++;
        } else {
            $belum++;
        }
    }

    $total = $sudah + $belum;

    $persen = $total > 0
        ? round(($sudah / $total) * 100)
        : 0;

    return [
        'sudah' => $sudah,
        'belum' => $belum,
        'persen' => $persen,
        'tahun_target' => $tahun_target
    ];
}