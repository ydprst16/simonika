<?php

require_once __DIR__ . '/../models/WilayahModel.php';
require_once __DIR__ . '/../helpers/helper.php';

function getInputData($conn, $kelurahan, $tahun)
{
    $wilayah = getWilayahByKelurahan($conn, $kelurahan);

    if (!$wilayah) {
        die("ERROR: wilayah tidak ditemukan");
    }

    $wilayah_id = $wilayah['id'];

    $monografi_id = getMonografiId($conn, $wilayah_id, $tahun) ?? 0;

    $data = getAllMonografiData($conn, $monografi_id);

    return [
        'wilayah' => $wilayah,
        'wilayah_id' => $wilayah_id,
        'monografi_id' => $monografi_id,
        'data' => $data
    ];
}