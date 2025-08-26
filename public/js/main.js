/**
 * ERP Sistemi - Merkezi JavaScript Dosyası
 * Bu dosya, tüm modern frontend (AJAX) işlemlerini yönetir.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Kargoya ürün ekleme formu
    const urunEkleForm = document.getElementById('urunEkleForm');
    if (urunEkleForm) {
        urunEkleForm.addEventListener('submit', urunEkleHandler);
    }

    // Kargo durumunu güncelleme formu
    const durumGuncelleForm = document.getElementById('durumGuncelleForm');
    if (durumGuncelleForm) {
        durumGuncelleForm.addEventListener('submit', durumGuncelleHandler);
    }

    // Canlı arama
    const searchInput = document.getElementById('liveSearchInput');
    const searchResults = document.getElementById('liveSearchResults');
    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(liveSearchHandler, 300));
    }

    // Arama sonuçları kutusunun dışına tıklandığında kapat
    document.addEventListener('click', function(e) {
        if (searchResults && !searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.style.display = 'none';
        }
    });

    // Kargo listeleme ve filtreleme
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) { e.preventDefault(); fetchKargolar(1); });
        const resetButton = filterForm.querySelector('button[type="reset"]');
        resetButton.addEventListener('click', function() { setTimeout(() => fetchKargolar(1), 0); });
        fetchKargolar(1);
    }

    // İşlem logları sayfası
    if (document.getElementById('logTableBody')) {
        fetchLogs(1);
    }

    // Personel Yönetimi Sayfası
    const personelModal = document.getElementById('personelModal');
    if (personelModal) {
        const modal = new bootstrap.Modal(personelModal);

        document.getElementById('yeniPersonelBtn').addEventListener('click', function() {
            resetPersonelForm();
            modal.show();
        });

        document.getElementById('personelTableBody').addEventListener('click', async function(e) {
            const editButton = e.target.closest('.edit-btn');
            if (editButton) {
                const personelId = editButton.dataset.id;
                await getPersonelDetay(personelId);
                modal.show();
            }
        });

        document.getElementById('personelForm').addEventListener('submit', kaydetPersonelHandler);
    }
});


// --- PERSONEL YÖNETİMİ FONKSİYONLARI ---

function resetPersonelForm() {
    const form = document.getElementById('personelForm');
    form.reset();
    document.getElementById('personelModalLabel').textContent = 'Yeni Personel Ekle';
    document.getElementById('personel_id').value = '';
    document.getElementById('personel_kodu').readOnly = false;
    document.getElementById('ise_giris_tarihi_group').style.display = 'block';
    // Şifre alanının required özelliğini ekle (yeni kayıtta zorunlu)
    document.querySelector('input[name="sifre"]').required = true;
}

async function getPersonelDetay(id) {
    resetPersonelForm();
    try {
        const response = await fetch(`/erp/api.php?action=get_personel_detay&id=${id}`);
        const data = await response.json();

        if (data.status === 'success') {
            const form = document.getElementById('personelForm');
            const p = data.personel;
            document.getElementById('personelModalLabel').textContent = 'Personel Düzenle: ' + p.ad_soyad;
            document.getElementById('personel_id').value = p.id;
            form.ad_soyad.value = p.ad_soyad;
            form.personel_kodu.value = p.personel_kodu;
            form.kullanici_adi.value = p.kullanici_adi;
            form.email.value = p.email;
            form.magaza_id.value = p.magaza_id;
            form.pozisyon.value = p.pozisyon;
            form.rol.value = p.rol;
            form.aktif_mi.value = p.aktif_mi;
            
            document.getElementById('personel_kodu').readOnly = true;
            document.getElementById('ise_giris_tarihi_group').style.display = 'none';
            // Şifre alanının required özelliğini kaldır (düzenlemede zorunlu değil)
            document.querySelector('input[name="sifre"]').required = false;
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Personel detayı alınamadı:', error);
    }
}

async function kaydetPersonelHandler(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'personel_kaydet');
    const personelId = document.getElementById('personel_id').value;

    try {
        const response = await fetch('/erp/api.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.status === 'success') {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('personelModal'));
            modalInstance.hide();
            
            // Tabloyu sayfa yenilemeden dinamik olarak güncelle
            updatePersonelTable(result.personel, personelId);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Personel kaydedilemedi:', error);
    }
}

function updatePersonelTable(personel, originalId) {
    const tableBody = document.getElementById('personelTableBody');
    const newRowHTML = `
        <td>${personel.ad_soyad}</td>
        <td>${personel.kullanici_adi}</td>
        <td>${personel.magaza_adi || 'N/A'}</td>
        <td><span class="badge bg-${personel.rol == 'Admin' ? 'danger' : 'secondary'}">${personel.rol}</span></td>
        <td><span class="badge bg-${personel.aktif_mi == 1 ? 'success' : 'warning'}">${personel.aktif_mi == 1 ? 'Aktif' : 'Pasif'}</span></td>
        <td><button class="btn btn-sm btn-info edit-btn" data-id="${personel.id}"><i class="bi bi-pencil-square"></i></button></td>
    `;

    if (originalId) { // Güncelleme
        const rowToUpdate = tableBody.querySelector(`tr[data-personel-id="${originalId}"]`);
        if (rowToUpdate) {
            rowToUpdate.innerHTML = newRowHTML;
        }
    } else { // Ekleme
        const newRow = tableBody.insertRow(0); // En üste ekle
        newRow.setAttribute('data-personel-id', personel.id);
        newRow.innerHTML = newRowHTML;
    }
}


// --- DİĞER TÜM FONKSİYONLAR ---

async function urunEkleHandler(e) {
    e.preventDefault(); 
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const bildirimAlani = document.getElementById('bildirimAlani');
    const urunListesiBody = document.getElementById('urunListesiBody');
    const urunSayac = document.getElementById('urunSayac');
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Ekleniyor...';
    const formData = new FormData(form);
    formData.append('action', 'urun_ekle');
    try {
        const response = await fetch('/erp/api.php', { method: 'POST', body: formData });
        const result = await response.json();
        bildirimAlani.innerHTML = `<div class="alert alert-${result.status === 'success' ? 'success' : 'danger'}">${result.message}</div>`;
        if (result.status === 'success') {
            form.reset();
            const noProductRow = urunListesiBody.querySelector('.no-product-row');
            if (noProductRow) noProductRow.remove();
            const newRow = urunListesiBody.insertRow();
            newRow.innerHTML = `<td>${result.urun.imei}</td><td>${result.urun.model}</td>`;
            urunSayac.textContent = parseInt(urunSayac.textContent) + 1;
        }
    } catch (error) {
        console.error('Ürün ekleme hatası:', error);
        bildirimAlani.innerHTML = `<div class="alert alert-danger">Bir ağ hatası oluştu.</div>`;
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Ekle';
    }
}

async function durumGuncelleHandler(e) {
    e.preventDefault();
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const bildirimAlani = document.getElementById('detayBildirimAlani');
    const durumBadge = document.getElementById('mevcutDurumBadge');
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Güncelleniyor...';
    const formData = new FormData(form);
    formData.append('action', 'durum_guncelle');
    try {
        const response = await fetch('/erp/api.php', { method: 'POST', body: formData });
        const result = await response.json();
        bildirimAlani.innerHTML = `<div class="alert alert-${result.status === 'success' ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
            ${result.message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        if (result.status === 'success') {
            durumBadge.textContent = result.yeni_durum;
        }
    } catch (error) {
        console.error('Durum güncelleme hatası:', error);
        bildirimAlani.innerHTML = `<div class="alert alert-danger">Bir ağ hatası oluştu.</div>`;
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Güncelle';
    }
}

async function liveSearchHandler() {
    const searchInput = document.getElementById('liveSearchInput');
    const searchResults = document.getElementById('liveSearchResults');
    const term = searchInput.value;
    if (term.length < 3) {
        searchResults.style.display = 'none';
        return;
    }
    try {
        const response = await fetch(`/erp/api.php?action=live_search&term=${encodeURIComponent(term)}`);
        const data = await response.json();
        searchResults.innerHTML = '';
        if (data.status === 'success' && data.results.length > 0) {
            const list = document.createElement('ul');
            list.className = 'list-group';
            data.results.forEach(item => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item list-group-item-action';
                listItem.innerHTML = `<a href="${item.url}" class="text-decoration-none text-dark d-block">
                                        <div class="fw-bold">${item.text}</div>
                                        <small class="text-muted">${item.subtext}</small>
                                      </a>`;
                list.appendChild(listItem);
            });
            searchResults.appendChild(list);
            searchResults.style.display = 'block';
        } else {
            searchResults.innerHTML = '<div class="p-2 text-muted">Sonuç bulunamadı.</div>';
            searchResults.style.display = 'block';
        }
    } catch (error) {
        console.error('Arama hatası:', error);
        searchResults.style.display = 'none';
    }
}

async function fetchKargolar(page = 1) {
    const tableBody = document.getElementById('kargoTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const filterForm = document.getElementById('filterForm');
    tableBody.innerHTML = '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yükleniyor...</span></div></td></tr>';
    const params = new URLSearchParams(new FormData(filterForm));
    params.set('action', 'get_kargolar');
    params.set('page', page);
    try {
        const response = await fetch(`/erp/api.php?${params.toString()}`);
        const data = await response.json();
        tableBody.innerHTML = '';
        if (data.status === 'success' && data.kargolar.length > 0) {
            data.kargolar.forEach(kargo => {
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td>${kargo.takip_kodu}</td>
                    <td>${kargo.alici}</td>
                    <td>${kargo.magaza_adi}</td>
                    <td>${kargo.personel_adi}</td>
                    <td><span class="badge bg-info">${kargo.kargo_durumu}</span></td>
                    <td>${new Date(kargo.gonderim_tarihi).toLocaleString('tr-TR')}</td>
                    <td>
                        <a href="/erp/templates/kargo_detay.php?id=${kargo.id}" class="btn btn-sm btn-info" title="Detayları Gör"><i class="bi bi-eye"></i></a>
                    </td>
                `;
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Filtre kriterlerine uygun kargo bulunamadı.</td></tr>';
        }
        renderPagination(data.pagination);
    } catch (error) {
        console.error('Kargo listeleme hatası:', error);
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Veriler yüklenirken bir hata oluştu.</td></tr>';
    }
}

function renderPagination(paginationData) {
    const { total_pages, current_page } = paginationData;
    const paginationContainer = document.getElementById('paginationContainer');
    paginationContainer.innerHTML = '';
    if (total_pages <= 1) return;
    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';
    for (let i = 1; i <= total_pages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === current_page ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', (e) => {
            e.preventDefault();
            fetchKargolar(i);
        });
        li.appendChild(a);
        ul.appendChild(li);
    }
    nav.appendChild(ul);
    paginationContainer.appendChild(nav);
}

async function fetchLogs(page = 1) {
    const tableBody = document.getElementById('logTableBody');
    const paginationContainer = document.getElementById('logPaginationContainer');
    tableBody.innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yükleniyor...</span></div></td></tr>';
    try {
        const response = await fetch(`/erp/api.php?action=get_logs&page=${page}`);
        const data = await response.json();
        tableBody.innerHTML = '';
        if (data.status === 'success' && data.logs.length > 0) {
            data.logs.forEach(log => {
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td>${new Date(log.tarih).toLocaleString('tr-TR')}</td>
                    <td>${log.personel_adi}</td>
                    <td><span class="badge bg-secondary">${log.islem_tipi}</span></td>
                    <td>${log.aciklama}</td>
                    <td>${log.ip_adresi}</td>
                `;
            });
        } else {
            const message = data.message || 'Gösterilecek işlem kaydı bulunamadı.';
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center">${message}</td></tr>`;
        }
        renderLogPagination(data.pagination);
    } catch (error) {
        console.error('Log listeleme hatası:', error);
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Loglar yüklenirken bir hata oluştu.</td></tr>';
    }
}

function renderLogPagination(paginationData) {
    if (!paginationData) return;
    const { total_pages, current_page } = paginationData;
    const paginationContainer = document.getElementById('logPaginationContainer');
    paginationContainer.innerHTML = '';
    if (total_pages <= 1) return;
    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';
    for (let i = 1; i <= total_pages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === current_page ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', (e) => {
            e.preventDefault();
            fetchLogs(i);
        });
        li.appendChild(a);
        ul.appendChild(li);
    }
    nav.appendChild(ul);
    paginationContainer.appendChild(nav);
}

function debounce(func, delay) {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}
// YENİ EKLENEN KISIM: Mağaza Yönetimi Sayfası
    const magazaModal = document.getElementById('magazaModal');
    if (magazaModal) {
        const modal = new bootstrap.Modal(magazaModal);

        // "Yeni Mağaza Ekle" butonu
        document.getElementById('yeniMagazaBtn').addEventListener('click', function() {
            resetMagazaForm();
            modal.show();
        });

        // "Düzenle" butonları (Event Delegation)
        document.getElementById('magazaTableBody').addEventListener('click', async function(e) {
            const editButton = e.target.closest('.edit-btn-magaza');
            if (editButton) {
                const magazaId = editButton.dataset.id;
                await getMagazaDetay(magazaId);
                modal.show();
            }
        });

        // Modal'daki kaydetme formu
        document.getElementById('magazaForm').addEventListener('submit', kaydetMagazaHandler);
    }


/** YENİ FONKSİYONLAR **/

