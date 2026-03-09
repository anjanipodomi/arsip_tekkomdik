<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status'=>'error','pesan'=>'Akses ditolak']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['status'=>'error','pesan'=>'Gambar tidak ditemukan']);
    exit;
}

$tmpDir = __DIR__ . '/../assets/ocr/camera';
if (!is_dir($tmpDir)) mkdir($tmpDir,0777,true);

$path = $tmpDir.'/cam_'.uniqid().'.jpg';
move_uploaded_file($_FILES['image']['tmp_name'], $path);

$output = $tmpDir.'/out_'.uniqid();
exec("tesseract \"$path\" \"$output\" -l ind 2>&1");

$text = file_exists("$output.txt") ? file_get_contents("$output.txt") : '';

if(trim($text)===''){
    echo json_encode(['status'=>'error','pesan'=>'OCR kamera gagal']);
    exit;
}

// parsing sama dengan file
$lines = preg_split("/\r\n|\n|\r/", trim($text));

$data = [
    'asal_surat' => substr($lines[0] ?? '',0,150),
    'nomor_surat'=> '',
    'tanggal_surat'=>'',
    'isi_ringkas'=>substr($text,0,300)
];

if (preg_match('/(Nomor|No\.?)\s*[:\-]?\s*([A-Z0-9\/\.\-]+)/i',$text,$m))
    $data['nomor_surat']=$m[2];

if (preg_match('/(\d{1,2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4})/i',$text,$m))
    $data['tanggal_surat']=date('Y-m-d',strtotime($m[1]));

echo json_encode(['status'=>'ok','data'=>$data]);
exit;
