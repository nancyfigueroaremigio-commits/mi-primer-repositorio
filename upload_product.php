<?php
require_once 'auth.php';
require_login();
require_role('admin');
header('Content-Type: application/json; charset=utf-8');

$mysqli = new mysqli("localhost","root","","ejemplo");
if ($mysqli->connect_errno) {
    echo json_encode(['ok'=>false,'msg'=>'Error de conexión a la base de datos']);
    exit;
}

$nombre      = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio      = floatval($_POST['precio'] ?? 0);

if ($nombre === '' || $precio <= 0) {
    echo json_encode(['ok'=>false,'msg'=>'Nombre y precio obligatorios']);
    exit;
}

$imagen_blob = null;
$imagen_nombre = null;
$imagen_mime = null;

if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $f = $_FILES['imagen'];
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($f['type'], $allowed)) {
        echo json_encode(['ok'=>false,'msg'=>'Tipo de imagen no permitido']);
        exit;
    }
    if ($f['size'] > 5 * 1024 * 1024) { // 5 MB
        echo json_encode(['ok'=>false,'msg'=>'Imagen demasiado grande (máx 5 MB)']);
        exit;
    }
    $imagen_blob = file_get_contents($f['tmp_name']);
    $imagen_nombre = basename($f['name']);
    $imagen_mime = $f['type'];
}

// Preparar INSERT. Asumimos columnas: nombre, descripcion, precio, imagen_blob, imagen_nombre, imagen_mime, creado_en
$stmt = $mysqli->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen_blob, imagen_nombre, imagen_mime, creado_en) VALUES (?, ?, ?, ?, ?, ?, NOW())");
if (!$stmt) {
    echo json_encode(['ok'=>false,'msg'=>'Error interno al preparar statement']);
    exit;
}

// Para bind_param necesitamos tipos: s (string), i (int), d (double), b (blob) no existe 'b' directo en bind_param en mysqli; usamos send_long_data
// Haremos bind_param con un placeholder para blob y luego send_long_data
// Tipos: nombre(s), descripcion(s), precio(d), imagen_blob(llegará con send_long_data), imagen_nombre(s), imagen_mime(s)
$stmt->bind_param("ssds s", $nombre, $descripcion, $precio, $imagen_nombre, $imagen_mime);

// El índice para send_long_data es 3 (0-based): 0:nombre,1:descripcion,2:precio,3:imagen_blob,4:imagen_nombre,5:imagen_mime
if ($imagen_blob !== null) {
    $stmt->send_long_data(3, $imagen_blob);
}

$ok = $stmt->execute();
if ($ok) {
    echo json_encode(['ok'=>true,'msg'=>'Producto creado correctamente']);
} else {
    echo json_encode(['ok'=>false,'msg'=>$stmt->error]);
}
$stmt->close();
$mysqli->close();
