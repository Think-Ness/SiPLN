<?php
declare(strict_types=1);

namespace App\Shared;

use Smalot\PdfParser\Parser;
use Yiisoft\Db\Connection\ConnectionInterface;

final class ItasParserEngine
{
    public const SETTING_KEY = 'itas_parser_config';

    /**
     * Profil bawaan sistem (Default Templates)
     */
    public static function getDefaultConfig(): array
    {
        return [
            'mode' => 'auto', // 'auto' | 'profile_id'
            'active_profile_id' => 'auto',
            'fallback_passport_match' => true, // Jika pencocokan nama gagal, coba cocokkan nomor paspor ke database
            'profiles' => [
                [
                    'id' => 'itas_kemenkumham_2021',
                    'name' => 'Format Kemenkumham Klasik / E-ITAS (2021 - 2023)',
                    'is_system' => true,
                    'enabled' => true,
                    'description' => 'Format dengan kop Kementerian Hukum dan HAM, nama berada di tabel dengan label "Full Name :".',
                    'identifier_keywords' => [
                        'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA',
                        'IZIN TINGGAL TERBATAS ELEKTRONIK',
                        'ELECTRONIC LIMITED STAY PERMIT',
                        'Full Name'
                    ],
                    'name_mode' => 'regex',
                    'name_regex' => '/(?:Full\s*Name|Nama\s*Lengkap|Name)\s*:\s*([^\n\r]+)/i',
                    'permit_regex' => '/(?:Permit\s+Number|PERMIT\s+NUMBER|NIORA)\s*:\s*([A-Z0-9\-]+)/i',
                    'expiry_regex' => '/(?:Stay\/Multiple\s+Entries\s+Permit\s+Expiry|Stay\s+Permit\s+Expiry|Permit\s+Expiry|Expiry\s+Date)\s*:\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
                    'passport_regex' => '/(?:TRAVEL\s+DOC(?:UMENT)?(?:\s+NUMBER|\s+NO\.?)?|PASSPORT(?:\s+NUMBER|\s+NO\.?)?|NO(?:MOR)?\.?\s*(?:DOKUMEN\s+PERJALANAN|PASPOR)|DOKUMEN\s+PERJALANAN|PASPOR)(?:[\/\s]+(?:NOMOR|NUMBER|DOKUMEN|PERJALANAN|PASPOR|PASSPORT|NO\.?))*\s*[:=]\s*[\r\n]*\s*[:=]?\s*([A-Z0-9\-]+)/i',
                ],
                [
                    'id' => 'itas_modern_2024',
                    'name' => 'Format Ditjen Imigrasi Modern (2024 - Sekarang)',
                    'is_system' => true,
                    'enabled' => true,
                    'description' => 'Format terkini dengan kop Ditjen Imigrasi, foto di kiri atas, nama santri di baris judul.',
                    'identifier_keywords' => [
                        'TEMPORARY STAY PERMIT',
                        'DIRECTORATE GENERAL OF IMMIGRATION'
                    ],
                    'name_mode' => 'top_line', // 'top_line' | 'regex'
                    'name_regex' => '/(?:Full\s*Name|Nama\s*Lengkap)\s*:\s*([^\n\r]+)/i',
                    'permit_regex' => '/(?:PERMIT\s+NUMBER|Permit\s+Number)\s*:\s*([A-Z0-9\-]+)/i',
                    'expiry_regex' => '/(?:STAY\s+PERMIT\s+EXPIRY|Stay\s+Permit\s+Expiry)\s*:\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
                    'passport_regex' => '/(?:TRAVEL\s+DOC(?:UMENT)?(?:\s+NUMBER|\s+NO\.?)?|PASSPORT(?:\s+NUMBER|\s+NO\.?)?|NO(?:MOR)?\.?\s*(?:DOKUMEN\s+PERJALANAN|PASPOR)|DOKUMEN\s+PERJALANAN|PASPOR)(?:[\/\s]+(?:NOMOR|NUMBER|DOKUMEN|PERJALANAN|PASPOR|PASSPORT|NO\.?))*\s*[:=]\s*[\r\n]*\s*[:=]?\s*([A-Z0-9\-]+)/i',
                ]
            ]
        ];
    }

