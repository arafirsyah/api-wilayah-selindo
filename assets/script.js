async function loadData(url, targetId, childId = null) {
    const select = document.getElementById(targetId);
    const childSelect = childId ? document.getElementById(childId) : null;

    // Reset current and child dropdown
    select.innerHTML = '<option value="">Pilih</option>';
    if (childSelect) childSelect.innerHTML = '<option value="">Pilih</option>';

    try {
        // Construct the full path from public directory
        const fullPath = `/api-wilayah-selindo/static/api/${url}`;
        const res = await fetch(fullPath);
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        const data = await res.json();

        if (!Array.isArray(data)) {
            console.error("Gagal load:", data);
            return;
        }

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
        console.error("Error AJAX:", e);
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
            
            // Get village details using the correct path
            const villageRes = await fetch(`/api-wilayah-selindo/static/api/districts/${districtId}/villages.json`);
            if (!villageRes.ok) {
                throw new Error(`HTTP error! status: ${villageRes.status}`);
            }
            const villages = await villageRes.json();
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
            const districts = await districtRes.json();
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
            const regencies = await regencyRes.json();
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
            const res = await fetch(`/api-wilayah-selindo/static/api/districts/${id.slice(0, 6)}/villages.json`);
            if (!res.ok) {
                console.error('Error fetching data:', res.statusText);
                return;
            }
            const villages = await res.json();
            const village = villages.find(v => v.id === id);
            if (village) {
                showVillagePostalCode(village);
            }
        }
    });
});
