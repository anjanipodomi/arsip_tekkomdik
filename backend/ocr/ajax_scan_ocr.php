<?php
ob_start();

/**
 * AJAX OCR + AUTO FILL FORM (FINAL FIX STEP 3)
 * Arsip Inaktif Digital - Tekkomdik DIY
 */

error_reporting(E_ALL);
ini_set('display_errors', 1); // jangan rusak JSON

session_start();
header('Content-Type: application/json');

// ================= CEK LOGIN =================
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status'=>'error','pesan'=>'Akses ditolak']);
    exit;
}

// ================= CEK FILE =================
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status'=>'error','pesan'=>'File tidak diterima server']);
    exit;
}

// ================= PATH TOOL =================
$tesseractRaw = 'C:\\Tesseract-OCR\\tesseract.exe';
$pdftoppmRaw  = 'C:\\poppler\\Library\\bin\\pdftoppm.exe';

$tesseract = realpath($tesseractRaw);
$pdftoppm  = realpath($pdftoppmRaw);

if ($tesseract === false) {
    echo json_encode(['status'=>'error','pesan'=>'Tesseract tidak ditemukan']);
    exit;
}

// ================= FOLDER TMP =================
$tmpDir = __DIR__ . '/../assets/ocr/tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

// ================= VALIDASI FILE =================
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['pdf','jpg','jpeg','png'])) {
    echo json_encode(['status'=>'error','pesan'=>'Format file tidak didukung']);
    exit;
}

// ================= SIMPAN FILE =================
$filePath = $tmpDir . '/scan_' . uniqid() . '.' . $ext;
if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
    echo json_encode(['status'=>'error','pesan'=>'Gagal menyimpan file upload']);
    exit;
}

$textOCR = '';
$log  = "FILE: $filePath\n";
$log .= "TESSERACT: $tesseract\n";

// ================= PREPROCESS IMAGE =================
function preprocessImage($filePath) {

    $info = getimagesize($filePath);
    if ($info === false) return false;

    switch ($info['mime']) {
        case 'image/jpeg': $img = imagecreatefromjpeg($filePath); break;
        case 'image/png':  $img = imagecreatefrompng($filePath);  break;
        default: return false;
    }

    if (!$img) return false;

    $w = imagesx($img);
    $h = imagesy($img);

    // resize max 2000px
    if ($w > 2000) {
        $nw = 2000;
        $nh = intval($h * (2000 / $w));
        $tmp = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($tmp, $img, 0,0,0,0, $nw,$nh, $w,$h);
        imagedestroy($img);
        $img = $tmp;
    }

    imagefilter($img, IMG_FILTER_GRAYSCALE);
    imagefilter($img, IMG_FILTER_CONTRAST, -15);


    imagejpeg($img, $filePath, 90);
    imagedestroy($img);

    return true;
}

// ================= PROSES OCR =================
if ($ext === 'pdf') {

    if ($pdftoppm === false) {
        echo json_encode(['status'=>'error','pesan'=>'Poppler tidak ditemukan']);
        exit;
    }

    $imgBase = $tmpDir . '/img_' . uniqid();
    $cmdPdf  = "\"{$pdftoppm}\" -png -r 300 -f 1 -l 1 \"{$filePath}\" \"{$imgBase}\" 2>&1";
    exec($cmdPdf);

    foreach (glob($imgBase . '-*.png') as $img) {
        preprocessImage($img);

        $out = $tmpDir . '/out_' . uniqid();
        $cmd = "\"{$tesseract}\" \"{$img}\" \"{$out}\" -l ind --oem 1 --psm 4 2>&1";
        exec($cmd);

        if (file_exists($out.'.txt')) {
            $textOCR .= file_get_contents($out.'.txt')."\n";
        }
    }

} else {

    preprocessImage($filePath);

    $out = $tmpDir . '/out_' . uniqid();
    $cmd = "\"{$tesseract}\" \"{$filePath}\" \"{$out}\" -l ind --oem 1 --psm 4 2>&1";
    exec($cmd);

    if (file_exists($out.'.txt')) {
        $textOCR = file_get_contents($out.'.txt');
    }
}

