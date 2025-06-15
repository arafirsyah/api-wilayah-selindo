<?php
/**
 * Generate Static API Files
 * 
 * This script reads CSV files from the data directory and generates
 * static JSON API endpoints in the static/api directory.
 */

// Set time limit to unlimited
set_time_limit(0);

// Define paths
$baseDir = __DIR__;
$dataDir = $baseDir . '/data';
$outputDir = $baseDir . '/static/api';

// Create output directory if it doesn't exist
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Function to read CSV file and return array
function readCsvFile($file) {
    $data = [];
    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle, 1000, ',');
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $data[] = array_combine($headers, $row);
        }
        fclose($handle);
    }
    return $data;
}

// Function to save JSON file
function saveJsonFile($path, $data) {
    $dir = dirname($path);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Process provinces
$provinces = readCsvFile($dataDir . '/provinces.csv');
saveJsonFile($outputDir . '/provinces.json', $provinces);

// Process regencies
$regencies = readCsvFile($dataDir . '/regencies.csv');
$regenciesByProvince = [];
foreach ($regencies as $regency) {
    $provinceId = $regency['province_id'];
    if (!isset($regenciesByProvince[$provinceId])) {
        $regenciesByProvince[$provinceId] = [];
    }
    $regenciesByProvince[$provinceId][] = $regency;
}

// Save regencies by province
foreach ($regenciesByProvince as $provinceId => $regencies) {
    saveJsonFile("$outputDir/provinces/$provinceId/regencies.json", $regencies);
}

// Process districts
$districts = readCsvFile($dataDir . '/districts.csv');
$districtsByRegency = [];
foreach ($districts as $district) {
    $regencyId = $district['regency_id'];
    if (!isset($districtsByRegency[$regencyId])) {
        $districtsByRegency[$regencyId] = [];
    }
    $districtsByRegency[$regencyId][] = $district;
}

// Save districts by regency
foreach ($districtsByRegency as $regencyId => $districts) {
    saveJsonFile("$outputDir/regencies/$regencyId/districts.json", $districts);
}

// Process villages with postal codes
$villages = readCsvFile($dataDir . '/villages.csv');
$villagesByDistrict = [];
$postalCodes = readCsvFile($dataDir . '/postal_codes.csv');

// Create postal code lookup
$postalCodeLookup = [];
foreach ($postalCodes as $code) {
    $villageId = $code['village_id'];
    $postalCodeLookup[$villageId] = $code['postal_code'];
}

foreach ($villages as $village) {
    $districtId = $village['district_id'];
    if (!isset($villagesByDistrict[$districtId])) {
        $villagesByDistrict[$districtId] = [];
    }
    
    // Add postal code to village data if available
    $village['postal_code'] = isset($postalCodeLookup[$village['id']]) ? 
        $postalCodeLookup[$village['id']] : null;
    
    $villagesByDistrict[$districtId][] = $village;
}

// Save villages by district with postal codes
foreach ($villagesByDistrict as $districtId => $villages) {
    saveJsonFile("$outputDir/districts/$districtId/villages.json", $villages);
}

// Generate postal codes endpoint
$postalCodeData = [];
foreach ($postalCodes as $code) {
    $villageId = $code['village_id'];
    $postalCodeData[$villageId] = $code['postal_code'];
}
saveJsonFile("$outputDir/postal_codes.json", $postalCodeData);

// Generate postal code search endpoint
$postalCodeSearch = [];
foreach ($postalCodes as $code) {
    $postalCode = $code['postal_code'];
    if (!isset($postalCodeSearch[$postalCode])) {
        $postalCodeSearch[$postalCode] = [];
    }
    $postalCodeSearch[$postalCode][] = $code['village_id'];
}
saveJsonFile("$outputDir/postal_codes_search.json", $postalCodeSearch);

// Create index file with API documentation
$apiDoc = [
    'name' => 'Wilayah Indonesia API',
    'description' => 'Static API for Indonesian administrative divisions',
    'endpoints' => [
        '/api/provinces.json' => 'Get all provinces',
        '/api/provinces/{id}/regencies.json' => 'Get regencies by province ID',
        '/api/regencies/{id}/districts.json' => 'Get districts by regency ID',
        '/api/districts/{id}/villages.json' => 'Get villages by district ID',
        '/api/postal_codes.json' => 'Get all postal codes by village ID',
        '/api/postal_codes_search.json' => 'Search villages by postal code',
    ],
    'github' => 'https://github.com/arafirsyah/api-wilayah-selindo',
    'version' => '1.0.0'
];

saveJsonFile($outputDir . '/index.json', $apiDoc);

echo "Static API generation complete!\n";
?>
