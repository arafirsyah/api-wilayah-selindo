<?php
require_once __DIR__ . '/../../includes/DataLoader.php';
header("Content-Type: application/json");

$id_prov = $_GET['id_provinsi'] ?? null;
if (!$id_prov) {
    echo json_encode(["error" => "Parameter id_provinsi diperlukan"]);
    exit;
}

echo json_encode(DataLoader::loadCSV('regencies.csv', 'province_id', $id_prov));
