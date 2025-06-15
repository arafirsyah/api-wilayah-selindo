<?php
require_once __DIR__ . '/../../includes/DataLoader.php';
header("Content-Type: application/json");
echo json_encode(DataLoader::loadCSV('provinces.csv'));
