<?php
require_once 'auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    echo json_encode(['ok' => false, 'msg' => 'Error de conexión']);
    exit;
}

// Leer datos JSON enviados desde el frontend
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
    exit;
}

// Extraer datos del pedido y pago
$order_id   = intval($data['order_id'] ?? 0); // ✅ ID del pedido generado previamente
$email      = trim($data['email'] ?? '');
$telefono   = trim($data['telefono'] ?? '');
$direccion  = trim($data['direccion'] ?? '');
$nombre     = trim($data['tarjeta']['nombre'] ?? '');
$numero     = trim($data['tarjeta']['numero'] ?? '');
$exp        = trim($data['tarjeta']['expiracion'] ?? '');
$cvv        = trim($data['tarjeta']['cvv'] ?? '');
$usuario_id = current_user()['id'] ?? null;

// Validación de campos obligatorios
if ($direccion === '' || $nombre === '' || $numero === '' || $exp === '' || $cvv === '') {
    echo json_encode(['ok' => false, 'msg' => 'Faltan campos obligatorios']);
    exit;
}

// Insertar datos en la tabla pagos
$stmt = $mysqli->prepare("INSERT INTO pagos (
    usuario_id, order_id, email, telefono, direccion,
    nombre_tarjeta, numero_tarjeta, expiracion, cvv
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['ok' => false, 'msg' => 'Error al preparar la consulta']);
    exit;
}

$stmt->bind_param("iisssssss",
    $usuario_id, $order_id, $email, $telefono, $direccion,
    $nombre, $numero, $exp, $cvv
);

$ok = $stmt->execute();
$stmt->close();

// Respuesta al frontend
echo json_encode([
    'ok' => $ok,
    'msg' => $ok ? 'Pago guardado correctamente' : 'Error al guardar el pago'
]);

