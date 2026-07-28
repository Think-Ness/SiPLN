<?php
$html = file_get_contents('tmp_modal.html');

// Rename the modal ID to avoid conflicts if needed, but it's okay to keep santriModal 
$html = str_replace('id="santriModal"', 'id="santriModalInaktif"', $html);

// Change the title
$html = preg_replace('/<h5 class="modal-title.*?id="modalTitle".*?<\/h5>/', '<h5 class="modal-title fw-bold text-dark" id="modalTitle"><i class="bi bi-person-lines-fill me-2"></i>Detail Santri Inaktif</h5>', $html);

// Remove the Simpan button
$html = preg_replace('/<button type="button" class="btn btn-primary.*?id="btnSimpan".*?<\/button>/', '', $html);

// Remove fotoActions
$html = preg_replace('/<div class="d-none mt-2 justify-content-center gap-2" id="fotoActions">.*?<\/div>/s', '', $html);

// Remove btnTambahBarang and the column for actions in barang table
$html = preg_replace('/<button type="button" class="btn btn-sm btn-outline-primary.*?id="btnTambahBarang".*?<\/button>/', '', $html);
$html = preg_replace('/<th class="text-center" style="width: 80px;">Aksi<\/th>/', '', $html);

// Remove Add/Upload buttons for Paspor and ITAS and Berkas
$html = preg_replace('/<button type="button" class="btn btn-sm btn-outline-primary.*?<i class="bi bi-upload"><\/i><\/button>/', '', $html);
$html = str_replace('<th class="text-center" style="width: 100px;">Aksi</th>', '', $html);
$html = str_replace('<th class="text-center" style="width: 120px;">Aksi</th>', '', $html);

// Replace ALL inputs and selects with disabled readonly versions
$html = preg_replace('/<input (.*?)>/', '<input $1 readonly disabled>', $html);
$html = preg_replace('/<select (.*?)>/', '<select $1 disabled>', $html);

// Replace textarea with readonly disabled
$html = preg_replace('/<textarea (.*?)>/', '<textarea $1 readonly disabled>', $html);

// Save it to a clean file
file_put_contents('tmp_modal_clean.html', $html);

// Now generate the JS script for lihatSantri()
$js = <<<'JS'
function lihatSantri(kds) {
    const tbodyBarang = document.getElementById('tbodyBarang');
    const listRPaspor = document.getElementById('listRPaspor');
    const listRITAS = document.getElementById('listRITAS');
    const listBerkas = document.getElementById('listBerkas');
    
    // reset
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('imgIcon').style.display = 'block';
    
    ['stambuk','nama','kelas','rayon','pondok','negara','kewarganegaraan','jenis_kelamin','kepengurusan',
     'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
     'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
     'no_itas','exp_itas','level_itas','nik','no_ic','no_paspor_lama','keberadaan_paspor','ukuran_baju'].forEach(id => {
         const el = document.getElementById('f_' + id);
         if(el) el.value = '';
     });

    fetch('/api/santri/' + kds)
        .then(r => r.json())
        .then(data => {
            const s = data.santri || {};
            const p = data.paspor || {};
            const i = data.itas || {};
            const pl = data.paspor_lama || {};

            const set = (id, val) => { const el = document.getElementById('f_' + id); if(el) el.value = val || ''; };
            
            set('stambuk', s.stambuk); set('nama', s.nama); set('kelas', s.kelas); 
            set('rayon', s.rayon); set('pondok', s.pondok); set('negara', s.negara); 
            set('kewarganegaraan', s.kewarganegaraan); set('jenis_kelamin', s.jenis_kelamin);
            set('kepengurusan', s.kepengurusan); set('tempat_lahir', s.tempat_lahir);
            set('tanggal_lahir', s.tanggal_lahir); set('nama_ayah', s.nama_ayah);
            set('nama_ibu', s.nama_ibu); set('no_ayah', s.no_ayah); set('no_ibu', s.no_ibu);
            set('no_hp_alternatif', s.no_hp_alternatif); set('alamat', s.alamat);
            set('nik', s.no_sktt); set('no_ic', s.no_ic);
            set('keberadaan_paspor', s.keberadaan_paspor); set('ukuran_baju', s.ukuran_baju);

            if (p) { 
                set('no_paspor', p.no_paspor); set('tempat_paspor', p.tempat_keluaran); 
                set('tgl_paspor', p.tgl_keluaran); set('exp_paspor', p.exp_paspor); 
                if (p.path_file) {
                    const vp = document.getElementById('view_file_paspor');
                    if (vp) { vp.href = '/api/santri/doc/paspor/' + p.id; vp.classList.remove('d-none'); }
                }
            }
            if (pl) { set('no_paspor_lama', pl.no_paspor); }
            if (i) { 
                set('no_itas', i.no_itas); set('exp_itas', i.exp_itas); set('level_itas', i.level_itas); 
                if (i.path_file) {
                    const vi = document.getElementById('view_file_itas');
                    if(vi) { vi.href = '/api/santri/doc/itas/' + i.id; vi.classList.remove('d-none'); }
                }
            }

            if (tbodyBarang) {
                tbodyBarang.innerHTML = (data.barang || []).map((b, idx) => `
                    <tr>
                        <td class="text-center">${idx + 1}</td>
                        <td>${b.nama_barang}</td>
                        <td class="text-center">${b.jumlah_barang}</td>
                    </tr>
                `).join('') || '<tr><td colspan="3" class="text-center text-muted">Belum ada barang bawaan</td></tr>';
            }

            if (listRPaspor) {
                listRPaspor.innerHTML = (data.r_paspor || []).map(rp => `
                    <tr>
                        <td>${rp.no_paspor}</td>
                        <td>${rp.exp_paspor}</td>
                        <td>${rp.path_file ? `<a href="/api/santri/doc/paspor/${rp.id}" target="_blank" title="Lihat Dok"><i class="bi bi-eye"></i></a>` : '-'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="3" class="text-center text-muted">Kosong</td></tr>';
            }

            if (listRITAS) {
                listRITAS.innerHTML = (data.r_itas || []).map(ri => `
                    <tr>
                        <td>${ri.no_itas}</td>
                        <td>${ri.level_itas || '-'}</td>
                        <td>${ri.exp_itas}</td>
                        <td>${ri.path_file ? `<a href="/api/santri/doc/itas/${ri.id}" target="_blank" title="Lihat Dok"><i class="bi bi-eye"></i></a>` : '-'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="4" class="text-center text-muted">Kosong</td></tr>';
            }

            if (listBerkas) {
                listBerkas.innerHTML = (data.berkas || []).map(b => `
                    <tr>
                        <td><div class="text-truncate" style="max-width: 200px;" title="${b.nama}">${b.nama}</div></td>
                        <td class="text-center">
                            <a href="${b.url}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1" title="Lihat"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="2" class="text-center text-muted">Belum ada berkas</td></tr>';
            }

            if (s.path_foto) {
                const imgPreview = document.getElementById('imgPreview');
                if(imgPreview) {
                    imgPreview.src = '/santri/' + kds + '/photo?v=' + new Date().getTime();
                    imgPreview.style.display = 'block';
                    document.getElementById('imgIcon').style.display = 'none';
                }
            }
            
            const myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('santriModalInaktif'));
            myModal.show();
        });
}
JS;

file_put_contents('tmp_js_clean.js', $js);
echo "Cleaned modal and JS ready.";
