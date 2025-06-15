<?php
class DataLoader {
    private static $cacheDir = __DIR__ . '/../cache/';

    public static function loadCSV($filename, $filterKey = null, $filterValue = null) {
        $path = __DIR__ . "/../data/$filename";
        if (!file_exists($path)) {
            http_response_code(404);
            echo json_encode(["error" => "File not found: $filename"]);
            exit;
        }

        // Caching key
        $cacheKey = md5($filename . $filterKey . $filterValue);
        $cacheFile = self::$cacheDir . $cacheKey . '.json';

        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir);
        }

        if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($path)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        // Load from CSV
        $rows = [];
        if (($handle = fopen($path, "r")) !== FALSE) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== FALSE) {
                $row = array_combine($headers, $data);
                if ($filterKey === null || ($row[$filterKey] ?? null) === $filterValue) {
                    $rows[] = $row;
                }
            }
            fclose($handle);
        }

        file_put_contents($cacheFile, json_encode($rows));
        return $rows;
    }
}
