<?php

require_once __DIR__ . '/../helpers/helper.php';
require_once __DIR__ . '/../models/WilayahModel.php';

function getMonographData($conn, $kelurahan, $tahun)
{
    $wilayah = getWilayahByKelurahan($conn, $kelurahan);

    if (!$wilayah) {
        die("Data tidak ditemukan");
    }

    $wilayah_id = $wilayah['id'];

    $monografi_id = getMonografiId($conn, $wilayah_id, $tahun) ?? 0;

    $data = getAllMonografiData($conn, $monografi_id);

    return [
        'wilayah' => $wilayah,
        'data' => $data
    ];
}