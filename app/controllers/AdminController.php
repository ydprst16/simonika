<?php

function getDashboardAdmin($conn)
{
    $tahun_target = date("Y");

    $query = "
    SELECT 
        w.id,
        w.kelurahan,
        w.kecamatan,
        mt.tahun
    FROM wilayah w
    LEFT JOIN monografi_tahun mt ON mt.wilayah_id = w.id
    ORDER BY w.kelurahan ASC, mt.tahun DESC
    ";

    //$result = $conn->query($query);
    require_once __DIR__ . '/../models/WilayahModel.php';
    $result = getAllWilayahWithTahun($conn);

    $data = [];
    $total_kelurahan = 0;
    $sudah_update = 0;

    while ($row = $result->fetch_assoc()) {

        $id = $row['id'];

        // grouping per kelurahan
        if (!isset($data[$id])) {
            $data[$id] = [
                'kelurahan' => $row['kelurahan'],
                'kecamatan' => $row['kecamatan'],
                'tahun' => []
            ];
            $total_kelurahan++;
        }

        // kumpulkan tahun
        if ($row['tahun']) {
            $data[$id]['tahun'][] = $row['tahun'];
        }
    }

    // tentukan status
    foreach ($data as &$row) {

        $tahun_terbaru = $row['tahun'][0] ?? null;

        if ($tahun_terbaru == $tahun_target) {
            $row['status'] = 'sudah';
            $sudah_update++;
        } else {
            $row['status'] = 'belum';
        }
    }

    // hitung statistik
    $belum_update = $total_kelurahan - $sudah_update;

    $persentase = $total_kelurahan > 0
        ? round(($sudah_update / $total_kelurahan) * 100)
        : 0;

    // IMPORTANT: return format harus sama dengan yang dipakai di view
    return [
        'data' => $data,
        'total_kelurahan' => $total_kelurahan,
        'sudah_update' => $sudah_update,
        'belum_update' => $belum_update,
        'persentase' => $persentase,
        'tahun_target' => $tahun_target
    ];
}