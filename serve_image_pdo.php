<?php
// serve_image_pdo.php?id=123
$dsn = "mysql:host=127.0.0.1;dbname=ejemplo;charset=utf8mb4";
$dbUser = "root";
$dbPass = "";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); exit; }

$stmt = $pdo->prepare("SELECT imagen_blob, imagen_mime, imagen_nombre FROM productos WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || !$row['imagen_blob']) {
    http_response_code(404);
    exit;
}

$mime = $row['imagen_mime'] ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=86400');
echo $row['imagen_blob'];
