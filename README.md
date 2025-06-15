# Wilayah Indonesia Static API

A static JSON API for Indonesian administrative divisions (Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan/Desa).

## Features

- 🚀 Blazing fast (static file serving)
- 📂 Easy to update (CSV files in `/data`)
- 🌍 CORS enabled
- 📱 Lightweight and simple

## API Endpoints

- `GET /api/provinces.json` - Get all provinces
- `GET /api/provinces/{id}/regencies.json` - Get regencies by province ID
- `GET /api/regencies/{id}/districts.json` - Get districts by regency ID
- `GET /api/districts/{id}/villages.json` - Get villages by district ID

## Setup

1. Make sure you have PHP installed
2. Clone this repository
3. Place your CSV files in the `/data` directory:
   - `provinces.csv`
   - `regencies.csv`
   - `districts.csv`
   - `villages.csv`

## Generate Static API

Run the generator script:

```bash
php generate.php
```

This will create static JSON files in the `/static/api` directory.

## Update Data

1. Update the CSV files in the `/data` directory
2. Run `php generate.php` to regenerate the static files

## Example Usage

```javascript
// Get all provinces
fetch('/api-wilayah-selindo/static/api/provinces.json')
  .then(response => response.json())
  .then(data => console.log(data));

// Get regencies by province ID
fetch('/api-wilayah-selindo/static/api/provinces/32/regencies.json')
  .then(response => response.json())
  .then(data => console.log(data));
```

## License

MIT
