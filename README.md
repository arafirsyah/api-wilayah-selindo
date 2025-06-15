Wilayah Indonesia Static API
---
## 🔍 Deskripsi
`api-wilayah-selindo` adalah API berbasis data wilayah administratif Indonesia dalam format JSON yang dihasilkan dari file CSV. Menyediakan data provinsi, kabupaten/kota, kecamatan, dan kelurahan untuk integrasi ke sistem informasi atau website.

---

## 📦 Fitur Utama
- ✅ RESTful API berbasis file JSON
- ✅ AJAX chaining: Provinsi → Kabupaten → Kecamatan → Kelurahan
- ✅ File data bersumber dari CSV
- ✅ Tidak memerlukan database atau backend
- ✅ Hosting via GitHub Pages (statis)

---

## 🚀 Akses
Frontend:  
https://arafirsyah.github.io/api-wilayah-selindo/


Contoh endpoint API:  
https://arafirsyah.github.io/api-wilayah-selindo/static/api/provinces.json

---

API Endpoints
GET /api/provinces.json - Get all provinces
GET /api/provinces/{id}/regencies.json - Get regencies by province ID
GET /api/regencies/{id}/districts.json - Get districts by regency ID
GET /api/districts/{id}/villages.json - Get villages by district ID

---

Setup
1. Pastikan sudah menginstall PHP
2. Clone repository
3. Tempatkan file CSV files di folder /data :
	- provinces.csv
	- regencies.csv
	- districts.csv
	- villages.csv

---

Generate Static API
Jalankan script generator :

php generate.php

file tersebut akan membuat file JSON static di folder /static/api directory.

---
Contoh Penggunaan :
// Get all provinces
fetch('/api-wilayah-selindo/static/api/provinces.json')
  .then(response => response.json())
  .then(data => console.log(data));

// Get regencies by province ID
fetch('/api-wilayah-selindo/static/api/provinces/32/regencies.json')
  .then(response => response.json())
  .then(data => console.log(data));

---

## 📂 Struktur Folder
- `/public/` atau `/docs/`: tampilan utama frontend
- `/data/`: CSV sumber data
- `/static/api/`: JSON hasil parsing
- `/index.html`: halaman utama GitHub Pages (dipindah ke root atau `docs/`)

---

## 🛠️ Teknologi
- HTML, JS (AJAX)
- PHP (hanya lokal untuk convert CSV → JSON)
- GitHub Pages

---

## 🧾 Lisensi
MIT License  
Silakan fork, modifikasi, dan kontribusikan 💡

---
#trigger commit
