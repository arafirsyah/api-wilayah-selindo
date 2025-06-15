<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("PHP SELF: " . $_SERVER['PHP_SELF']);
error_log("SCRIPT NAME: " . $_SERVER['SCRIPT_NAME']);

// Di awal script, setelah deklarasi header
$request = isset($_GET['url']) ? '/'.$_GET['url'] : $_SERVER['REQUEST_URI'];

require_once 'config.php';

$db = new Database();
$connection = $db->getConnection();

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetRequest($request, $connection);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleGetRequest($request, $connection) {
    // Membersihkan request URI dari query string
    $request = strtok($request, '?');
    
    // Menentukan base path
    $base_path = '/api_wilayah_indonesia';
    
    // Menghapus base path dari request
    if (strpos($request, $base_path) === 0) {
        $request = substr($request, strlen($base_path));
    }
    
    $parts = explode('/', trim($request, '/'));
    
    // Debugging
    error_log("Processed request: " . $request);
    error_log("Parts after split: " . print_r($parts, true));
    
    if (count($parts) === 1 && $parts[0] === 'provinces') {
        getProvinces($connection);
    } 
    elseif (count($parts) === 2 && $parts[0] === 'regencies' && is_numeric($parts[1])) {
        getRegencies($connection, $parts[1]);
    } 
    elseif (count($parts) === 2 && $parts[0] === 'districts' && is_numeric($parts[1])) {
        getDistricts($connection, $parts[1]);
    } 
    elseif (count($parts) === 2 && $parts[0] === 'villages' && is_numeric($parts[1])) {
        getVillages($connection, $parts[1]);
    } 
    elseif (count($parts) === 3 && $parts[0] === 'provinces' && is_numeric($parts[1]) && $parts[2] === 'regencies') {
        getRegenciesByProvince($connection, $parts[1]);
    } 
    elseif (count($parts) === 3 && $parts[0] === 'regencies' && is_numeric($parts[1]) && $parts[2] === 'districts') {
        getDistrictsByRegency($connection, $parts[1]);
    } 
    elseif (count($parts) === 3 && $parts[0] === 'districts' && is_numeric($parts[1]) && $parts[2] === 'villages') {
        getVillagesByDistrict($connection, $parts[1]);
    } 
    else {
        http_response_code(404);
        echo json_encode(array(
            "message" => "Endpoint not found",
            "request" => $request, // Tambahkan info request untuk debugging
            "valid_endpoints" => [
                "GET /provinces",
                "GET /provinces/{id}/regencies",
                "GET /regencies/{id}",
                "GET /regencies/{id}/districts",
                "GET /districts/{id}",
                "GET /districts/{id}/villages",
                "GET /villages/{id}"
            ]
        ));
    }
}

function getProvinces($connection) {
    try {
        $query = "SELECT id, name FROM provinces ORDER BY name";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        
        $provinces = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $province = array(
                "id" => $row['id'],
                "name" => $row['name']
            );
            array_push($provinces, $province);
        }
        
        http_response_code(200);
        echo json_encode($provinces);
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to get provinces. " . $exception->getMessage()));
    }
}

function getRegencies($connection, $id) {
    try {
        $query = "SELECT id, province_id, name FROM regencies WHERE id = :id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $regency = array(
                "id" => $row['id'],
                "province_id" => $row['province_id'],
                "name" => $row['name']
            );
            
            http_response_code(200);
            echo json_encode($regency);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Regency not found"));
        }
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to get regency. " . $exception->getMessage()));
    }
}

function getRegenciesByProvince($connection, $province_id) {
    try {
        $query = "SELECT id, name FROM regencies WHERE province_id = :province_id ORDER BY name";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':province_id', $province_id);
        $stmt->execute();
        
        $regencies = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $regency = array(
                "id" => $row['id'],
                "name" => $row['name']
            );
            array_push($regencies, $regency);
        }
        
        http_response_code(200);
        echo json_encode($regencies);
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to get regencies. " . $exception->getMessage()));
    }
}

function getDistricts($connection, $id) {
    try {
        $query = "SELECT id, regency_id, name FROM districts WHERE id = :id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $district = array(
                "id" => $row['id'],
                "regency_id" => $row['regency_id'],
                "name" => $row['name']
            );
            
            http_response_code(200);
            echo json_encode($district);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "District not found"));
        }
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to get district. " . $exception->getMessage()));
    }
}

function getDistrictsByRegency($connection, $regency_id) {
    try {
        $query = "SELECT id, name FROM districts WHERE regency_id = :regency_id ORDER BY name";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':regency_id', $regency_id);
        $stmt->execute();
        
        $districts = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $district = array(
                "id" => $row['id'],
                "name" => $row['name']
            );
            array_push($districts, $district);
        }
        
        http_response_code(200);
        echo json_encode($districts);
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to get districts. " . $exception->getMessage()));
    }
}

function getVillages($connection, $id) {
    try {
        $query = "SELECT id, district_id, name FROM villages WHERE id = :id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $village = array(
                "id" => $row['id'],
                "district_id" => $row['district_id'],
                "name" => $row['name']
            );
            
            http_response_code(200);
            echo json_encode($village);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Village not found"));
        }
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to get village. " . $exception->getMessage()));
    }
}

function getVillagesByDistrict($connection, $district_id) {
    try {
        $query = "SELECT id, name FROM villages WHERE district_id = :district_id ORDER BY name";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':district_id', $district_id);
        $stmt->execute();
        
        $villages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $village = array(
                "id" => $row['id'],
                "name" => $row['name']
            );
            array_push($villages, $village);
        }
        
        http_response_code(200);
        echo json_encode($villages);
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to get villages. " . $exception->getMessage()));
    }
}
?>