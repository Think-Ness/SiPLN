<?php
$content = file_get_contents('template.php');

$statuses = [
    ['Pending', 'Aksi'],
    ['Approved', 'Status'],
    ['Rejected', 'Status']
];

foreach ($statuses as $s) {
    $status = $s[0];
    $aksi = $s[1];
    
    // The exact search pattern for the header of each tab
    $pattern = '/<thead class="table-light">.*?<th class="bg-light" style="width: 40px; position: sticky; left: 0; z-index: 2;"><input type="checkbox" class="form-check-input" id="chkAll' . $status . '" onchange="toggleAll\(\'' . $status . '\', this\)"><\/th>.*?<\/thead>/s';
    
    $replacement = '<thead class="table-light">
                            <tr>
                                <th class="bg-light" style="width: 40px; position: sticky; left: 0; z-index: 2;"><input type="checkbox" class="form-check-input" id="chkAll' . $status . '" onchange="toggleAll(\'' . $status . '\', this)"></th>
                                <th class="bg-light text-nowrap">Tanggal Submit</th>
                                <th class="bg-light text-nowrap" style="position: sticky; left: 40px; z-index: 2;">Nama Lengkap</th>
                                <th class="bg-light text-nowrap">Kewarganegaraan</th>
                                <th class="bg-light text-nowrap">Tempat, Tgl Lahir</th>
                                <th class="bg-light text-nowrap">Orang Tua</th>
                                <th class="bg-light text-nowrap">Kontak</th>
                                <th class="bg-light text-nowrap">Info Paspor</th>
                                <th class="bg-light text-nowrap">Alamat</th>
                                <th class="bg-light text-nowrap sticky-right">' . $aksi . '</th>
                            </tr>
                            <tr class="search-row bg-light">
                                <th class="bg-light" style="position: sticky; left: 0; z-index: 2;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="1" placeholder="Cari..."></th>
                                <th class="bg-light" style="position: sticky; left: 40px; z-index: 2;"><input type="text" class="form-control form-control-sm column-search" data-col="2" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="3" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="4" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="5" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="6" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="7" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="8" placeholder="Cari..."></th>
                                <th class="bg-light sticky-right"></th>
                            </tr>
                        </thead>';
                        
    $content = preg_replace($pattern, $replacement, $content);
}

file_put_contents('template.php', $content);
echo "Headers updated.";
