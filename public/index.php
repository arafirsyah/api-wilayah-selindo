<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>API Wilayah Seluruh Indonesia</title>
    <link rel="stylesheet" href="/api-wilayah-selindo/assets/style.css">
</head>
<body>
    <h1>API Wilayah Seluruh Indonesia</h1>

    <div class="search-container">
        <label>Cari Kode Pos:
            <input type="text" id="postalSearch" placeholder="Masukkan kode pos">
            <button onclick="searchPostalCode()">Cari</button>
        </label>
    </div>

    <div class="wilayah-container">
        <label>Provinsi:
            <select id="provinsi"></select>
        </label>
        <label>Kabupaten:
            <select id="kabupaten"></select>
        </label>
        <label>Kecamatan:
            <select id="kecamatan"></select>
        </label>
        <label>Kelurahan:
            <select id="kelurahan"></select>
        </label>
        <div id="postalInfo" class="postal-info"></div>
    </div>

    <script src="/api-wilayah-selindo/assets/script.js"></script>
</body>
</html>
