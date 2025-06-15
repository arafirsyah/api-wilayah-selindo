<?php
require_once __DIR__ . '/../../includes/DataLoader.php';
header("Content-Type: application/json");

$id_kab = $_GET['id_kabupaten'] ?? null;
if (!$id_kab) {
    echo json_encode(["error" => "Parameter id_kabupaten diperlukan"]);
    exit;
}

echo json_encode(DataLoader::loadCSV('districts.csv', 'regency_id', $id_kab));
