<?php

function getStatistik($conn)
{
    $tahun = date('Y');

    // jumlah kelurahan yang sudah isi monografi
    $q1 = mysqli_query($conn, "
        SELECT COUNT(DISTINCT wilayah_id) as total 
        FROM monografi_tahun 
        WHERE tahun = '$tahun'
    ");

    $total_kelurahan = 0;
    if ($q1) {
        $data = mysqli_fetch_assoc($q1);
        $total_kelurahan = $data['total'] ?? 0;
    }

    // jumlah total data monografi
    $q2 = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM monografi_tahun 
        WHERE tahun = '$tahun'
    ");

    $total_data = 0;
    if ($q2) {
        $data = mysqli_fetch_assoc($q2);
        $total_data = $data['total'] ?? 0;
    }

    return [
        'total_kelurahan' => $total_kelurahan,
        'total_data' => $total_data,
        'tahun' => $tahun
    ];
}


function getGaleriDummy()
{
    return [
        ["src" => "assets/images/foto1.jpeg", "title" => "Pelatihan Operator"],
        ["src" => "assets/images/foto2.jpeg", "title" => "Pendataan"],
        ["src" => "assets/images/foto1.jpeg", "title" => "Rapat"],
        ["src" => "assets/images/foto2.jpeg", "title" => "Workshop"],
        ["src" => "assets/images/foto1.jpeg", "title" => "Kegiatan"]
    ];
}