    /**
     * Ambil konfigurasi saat ini dari database (dengan fallback ke default)
     */
    public static function getConfig(ConnectionInterface $db): array
    {
        try {
            $raw = $db->createCommand("SELECT setting_value FROM app_settings WHERE setting_key = :k", [
                ':k' => self::SETTING_KEY
            ])->queryScalar();

            if (!empty($raw)) {
                $saved = json_decode((string)$raw, true);
                if (is_array($saved) && isset($saved['profiles'])) {
                    return $saved;
                }
            }
        } catch (\Throwable $e) {}

        return self::getDefaultConfig();
    }

    /**
     * Simpan konfigurasi ke database
     */
    public static function saveConfig(ConnectionInterface $db, array $config): bool
    {
        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $sql = "
            INSERT INTO app_settings (setting_key, setting_value) 
            VALUES (:k, :v)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ";
        $db->createCommand($sql, [
            ':k' => self::SETTING_KEY,
            ':v' => $json
        ])->execute();

        return true;
    }

    /**
     * Cek apakah teks yang terekstrak sebagai nama sebenarnya adalah header instansi pemerintah
     */
    public static function isInvalidPersonName(?string $name): bool
    {
        if (empty($name)) return true;
        $nameUpper = strtoupper(trim($name));
        if (strlen($nameUpper) < 3) return true;

        $blacklist = [
            'REPUBLIK INDONESIA',
            'REPUBLIC OF INDONESIA',
            'KEMENTERIAN',
            'MINISTRY',
            'HUKUM DAN HAK ASASI',
            'DIREKTORAT JENDERAL',
            'DIRECTORATE GENERAL',
            'IMIGRASI',
            'IMMIGRATION',
            'IZIN TINGGAL',
            'STAY PERMIT',
            'ELEKTRONIK',
            'ELECTRONIC',
            'TEMPORARY',
            'SURAT PERJALANAN',
            'PASSPORT',
            'PASPOR',
            'PERMIT NUMBER',
            'EXPIRY DATE',
            'NATIONALITY',
            'GENDER',
            'DATE OF BIRTH',
            'PLACE OF BIRTH'
        ];

        foreach ($blacklist as $b) {
            if (strpos($nameUpper, $b) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bersihkan string nama dari karakter ilegal
     */
    public static function cleanName(?string $nama): string
    {
        if (empty($nama)) return '';
        $illegal = ['<','>',':','"','/','\\','|','?','*'];
        $clean = trim(str_replace($illegal, '', $nama));
        // Bersihkan whitespace berlebih
        return preg_replace('/\s+/', ' ', $clean);
    }

    /**
     * Normalisasi format tanggal ke YYYY-MM-DD
     */
    public static function normalizeDate(?string $rawDate): ?string
    {
        if (empty($rawDate)) return null;
        $rawDate = str_replace(['.', '-'], '/', trim($rawDate));
        $parts = explode('/', $rawDate);
        if (count($parts) === 3) {
            // Asumsi DD/MM/YYYY
            $d = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $m = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            $y = $parts[2];
            if (strlen($y) === 2) $y = '20' . $y;
            if (checkdate((int)$m, (int)$d, (int)$y)) {
                return "$y-$m-$d";
            }
        }
        $time = strtotime($rawDate);
        return $time ? date('Y-m-d', $time) : null;
    }

    /**
     * Parse file PDF ITAS berdasarkan konfigurasi dan profil aktif
     */
    public static function parsePdf(string $pdfPath, ConnectionInterface $db, ?array $forcedConfig = null): array
    {
        $result = [
            'success' => false,
            'matched_profile' => null,
            'matched_profile_name' => null,
            'raw_text' => '',
            'extracted_name' => '',
            'no_itas' => '',
            'exp_itas' => '',
            'no_paspor' => '',
            'matched_santri' => null,
            'match_method' => null, // 'exact_name' | 'like_name' | 'word_by_word' | 'passport_number'
            'logs' => [],
            'error' => null
        ];

        if (!file_exists($pdfPath)) {
            $result['error'] = 'File PDF tidak ditemukan.';
            return $result;
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();
            if (empty($pages)) {
                $result['error'] = 'PDF kosong atau tidak memiliki halaman.';
                return $result;
            }

            $rawText = $pages[0]->getText();
            $result['raw_text'] = $rawText;

            if (empty(trim($rawText))) {
                $result['error'] = 'Tidak ada teks terdeteksi dalam PDF (kemungkinan hasil scan gambar).';
                return $result;
            }

            $config = $forcedConfig ?? self::getConfig($db);
            $profiles = $config['profiles'] ?? [];
            $mode = $config['mode'] ?? 'auto';
            $activeProfileId = $config['active_profile_id'] ?? 'auto';

            // Filter enabled profiles
            $activeProfiles = array_filter($profiles, function($p) {
                return !empty($p['enabled']);
            });

            if (empty($activeProfiles)) {
                $activeProfiles = self::getDefaultConfig()['profiles'];
            }

            $candidateProfiles = [];
            if ($mode !== 'auto' && $activeProfileId !== 'auto') {
                foreach ($activeProfiles as $p) {
                    if ($p['id'] === $activeProfileId) {
                        $candidateProfiles[] = $p;
                        break;
                    }
                }
            } else {
                $candidateProfiles = $activeProfiles;
            }

            // Evaluasi semua kandidat profil dan berikan skor kecocokan
            $evaluatedCandidates = [];

            foreach ($candidateProfiles as $prof) {
                $profId = $prof['id'] ?? 'unknown';
                $profName = $prof['name'] ?? 'Profil Tanpa Nama';

                $extractedName = '';
                $noItas = '';
                $expItas = '';
                $noPaspor = '';
                $keywordScore = 0;

                // Hitung kecocokan keyword pengenal
                $keywords = $prof['identifier_keywords'] ?? [];
                foreach ($keywords as $kw) {
                    if (!empty($kw) && stripos($rawText, trim($kw)) !== false) {
                        $keywordScore += 25;
                    }
                }

                // 1. Ekstrak No ITAS
                if (!empty($prof['permit_regex'])) {
                    if (@preg_match($prof['permit_regex'], $rawText, $m)) {
                        $noItas = trim($m[1] ?? '');
                    }
                }

                // 2. Ekstrak Expiry Date
                if (!empty($prof['expiry_regex'])) {
                    if (@preg_match($prof['expiry_regex'], $rawText, $m)) {
                        $rawExp = trim($m[1] ?? '');
                        $expItas = self::normalizeDate($rawExp) ?? '';
                    }
                }

                // 3. Ekstrak No Paspor
                $blacklistPassport = ['NOMOR', 'NUMBER', 'NO', 'DOKUMEN', 'DOCUMENT', 'PERJALANAN', 'PASPOR', 'PASSPORT', 'NAME', 'FULL', 'PERMIT', 'ITAS', 'NIORA'];
                if (!empty($prof['passport_regex'])) {
                    if (@preg_match($prof['passport_regex'], $rawText, $m)) {
                        $cand = trim($m[1] ?? '');
                        if (!in_array(strtoupper($cand), $blacklistPassport) && strlen($cand) >= 3) {
                            $noPaspor = $cand;
                        }
                    }
                }
                if (empty($noPaspor)) {
                    $defaultPassportPattern = '/(?:TRAVEL\s+DOC(?:UMENT)?(?:\s+NUMBER|\s+NO\.?)?|PASSPORT(?:\s+NUMBER|\s+NO\.?)?|NO(?:MOR)?\.?\s*(?:DOKUMEN\s+PERJALANAN|PASPOR)|DOKUMEN\s+PERJALANAN|PASPOR)(?:[\/\s]+(?:NOMOR|NUMBER|DOKUMEN|PERJALANAN|PASPOR|PASSPORT|NO\.?))*\s*[:=]\s*[\r\n]*\s*[:=]?\s*([A-Z0-9\-]+)/i';
                    if (preg_match($defaultPassportPattern, $rawText, $m)) {
                        $cand = trim($m[1] ?? '');
                        if (!in_array(strtoupper($cand), $blacklistPassport) && strlen($cand) >= 3) {
                            $noPaspor = $cand;
                        }
                    }
                }
                if (empty($noPaspor)) {
                    if (preg_match('/(?:TRAVEL\s+DOC(?:UMENT)?|PASSPORT|PASPOR|DOKUMEN\s+PERJALANAN)[^:\n\r]{0,50}[:=]\s*([A-Z0-9\-]+)/i', $rawText, $m)) {
                        $cand = trim($m[1] ?? '');
                        if (!in_array(strtoupper($cand), $blacklistPassport) && strlen($cand) >= 3) {
                            $noPaspor = $cand;
                        }
                    }
                }
                if (empty($noPaspor)) {
                    if (preg_match('/\b([A-Z][0-9]{7,9})\b/i', $rawText, $m)) {
                        $noPaspor = trim($m[1] ?? '');
                    }
                }

                // 4. Ekstrak Nama
                $nameMode = $prof['name_mode'] ?? 'top_line';
                if ($nameMode === 'regex' && !empty($prof['name_regex'])) {
                    if (@preg_match($prof['name_regex'], $rawText, $m)) {
                        $extractedName = self::cleanName($m[1] ?? '');
                    }
                } else {
                    // Mode Top Line: Ambil baris teratas yang BUKAN header institusi
                    $textClean = str_replace("\r", "", $rawText);
                    $lines = explode("\n", $textClean);
                    $lines = array_values(array_filter(array_map('trim', $lines), function($l) { return $l !== ''; }));
                    
                    foreach ($lines as $idx => $line) {
                        if (!self::isInvalidPersonName($line)) {
                            $topName = $line;
                            // Cek apakah ada nama lanjutan di baris kedua (jika nama santri panjang)
                            if (isset($lines[$idx + 1])) {
                                $nextL = $lines[$idx + 1];
                                if (!self::isInvalidPersonName($nextL) && strtoupper($nextL) === $nextL) {
                                    $topName .= " " . $nextL;
                                }
                            }
                            $extractedName = self::cleanName($topName);
                            break;
                        }
                    }
                }

                $isNameValid = !empty($extractedName) && !self::isInvalidPersonName($extractedName);

                // Pencarian Santri di Database
                $santri = null;
                $matchMethod = null;

                if ($isNameValid) {
                    // Tahap 1: Exact Match
                    $santri = $db->createCommand("SELECT kds, nama, stambuk, kepengurusan, kode FROM master_santri WHERE aktif = 1 AND LOWER(nama) = :n LIMIT 1", [
                        ':n' => strtolower($extractedName)
                    ])->queryOne();
                    if ($santri) $matchMethod = 'exact_name';

                    // Tahap 2: LIKE Match
                    if (!$santri) {
                        $santri = $db->createCommand("SELECT kds, nama, stambuk, kepengurusan, kode FROM master_santri WHERE aktif = 1 AND LOWER(nama) LIKE :n LIMIT 1", [
                            ':n' => '%' . strtolower($extractedName) . '%'
                        ])->queryOne();
                        if ($santri) $matchMethod = 'like_name';
                    }

                    // Tahap 3: Word-by-word Match
                    if (!$santri) {
                        $words = explode(' ', strtolower(preg_replace('/[^a-z ]/i', '', $extractedName)));
                        $words = array_filter($words, function($w) { return strlen(trim($w)) > 2; });
                        if (count($words) > 0) {
                            $sql = "SELECT kds, nama, stambuk, kepengurusan, kode FROM master_santri WHERE aktif = 1";
                            $params = [];
                            $i = 0;
                            foreach ($words as $w) {
                                $sql .= " AND LOWER(nama) LIKE :w$i";
                                $params[":w$i"] = '%' . trim($w) . '%';
                                $i++;
                            }
                            $sql .= " LIMIT 1";
                            $santri = $db->createCommand($sql, $params)->queryOne();
                            if ($santri) $matchMethod = 'word_by_word';
                        }
                    }
                }

                // Tahap 4: Fallback Match via Nomor Paspor
                $allowPassportFallback = $config['fallback_passport_match'] ?? true;
                if (!$santri && $allowPassportFallback && !empty($noPaspor)) {
                    $santri = $db->createCommand("
                        SELECT s.kds, s.nama, s.stambuk, s.kepengurusan, s.kode
                        FROM master_santri s
                        JOIN mtb_paspor p ON s.kds = p.kds
                        WHERE s.aktif = 1 AND LOWER(REPLACE(p.no_paspor, ' ', '')) = :np
                        LIMIT 1
                    ", [
                        ':np' => strtolower(str_replace(' ', '', $noPaspor))
                    ])->queryOne();
                    if ($santri) {
                        $matchMethod = 'passport_number';
                    }
                }

                // Kalkulasi Skor Total
                $totalScore = $keywordScore;
                if ($santri) {
                    $totalScore += ($matchMethod === 'exact_name') ? 150 : (($matchMethod === 'like_name') ? 120 : (($matchMethod === 'word_by_word') ? 100 : 80));
                }
                if ($isNameValid) {
                    $totalScore += 30;
                } else {
                    $totalScore -= 50; // Penalti jika nama kosong / nama instansi
                }
                if (!empty($noItas)) $totalScore += 20;
                if (!empty($expItas)) $totalScore += 20;
                if (!empty($noPaspor)) $totalScore += 10;

                $evaluatedCandidates[] = [
                    'profile_id' => $profId,
                    'profile_name' => $profName,
                    'score' => $totalScore,
                    'extracted_name' => $extractedName,
                    'no_itas' => $noItas,
                    'exp_itas' => $expItas,
                    'no_paspor' => $noPaspor,
                    'santri' => $santri,
                    'match_method' => $matchMethod,
                    'is_name_valid' => $isNameValid
                ];

                $result['logs'][] = "Evaluasi profil [$profName] -> Skor: $totalScore, Nama: '$extractedName', No ITAS: '$noItas', Exp: '$expItas', Paspor: '$noPaspor', Santri Cocok: " . ($santri ? "{$santri['nama']} ($matchMethod)" : 'Tidak');
            }

            // Urutkan kandidat berdasarkan skor tertinggi
            usort($evaluatedCandidates, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $best = $evaluatedCandidates[0] ?? null;

            if ($best && ($best['santri'] || $best['is_name_valid'] || !empty($best['no_itas']))) {
                $result['matched_profile'] = $best['profile_id'];
                $result['matched_profile_name'] = $best['profile_name'];
                $result['extracted_name'] = $best['extracted_name'];
                $result['no_itas'] = $best['no_itas'];
                $result['exp_itas'] = $best['exp_itas'];
                $result['no_paspor'] = $best['no_paspor'];
                $result['matched_santri'] = $best['santri'];
                $result['match_method'] = $best['match_method'];

                if ($best['santri']) {
                    $result['success'] = true;
                    $result['logs'][] = "⭐ Terpilih profil terbaik: [{$best['profile_name']}] dengan skor {$best['score']}. Santri: {$best['santri']['nama']} (KDS: {$best['santri']['kds']})";
                } else {
                    $result['success'] = false;
                    $result['error'] = !empty($best['extracted_name'])
                        ? "Data santri atas nama '{$best['extracted_name']}' tidak ditemukan di database."
                        : "Gagal mengekstrak nama atau nomor identitas santri.";
                }

                return $result;
            }

            $result['error'] = 'Tidak ada profil yang cocok untuk mengekstrak file PDF ITAS ini.';
            return $result;

        } catch (\Throwable $e) {
            $result['error'] = 'Kesalahan saat memproses PDF: ' . $e->getMessage();
            return $result;
        }
    }

    /**
     * Analisis Cerdas File PDF Sampel untuk Membantu Pengguna Awam Membuat Template Tanpa Regex Manual
     */
    public static function analyzeSamplePdf(string $pdfPath): array
    {
        $response = [
            'success' => false,
            'raw_text' => '',
            'detected' => [
                'name_template' => '',
                'description' => '',
                'identifier_keywords' => [],
                'identifier_keywords_str' => '',
                'name_mode' => 'top_line', // 'top_line' | 'regex'
                'name_regex' => '/(?:Full\s*Name|Nama\s*Lengkap|Name)\s*:\s*([^\n\r]+)/i',
                'permit_regex' => '/(?:PERMIT\s+NUMBER|Permit\s+Number|NIORA)\s*:\s*([A-Z0-9\-]+)/i',
                'expiry_regex' => '/(?:STAY\s+PERMIT\s+EXPIRY|Stay\s+Permit\s+Expiry|Permit\s+Expiry)\s*:\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
                'passport_regex' => '/(?:Passport\s+Number|PASSPORT\s+NUMBER|No\s+Paspor)\s*:\s*([A-Z0-9\-]+)/i',
                'sample_name' => '',
                'sample_permit' => '',
                'sample_expiry' => '',
                'sample_passport' => '',
            ],
            'logs' => [],
            'error' => null
        ];

        if (!file_exists($pdfPath)) {
            $response['error'] = 'File sampel PDF tidak ditemukan.';
            return $response;
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();
            if (empty($pages)) {
                $response['error'] = 'PDF kosong atau tidak memiliki halaman.';
                return $response;
            }

            $rawText = $pages[0]->getText();
            $response['raw_text'] = $rawText;

            if (empty(trim($rawText))) {
                $response['error'] = 'Tidak ada teks yang dapat dibaca pada file PDF (kemungkinan PDF hasil foto/scan murni tanpa teks).';
                return $response;
            }

            $cleanText = str_replace("\r", "", $rawText);
            $lines = explode("\n", $cleanText);
            $cleanLines = array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));

            // 1. Deteksi Nomor ITAS / Permit Number
            $samplePermit = '';
            $permitRegex = '/(?:PERMIT\s+NUMBER|Permit\s+Number|NIORA|ITAS\s*NO\.?)\s*:\s*([A-Z0-9\-]+)/i';
            if (preg_match('/(?:PERMIT\s+NUMBER|Permit\s+Number|NIORA|NO\.?\s*ITAS|ITAS\s*NO\.?|NO\.?\s*IZIN\s*TINGGAL|PERMIT\s*NO\.?)\s*[:=]?\s*([A-Z0-9\-]+)/i', $rawText, $m)) {
                $samplePermit = trim($m[1]);
                $response['logs'][] = "Deteksi Nomor ITAS: $samplePermit (dari label teks)";
            } elseif (preg_match('/\b(2C[0-9A-Z]{2}[A-Z0-9\-]{5,})\b/i', $rawText, $m)) {
                $samplePermit = trim($m[1]);
                $response['logs'][] = "Deteksi Nomor ITAS: $samplePermit (dari pola format standar imigrasi)";
            }

            // 2. Deteksi Expiry Date / Masa Berlaku
            $sampleExpiry = '';
            $expiryRegex = '/(?:STAY\s+PERMIT\s+EXPIRY|Stay\s+Permit\s+Expiry|Permit\s+Expiry|EXPIRY\s+DATE|VALID\s+UNTIL)\s*:\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i';
            if (preg_match('/(?:STAY\s+(?:\/\s*MULTIPLE\s+ENTRIES\s+)?PERMIT\s+EXPIRY|Stay\s+Permit\s+Expiry|PERMIT\s+EXPIRY|EXPIRY\s+DATE|EXPIRY|VALID\s+UNTIL|MASA\s+BERLAKU)\s*[:=]?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4})/i', $rawText, $m)) {
                $sampleExpiry = trim($m[1]);
                $response['logs'][] = "Deteksi Masa Berlaku: $sampleExpiry";
            }

            // 3. Deteksi Nomor Paspor
            $samplePassport = '';
            $passportRegex = '/(?:TRAVEL\s+DOC(?:UMENT)?(?:\s+NUMBER|\s+NO\.?)?|PASSPORT(?:\s+NUMBER|\s+NO\.?)?|NO(?:MOR)?\.?\s*(?:DOKUMEN\s+PERJALANAN|PASPOR)|DOKUMEN\s+PERJALANAN|PASPOR)(?:[\/\s]+(?:NOMOR|NUMBER|DOKUMEN|PERJALANAN|PASPOR|PASSPORT|NO\.?))*\s*[:=]\s*[\r\n]*\s*[:=]?\s*([A-Z0-9\-]+)/i';
            $blacklistPassport = ['NOMOR', 'NUMBER', 'NO', 'DOKUMEN', 'DOCUMENT', 'PERJALANAN', 'PASPOR', 'PASSPORT', 'NAME', 'FULL', 'PERMIT', 'ITAS', 'NIORA'];
            if (preg_match($passportRegex, $rawText, $m)) {
                $cand = trim($m[1]);
                if (!in_array(strtoupper($cand), $blacklistPassport) && strlen($cand) >= 3) {
                    $samplePassport = $cand;
                }
            }
            if (empty($samplePassport)) {
                if (preg_match('/(?:TRAVEL\s+DOC(?:UMENT)?|PASSPORT|PASPOR|DOKUMEN\s+PERJALANAN)[^:\n\r]{0,50}[:=]\s*([A-Z0-9\-]+)/i', $rawText, $m)) {
                    $cand = trim($m[1]);
                    if (!in_array(strtoupper($cand), $blacklistPassport) && strlen($cand) >= 3) {
                        $samplePassport = $cand;
                    }
                }
            }
            if (empty($samplePassport)) {
                if (preg_match('/\b([A-Z][0-9]{7,9})\b/i', $rawText, $m)) {
                    $samplePassport = trim($m[1]);
                }
            }
            if (!empty($samplePassport)) {
                $response['logs'][] = "Deteksi Nomor Paspor: $samplePassport";
            }

            // 4. Deteksi Nama Santri & Mode Ekstraksi Nama
            $sampleName = '';
            $nameMode = 'top_line';
            $nameRegex = '/(?:Full\s*Name|Nama\s*Lengkap|Name)\s*:\s*([^\n\r]+)/i';

            // Cek apakah ada label nama eksplisit
            if (preg_match('/(?:Full\s*Name|Name|Nama\s*Lengkap|Nama)\s*[:=]\s*([A-Za-z0-9\s\.\',\-]+)/i', $rawText, $m)) {
                $candName = self::cleanName($m[1]);
                if (!self::isInvalidPersonName($candName)) {
                    $sampleName = $candName;
                    $nameMode = 'regex';
                    $response['logs'][] = "Deteksi Nama Santri (Label Eksplisit): $sampleName";
                }
            }

            if (empty($sampleName)) {
                // Gunakan mode Top Line Header
                $nameMode = 'top_line';
                foreach ($cleanLines as $idx => $line) {
                    if (!self::isInvalidPersonName($line) && strlen($line) >= 3 && preg_match('/^[A-Za-z\s\.\',\-]+$/', $line)) {
                        $topName = $line;
                        if (isset($cleanLines[$idx + 1])) {
                            $nextL = $cleanLines[$idx + 1];
                            if (!self::isInvalidPersonName($nextL) && strtoupper($nextL) === $nextL && preg_match('/^[A-Za-z\s\.\',\-]+$/', $nextL)) {
                                $topName .= " " . $nextL;
                            }
                        }
                        $sampleName = self::cleanName($topName);
                        $response['logs'][] = "Deteksi Nama Santri (Top Line Header): $sampleName";
                        break;
                    }
                }
            }

            // 5. Deteksi Kata Kunci Pengenal Dokumen (Identifier Keywords)
            $knownKeywords = [
                'DIRECTORATE GENERAL OF IMMIGRATION',
                'TEMPORARY STAY PERMIT',
                'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA',
                'IZIN TINGGAL TERBATAS ELEKTRONIK',
                'ELECTRONIC LIMITED STAY PERMIT',
                'MINISTRY OF IMMIGRATION',
                'REPUBLIK INDONESIA',
                'REPUBLIC OF INDONESIA',
                'LIMITED STAY PERMIT',
                'DIREKTORAT JENDERAL IMIGRASI'
            ];

            $matchedKeywords = [];
            foreach ($knownKeywords as $kw) {
                if (stripos($rawText, $kw) !== false) {
                    $matchedKeywords[] = $kw;
                }
            }

            // Jika tidak ada kata kunci umum yang cocok, ambil baris header unik
            if (empty($matchedKeywords)) {
                foreach (array_slice($cleanLines, 0, 5) as $line) {
                    if (strlen($line) > 10 && strtoupper($line) === $line && !preg_match('/\d/', $line)) {
                        $matchedKeywords[] = $line;
                    }
                }
            }

            // Ambil maksimal 3 keywords
            $matchedKeywords = array_values(array_unique(array_slice($matchedKeywords, 0, 3)));
            $keywordsStr = implode(', ', $matchedKeywords);

            // 6. Rekomendasi Nama & Deskripsi Template
            $tplName = 'Format ITAS ' . (!empty($matchedKeywords) ? mb_substr($matchedKeywords[0], 0, 25) : 'Kustom') . ' (' . date('Y') . ')';
            $tplDesc = 'Template terdeteksi otomatis dari berkas sampel ' . basename($pdfPath);

            $response['success'] = true;
            $response['detected'] = [
                'name_template' => $tplName,
                'description' => $tplDesc,
                'identifier_keywords' => $matchedKeywords,
                'identifier_keywords_str' => $keywordsStr,
                'name_mode' => $nameMode,
                'name_regex' => $nameRegex,
                'permit_regex' => $permitRegex,
                'expiry_regex' => $expiryRegex,
                'passport_regex' => $passportRegex,
                'sample_name' => $sampleName,
                'sample_permit' => $samplePermit,
                'sample_expiry' => $sampleExpiry,
                'sample_passport' => $samplePassport,
            ];

            return $response;

        } catch (\Throwable $e) {
            $response['error'] = 'Kesalahan saat menganalisis sampel PDF: ' . $e->getMessage();
            return $response;
        }
    }
}

