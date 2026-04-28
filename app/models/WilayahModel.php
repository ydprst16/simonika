<?php

function getAllWilayahWithTahun($conn)
{
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

    return $conn->query($query);
}

function getWilayahByKelurahan($conn, $kelurahan)
{
    $stmt = $conn->prepare("SELECT * FROM wilayah WHERE kelurahan = ?");
    $stmt->bind_param("s", $kelurahan);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}