// ================= DEBUG =================
file_put_contents(__DIR__ . '/../assets/ocr/debug_ocr.txt', $textOCR);

// ================= CEK HASIL =================
if (trim($textOCR) === '') {
    echo json_encode([
        'status'=>'error',
        'pesan'=>'OCR gagal membaca teks'
    ]);
    exit;
}


// ================= PARSING =================
$hasil = [
    'asal_surat'    => '',
    'nomor_surat'   => '',
    'tanggal_surat' => '',
    'isi_ringkas'   => ''
];

$lines = preg_split("/\r\n|\n|\r/", trim($textOCR));

// ================= NORMALISASI OCR =================
$normalizedText = preg_replace('/\s+/', ' ', $textOCR);

$bulanFix = [
    'Desombat'=>'Desember','Dosember'=>'Desember',
    'Septembar'=>'September','Oktobar'=>'Oktober',
    'Novembar'=>'November'
];

foreach ($bulanFix as $s=>$b) {
    $normalizedText = preg_replace("/$s/i",$b,$normalizedText);
}

// ================= ASAL SURAT =================
$asal = [];

foreach ($lines as $l) {
    $l = trim($l);

    if (
        preg_match('/PEMERINTAH|DINAS|BALAI|KEMENTERIAN|UNIVERSITAS/i', $l)
        && strlen($l) > 10
        && strlen($l) < 120
    ) {
        $asal[] = $l;
    }

    if (count($asal) >= 3) break;
}

$hasil['asal_surat'] = implode(' ', $asal);


// ================= NOMOR SURAT (FINAL STABIL) =================
$nomorSurat = '';

for ($i = 0; $i < count($lines); $i++) {
    if (preg_match('/^Nomor/i', trim($lines[$i]))) {

        $candidate = $lines[$i];

        // kadang nomor kepotong ke baris berikutnya
        if (isset($lines[$i+1]) && strlen(trim($lines[$i+1])) < 60) {
            $candidate .= ' '.$lines[$i+1];
        }

        // buang label
        $candidate = preg_replace('/Nomor\s*[:\-]?\s*/i', '', $candidate);

        // stop jika ketemu label lain
        $candidate = preg_replace('/(Sifat|Lampiran|Perihal).*/i', '', $candidate);

        // normalisasi OCR error umum
        $map = [
            'O' => '0',
            'o' => '0',
            'I' => '1',
            'l' => '1',
            'S' => '5',
            'B' => '8'
        ];
        $candidate = strtr($candidate, $map);

        // rapikan spasi
        $candidate = preg_replace('/\s+/', '', $candidate);

        // validasi longgar (jangan kosong & panjang masuk akal)
        if (strlen($candidate) >= 6 && strlen($candidate) <= 60) {
            $nomorSurat = $candidate;
            break;
        }
    }
}

$hasil['nomor_surat'] = $nomorSurat;

// ================= TANGGAL SURAT (ANTI 1970) =================
$bulan = [
    'Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04',
    'Mei'=>'05','Juni'=>'06','Juli'=>'07','Agustus'=>'08',
    'September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12'
];

if (preg_match('/(\d{1,2})\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+(\d{4})/i', $normalizedText, $m)) {
    $hasil['tanggal_surat'] =
        $m[3].'-'.$bulan[ucfirst(strtolower($m[2]))].'-'.str_pad($m[1],2,'0',STR_PAD_LEFT);
}

// ================= ISI RINGKAS =================
$ringkas = '';

if (preg_match(
    '/(Dengan hormat|Memperhatikan|Sehubungan dengan)(.+?)(Atas nama|Demikian|Hormat kami)/is',
    $textOCR,
    $m
)) {
    $ringkas = trim($m[2]);
} else {
    // fallback aman
    $ringkas = substr(trim($textOCR), 200, 300);
}

$hasil['isi_ringkas'] = substr(preg_replace('/\s+/', ' ', $ringkas), 0, 300);

ob_clean();

// ================= RESPONSE =================
echo json_encode([
    'status'=>'ok',
    'hasil'=>$hasil
]);
exit;
