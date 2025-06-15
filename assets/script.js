async function loadData(url, targetId, childId = null) {
    const select = document.getElementById(targetId);
    const childSelect = childId ? document.getElementById(childId) : null;

    // Reset dropdown yang dipilih dan dropdown turunannya
    select.innerHTML = '<option value="">Silakan Pilih</option>';
    if (childSelect) childSelect.innerHTML = '<option value="">Silakan Pilih</option>';

    try {
        // Membangun path lengkap dari direktori public
        const fullPath = `/api-wilayah-selindo/static/api/${url}`;
        const res = await fetch(fullPath);
        if (!res.ok) {
            throw new Error(`Error HTTP! status: ${res.status}`);
        }
        const data = await res.json();

        if (!Array.isArray(data)) {
            console.error("Gagal memuat data:", data);
            return;
        }

        // Pastikan data adalah array dan memiliki properti name
        if (!Array.isArray(data)) {
            console.error('Data yang diterima bukan array:', data);
            return;
        }

        // Debug: Tampilkan data sebelum diurutkan
        console.log('Data sebelum diurutkan:', data.map(item => item.name));

        // Mengurutkan data berdasarkan nama secara ascending (A-Z)
        data.sort((a, b) => {
            if (!a.name || !b.name) {
                console.error('Data tidak valid:', { a, b });
                return 0;
            }
            return a.name.localeCompare(b.name, 'id');
        });
        
        // Debug: Tampilkan data setelah diurutkan
        console.log('Data setelah diurutkan:', data.map(item => item.name));
        
        // Mengisi dropdown dengan data yang sudah diurutkan
        data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            if (item.postal_code) {
                opt.textContent += ` (${item.postal_code})`;
            }
            select.appendChild(opt);
        });
    } catch (e) {
        console.error("Kesalahan AJAX:", e);
        document.getElementById('postalInfo').innerHTML = `
            <div class="postal-result error">
                Terjadi kesalahan saat memuat data: ${e.message}
            </div>
        `;
    }
}

