<?php
// download.php: Verifikasi server-side dan paksa unduhan PDF
// Mitigasi utama: tidak ada lagi verifikasi di sisi klien dan akses langsung ke file PDF diblokir via .htaccess

// Helper: respons error
function respond_error(string $message, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    exit;
}

// Helper: sanitasi input sederhana
function get_post(string $key): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error('Metode tidak diizinkan', 405);
}

$school    = get_post('school');
$pendamping = get_post('pendamping');
$code      = get_post('code');

if ($school === '' || $pendamping === '' || $code === '') {
    respond_error('Parameter tidak lengkap', 400);
}

if (!preg_match('/^\d{4}$/', $code)) {
    respond_error('Kode verifikasi tidak valid', 400);
}

// Baca data sekolah (file ini diblokir dari akses langsung oleh .htaccess)
$json_file = __DIR__ . DIRECTORY_SEPARATOR . 'data_sekolah.json';
if (!is_readable($json_file)) {
    respond_error('Data tidak tersedia', 500);
}

$json = file_get_contents($json_file);
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    respond_error('Data tidak valid', 500);
}

$matchedPdf = null;
$verified   = false;

foreach ($data as $item) {
    if (!isset($item['sekolah'], $item['pdf_file'], $item['pendamping'], $item['verification_codes'])) {
        continue;
    }

    if (strcasecmp($item['sekolah'], $school) !== 0) {
        continue; // beda sekolah
    }

    // Cari pendamping pada sekolah yang sama, cocokkan index untuk kode
    $pendampingList = $item['pendamping'];
    $codesList      = $item['verification_codes'];

    // Normalisasi panjang list (antisipasi data tidak sejajar)
    $count = min(count($pendampingList), count($codesList));

    for ($i = 0; $i < $count; $i++) {
        if (strcasecmp($pendampingList[$i], $pendamping) === 0) {
            // Ketemu kandidat; cek kode
            if ((string)$codesList[$i] === $code) {
                $verified = true;
                $matchedPdf = $item['pdf_file'];
                break 2; // keluar dari kedua loop
            }
        }
    }
}

// Logging sederhana
$logDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ts = date('Y-m-d H:i:s');
$logLine = sprintf("%s\t%s\t%s\t%s\t%s\n", $ts, $ip, $school, $pendamping, $verified ? 'OK' : 'FAIL');
@file_put_contents($logDir . DIRECTORY_SEPARATOR . 'access.log', $logLine, FILE_APPEND);

if (!$verified || $matchedPdf === null) {
    respond_error('Verifikasi gagal. Periksa kembali data yang dimasukkan.', 401);
}

// Validasi nama file secara ketat (hanya dari sumber tepercaya/JSON)
$baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'pdf_files';
$filePath = $baseDir . DIRECTORY_SEPARATOR . $matchedPdf;

// Cegah path traversal dan pastikan file ada
$realBase = realpath($baseDir);
$realFile = realpath($filePath);
if ($realBase === false || $realFile === false || strpos($realFile, $realBase) !== 0) {
    respond_error('File tidak ditemukan', 404);
}
if (!is_readable($realFile)) {
    respond_error('File tidak tersedia', 404);
}

// Paksa unduhan
$filename = basename($realFile);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($realFile));
header('X-Content-Type-Options: nosniff');
readfile($realFile);
exit;
