<?php
require_once 'auth.php';
require_login();
require_role('admin');

header('Content-Type: application/json; charset=utf-8');

$dsn = "mysql:host=127.0.0.1;dbname=ejemplo;charset=utf8mb4";
$dbUser = "root";
$dbPass = "";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error de conexión a la base de datos']);
    exit;
}

$nombre      = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio      = floatval($_POST['precio'] ?? 0);

if ($nombre === '' || $precio <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Nombre y precio obligatorios']);
    exit;
}

$imagen_blob = null;
$imagen_nombre = null;
$imagen_mime = null;

if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $f = $_FILES['imagen'];
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($f['type'], $allowed)) {
        echo json_encode(['ok' => false, 'msg' => 'Tipo de imagen no permitido']);
        exit;
    }
    if ($f['size'] > 5 * 1024 * 1024) { // 5 MB
        echo json_encode(['ok' => false, 'msg' => 'Imagen demasiado grande (máx 5 MB)']);
        exit;
    }
    $imagen_blob = file_get_contents($f['tmp_name']);
    $imagen_nombre = basename($f['name']);
    $imagen_mime = $f['type'];
}

try {
    $sql = "INSERT INTO productos (nombre, descripcion, precio, imagen_blob, imagen_nombre, imagen_mime, creado_en)
            VALUES (:nombre, :descripcion, :precio, :imagen_blob, :imagen_nombre, :imagen_mime, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmt->bindValue(':precio', $precio);
    if ($imagen_blob !== null) {
        $stmt->bindParam(':imagen_blob', $imagen_blob, PDO::PARAM_LOB);
        $stmt->bindValue(':imagen_nombre', $imagen_nombre, PDO::PARAM_STR);
        $stmt->bindValue(':imagen_mime', $imagen_mime, PDO::PARAM_STR);
    } else {
        $stmt->bindValue(':imagen_blob', null, PDO::PARAM_NULL);
        $stmt->bindValue(':imagen_nombre', '', PDO::PARAM_STR);
        $stmt->bindValue(':imagen_mime', '', PDO::PARAM_STR);
    }
    $stmt->execute();
    echo json_encode(['ok' => true, 'msg' => 'Producto creado correctamente']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar: ' . $e->getMessage()]);
}