async function searchPostalCode() {
    const postalCode = document.getElementById('postalSearch').value;
    if (!postalCode) return;

    try {
        // Use the correct path for postal code search
        const res = await fetch(`/api-wilayah-selindo/static/api/postal_codes_search.json`);
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        const data = await res.json();
        
        if (data[postalCode]) {
            const villageIds = data[postalCode];
            const firstVillageId = villageIds[0];
            
            // Validate district ID format (should be 6 digits)
            const districtId = firstVillageId.slice(0, 6);
            if (districtId.length !== 6) {
                throw new Error(`Invalid district ID format: ${districtId}`);
            }
            
            // Mendapatkan detail kelurahan dengan pengurutan
            const villageRes = await fetch(`/api-wilayah-selindo/static/api/districts/${districtId}/villages.json`);
            if (!villageRes.ok) {
                throw new Error(`HTTP error! status: ${villageRes.status}`);
            }
            let villages = await villageRes.json();
            // Mengurutkan kelurahan berdasarkan nama
            villages.sort((a, b) => a.name.localeCompare(b.name, 'id'));
            const village = villages.find(v => v.id === firstVillageId);
            
            if (!village) {
                throw new Error(`Village with ID ${firstVillageId} not found in district ${districtId}`);
            }
            
            // Get district name from the district data
            const regencyIdForDistrict = districtId.slice(0, 4);
            const districtRes = await fetch(`/api-wilayah-selindo/static/api/regencies/${regencyIdForDistrict}/districts.json`);
            if (!districtRes.ok) {
                throw new Error(`HTTP error! status: ${districtRes.status}`);
            }
            let districts = await districtRes.json();
            // Mengurutkan kecamatan berdasarkan nama
            districts.sort((a, b) => a.name.localeCompare(b.name, 'id'));
            const district = districts.find(d => d.id === districtId);
            if (!district) {
                throw new Error(`District with ID ${districtId} not found in regency ${regencyIdForDistrict}`);
            }
            const districtName = district.name;

            // Get regency name from the regency data
            const regencyId = districtId.slice(0, 4);
            const provinceIdForRegency = regencyId.slice(0, 2);
            const regencyRes = await fetch(`/api-wilayah-selindo/static/api/provinces/${provinceIdForRegency}/regencies.json`);
            if (!regencyRes.ok) {
                throw new Error(`HTTP error! status: ${regencyRes.status}`);
            }
            let regencies = await regencyRes.json();
            // Mengurutkan kabupaten/kota berdasarkan nama
            regencies.sort((a, b) => a.name.localeCompare(b.name, 'id'));
            const regency = regencies.find(r => r.id === regencyId);
            if (!regency) {
                throw new Error(`Regency with ID ${regencyId} not found in province ${provinceIdForRegency}`);
            }
            const regencyName = regency.name;

            // Get province name from the province data
            const provinceId = districtId.slice(0, 2);
            const provinceRes = await fetch(`/api-wilayah-selindo/static/api/provinces.json`);
            if (!provinceRes.ok) {
                throw new Error(`HTTP error! status: ${provinceRes.status}`);
            }
            const provinces = await provinceRes.json();
            const province = provinces.find(p => p.id === provinceId);
            if (!province) {
                throw new Error(`Province with ID ${provinceId} not found.`);
            }
            const provinceName = province.name;

            document.getElementById('postalInfo').innerHTML = `
                <div class="postal-result">
                    <h3>Hasil Pencarian Kode Pos ${postalCode}</h3>
                    <p>${village.name} (${village.postal_code})</p>
                    <p>Kecamatan: ${districtName}</p>
                    <p>Kabupaten: ${regencyName}</p>
                    <p>Provinsi: ${provinceName}</p>
                </div>
            `;
        } else {
            document.getElementById('postalInfo').innerHTML = `
                <div class="postal-result error">
                    Kode pos ${postalCode} tidak ditemukan
                </div>
            `;
        }
    } catch (e) {
        console.error("Error searching postal code:", e);
        document.getElementById('postalInfo').innerHTML = `
            <div class="postal-result error">
                Terjadi kesalahan saat mencari kode pos: ${e.message}
            </div>
        `;
    }
}

async function showVillagePostalCode(village) {
    if (village.postal_code) {
        document.getElementById('postalInfo').innerHTML = `
            <div class="postal-result">
                <h3>Informasi Kode Pos</h3>
                <p>Kode Pos: ${village.postal_code}</p>
                <p>Wilayah: ${village.name}</p>
            </div>
        `;
    } else {
        document.getElementById('postalInfo').innerHTML = `
            <div class="postal-result error">
                Wilayah ini tidak memiliki kode pos
            </div>
        `;
    }
}

// Event listeners for province dropdowns
document.addEventListener("DOMContentLoaded", () => {
    loadData('provinces.json', 'provinsi', 'kabupaten');

    document.getElementById('provinsi').addEventListener('change', async function () {
        const id = this.value;
        if (id) {
            await loadData(`provinces/${id}/regencies.json`, 'kabupaten', 'kecamatan');
        }
    });

    document.getElementById('kabupaten').addEventListener('change', async function () {
        const id = this.value;
        if (id) {
            await loadData(`regencies/${id}/districts.json`, 'kecamatan', 'kelurahan');
        }
    });

    document.getElementById('kecamatan').addEventListener('change', async function () {
        const id = this.value;
        if (id) {
            await loadData(`districts/${id}/villages.json`, 'kelurahan');
        }
    });

    document.getElementById('kelurahan').addEventListener('change', async function () {
        const id = this.value;
        if (id) {
            const selectedOption = this.options[this.selectedIndex];
            const village = {
                id: id,
                name: selectedOption.textContent.split(' (')[0], // Menghapus kode pos dari teks jika ada
                postal_code: selectedOption.textContent.match(/\((\d+)\)/)?.[1] // Mengekstrak kode pos
            };
            showVillagePostalCode(village);
        }
    });
});
