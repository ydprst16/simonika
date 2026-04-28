<?php
function getMonografiId($conn, $wilayah_id, $tahun)
{
    $stmt = $conn->prepare("
        SELECT id FROM monografi_tahun 
        WHERE wilayah_id = ? AND tahun = ?
    ");
    $stmt->bind_param("ii", $wilayah_id, $tahun);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        return $row['id'];
    }

    return null;
}

function createMonografiIfNotExist($conn, $wilayah_id, $tahun, $bulan)
{
    $id = getMonografiId($conn, $wilayah_id, $tahun);

    if (!$id) {
        $stmt = $conn->prepare("
            INSERT INTO monografi_tahun (wilayah_id, tahun, bulan)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $wilayah_id, $tahun, $bulan);
        $stmt->execute();
        return $stmt->insert_id;
    }

    return $id;
}

function getData($conn, $table, $monografi_id)
{
    if (!$monografi_id)
        return [];

    $stmt = $conn->prepare("SELECT * FROM $table WHERE monografi_id = ?");
    $stmt->bind_param("i", $monografi_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?? [];
}

function getAllMonografiData($conn, $monografi_id)
{
    return [
        'batas' => getData($conn, 'wilayah_batas_jarak', $monografi_id),
        'demografi' => getData($conn, 'demografi', $monografi_id),
        'sarana' => getData($conn, 'sarana', $monografi_id),
        'pendidikan' => getData($conn, 'pendidikan', $monografi_id),
        'program' => getData($conn, 'program_bantuan', $monografi_id),
        'aparatur' => getData($conn, 'aparatur_lembaga', $monografi_id),
    ];
}