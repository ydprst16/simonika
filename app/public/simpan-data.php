<?php
session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../services/MonografiService.php';

$kelurahan = $_POST['kelurahan'] ?? '';
$wilayah_id = $_POST['wilayah_id'] ?? '';
$tahun = $_POST['tahun'] ?? '';
$mode = $_POST['mode'] ?? 'create';

// VALIDASI
if (!$wilayah_id || !$tahun) {
    header("Location: input-data.php?kelurahan=" . urlencode($kelurahan) . "&status=error");
    exit();
}

$conn->begin_transaction();

try {

    if ($mode === 'create') {

        $stmt = $conn->prepare("
            SELECT id FROM monografi_tahun 
            WHERE wilayah_id = ? AND tahun = ?
        ");
        $stmt->bind_param("ii", $wilayah_id, $tahun);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $conn->rollback();
            header("Location: input-data.php?kelurahan=" . urlencode($kelurahan) . "&status=duplicate");
            exit();
        }
    }

    saveMonografi($conn, $_POST);

    $conn->commit();

    header("Location: input-data.php?kelurahan=" . urlencode($kelurahan) . "&status=success");
    exit();

} catch (Throwable $e) {

    $conn->rollback();

    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        header("Location: input-data.php?kelurahan=" . urlencode($kelurahan) . "&status=duplicate");
        exit();
    }

    error_log("ERROR SIMPAN: " . $e->getMessage());

    header("Location: input-data.php?kelurahan=" . urlencode($kelurahan) . "&status=error");
    exit();
}