function resetMagazaForm() {
    const form = document.getElementById('magazaForm');
    form.reset();
    document.getElementById('magazaModalLabel').textContent = 'Yeni Mağaza Ekle';
    document.getElementById('magaza_id').value = '';
}

async function getMagazaDetay(id) {
    resetMagazaForm();
    try {
        const response = await fetch(`/erp/api.php?action=get_magaza_detay&id=${id}`);
        const data = await response.json();

        if (data.status === 'success') {
            const form = document.getElementById('magazaForm');
            const m = data.magaza;
            document.getElementById('magazaModalLabel').textContent = 'Mağaza Düzenle: ' + m.magaza_adi;
            document.getElementById('magaza_id').value = m.id;
            form.magaza_adi.value = m.magaza_adi;
            form.sehir.value = m.sehir;
            form.adres.value = m.adres;
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Mağaza detayı alınamadı:', error);
    }
}

async function kaydetMagazaHandler(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'magaza_kaydet');
    const magazaId = document.getElementById('magaza_id').value;

    try {
        const response = await fetch('/erp/api.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.status === 'success') {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('magazaModal'));
            modalInstance.hide();
            updateMagazaTable(result.magaza, magazaId);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Mağaza kaydedilemedi:', error);
    }
}

function updateMagazaTable(magaza, originalId) {
    const tableBody = document.getElementById('magazaTableBody');
    const newRowHTML = `
        <td>${magaza.magaza_adi}</td>
        <td>${magaza.sehir}</td>
        <td><button class="btn btn-sm btn-info edit-btn-magaza" data-id="${magaza.id}"><i class="bi bi-pencil-square"></i></button></td>
    `;

    if (originalId) { // Güncelleme
        const rowToUpdate = tableBody.querySelector(`tr[data-magaza-id="${originalId}"]`);
        if (rowToUpdate) {
            rowToUpdate.innerHTML = newRowHTML;
        }
    } else { // Ekleme
        const newRow = tableBody.insertRow(0); // En üste ekle
        newRow.setAttribute('data-magaza-id', magaza.id);
        newRow.innerHTML = newRowHTML;
    }
}
