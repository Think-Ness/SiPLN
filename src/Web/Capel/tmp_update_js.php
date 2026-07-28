<?php
$content = file_get_contents('template.php');

$oldJS = <<<'JS'
            if (status === 'Pending') {
                html += `<tr>
                    <td class="bg-white" style="position: sticky; left: 0; z-index: 1;"><input type="checkbox" class="form-check-input chk-${status} row-cb" value="${d.id}" data-nama="${d.nama_lengkap}"></td>
                    <td class="text-nowrap">${d.timestamp}</td>
                    <td class="fw-bold bg-white text-nowrap" style="position: sticky; left: 40px; z-index: 1;">${d.nama_lengkap}</td>
                    <td class="text-nowrap">${kwn}</td>
                    <td class="text-nowrap">${tmptLahir}</td>
                    <td class="text-nowrap">${d.tanggal_lahir || '-'}</td>
                    <td class="text-nowrap">${ayah}</td>
                    <td class="text-nowrap">${ibu}</td>
                    <td class="text-nowrap">${noHp}</td>
                    <td><div style="min-width: 200px;">${alamat}</div></td>
                    <td class="text-nowrap">${noPaspor}</td>
                    <td class="text-nowrap">${tglPaspor}</td>
                    <td class="text-nowrap">${expPaspor}</td>
                    <td class="text-nowrap">${tmptPaspor}</td>
                    <td class="text-nowrap">${email}</td>
                    <td class="text-nowrap sticky-right">
                        ${dupBadge}<br>
                        <button class="btn btn-sm btn-success rounded-pill px-3 py-1 mt-1" onclick="bukaModalTerima(${d.id}, '${d.nama_lengkap}')">Terima</button>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 mt-1 ms-1" onclick="tolak(${d.id})">Tolak</button>
                    </td>
                </tr>`;
            } else {
                html += `<tr>
                    <td class="bg-white" style="position: sticky; left: 0; z-index: 1;"><input type="checkbox" class="form-check-input chk-${status} row-cb" value="${d.id}" data-nama="${d.nama_lengkap}"></td>
                    <td class="text-nowrap">${d.timestamp}</td>
                    <td class="fw-bold bg-white text-nowrap" style="position: sticky; left: 40px; z-index: 1;">${d.nama_lengkap}</td>
                    <td class="text-nowrap">${kwn}</td>
                    <td class="text-nowrap">${tmptLahir}</td>
                    <td class="text-nowrap">${d.tanggal_lahir || '-'}</td>
                    <td class="text-nowrap">${ayah}</td>
                    <td class="text-nowrap">${ibu}</td>
                    <td class="text-nowrap">${noHp}</td>
                    <td><div style="min-width: 200px;">${alamat}</div></td>
                    <td class="text-nowrap">${noPaspor}</td>
                    <td class="text-nowrap">${tglPaspor}</td>
                    <td class="text-nowrap">${expPaspor}</td>
                    <td class="text-nowrap">${tmptPaspor}</td>
                    <td class="text-nowrap">${email}</td>
                    <td class="text-nowrap sticky-right"><span class="badge bg-secondary">${d.status_approval}</span></td>
                </tr>`;
            }
JS;

$newJS = <<<'JS'
            if (status === 'Pending') {
                html += `<tr>
                    <td class="bg-white" style="position: sticky; left: 0; z-index: 1;"><input type="checkbox" class="form-check-input chk-${status} row-cb" value="${d.id}" data-nama="${d.nama_lengkap}"></td>
                    <td class="text-nowrap">${d.timestamp}</td>
                    <td class="fw-bold bg-white text-nowrap" style="position: sticky; left: 40px; z-index: 1;">${d.nama_lengkap}</td>
                    <td class="text-nowrap">${kwn}</td>
                    <td class="text-nowrap">${tmptLahir},<br>${d.tanggal_lahir || '-'}</td>
                    <td class="text-nowrap">A: ${ayah}<br>I: ${ibu}</td>
                    <td class="text-nowrap"><i class="bi bi-whatsapp"></i> ${noHp}<br><i class="bi bi-envelope"></i> ${email}</td>
                    <td class="text-nowrap fw-medium">${noPaspor}<br><small class="text-muted text-nowrap">Exp: ${expPaspor}</small></td>
                    <td><div style="min-width: 150px; max-height: 3.5rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="${alamat}">${alamat}</div></td>
                    <td class="text-nowrap sticky-right bg-white">
                        ${dupBadge}<br>
                        <button class="btn btn-sm btn-success rounded-pill px-3 py-1 mt-1" onclick="bukaModalTerima(${d.id}, '${d.nama_lengkap}')">Terima</button>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 mt-1 ms-1" onclick="tolak(${d.id})">Tolak</button>
                    </td>
                </tr>`;
            } else {
                html += `<tr>
                    <td class="bg-white" style="position: sticky; left: 0; z-index: 1;"><input type="checkbox" class="form-check-input chk-${status} row-cb" value="${d.id}" data-nama="${d.nama_lengkap}"></td>
                    <td class="text-nowrap">${d.timestamp}</td>
                    <td class="fw-bold bg-white text-nowrap" style="position: sticky; left: 40px; z-index: 1;">${d.nama_lengkap}</td>
                    <td class="text-nowrap">${kwn}</td>
                    <td class="text-nowrap">${tmptLahir},<br>${d.tanggal_lahir || '-'}</td>
                    <td class="text-nowrap">A: ${ayah}<br>I: ${ibu}</td>
                    <td class="text-nowrap"><i class="bi bi-whatsapp"></i> ${noHp}<br><i class="bi bi-envelope"></i> ${email}</td>
                    <td class="text-nowrap fw-medium">${noPaspor}<br><small class="text-muted text-nowrap">Exp: ${expPaspor}</small></td>
                    <td><div style="min-width: 150px; max-height: 3.5rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="${alamat}">${alamat}</div></td>
                    <td class="text-nowrap sticky-right bg-white">
                        <span class="badge bg-secondary mb-1">${d.status_approval}</span><br>
                        ${d.final_status_santri ? `<span class="badge bg-info text-dark">${d.final_status_santri}</span>` : ''}
                    </td>
                </tr>`;
            }
JS;

// Use str_replace
$content = str_replace($oldJS, $newJS, $content);

file_put_contents('template.php', $content);
echo "JS updated.";
