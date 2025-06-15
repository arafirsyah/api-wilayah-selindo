<?php
require_once __DIR__ . '/../../includes/DataLoader.php';
header("Content-Type: application/json");

$id_kec = $_GET['id_kecamatan'] ?? null;
if (!$id_kec) {
    echo json_encode(["error" => "Parameter id_kecamatan diperlukan"]);
    exit;
}

echo json_encode(DataLoader::loadCSV('villages.csv', 'district_id', $id_kec));
