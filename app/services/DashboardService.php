<?php

/**
 * ========================================
 * DASHBOARD SERVICE
 * ========================================
 */

/**
 * Ambil data grid kelurahan + list tahun
 */
function getKelurahanData($conn)
{
    $query = "
        SELECT w.id, w.kelurahan,
               GROUP_CONCAT(m.tahun ORDER BY m.tahun DESC) as tahun_list
        FROM wilayah w
        LEFT JOIN monografi_tahun m ON w.id = m.wilayah_id
        GROUP BY w.id
        ORDER BY w.kelurahan ASC
    ";

    $result = $conn->query($query);

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
 * Ambil total kelurahan
 */
function getTotalKelurahan($conn)
{
    $result = $conn->query("SELECT COUNT(*) as total FROM wilayah");
    return $result->fetch_assoc()['total'] ?? 0;
}

/**
 * Ambil list tahun untuk filter global
 */
function getTahunGlobal($conn)
{
    $result = $conn->query("
        SELECT DISTINCT tahun 
        FROM monografi_tahun 
        ORDER BY tahun DESC
    ");

    $tahun = [];

    while ($row = $result->fetch_assoc()) {
        $tahun[] = $row['tahun'];
    }
    return $tahun;
}

/**
 * Ambil summary dashboard
 * - sudah update
 * - belum update
 * - persentase
 */
function getSummaryDashboard($conn)
{
    $tahun_target = date('Y');

    $sudah = 0;
    $belum = 0;

    $query = "
        SELECT w.id,
               GROUP_CONCAT(m.tahun) as tahun_list
        FROM wilayah w
        LEFT JOIN monografi_tahun m ON w.id = m.wilayah_id
        GROUP BY w.id
    ";

    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {

        $tahunArr = $row['tahun_list']
            ? explode(',', $row['tahun_list'])
            : [];

        if (in_array($tahun_target, $tahunArr)) {
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