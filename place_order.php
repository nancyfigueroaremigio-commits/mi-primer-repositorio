<?php
// place_order.php
require_once 'auth.php';
header('Content-Type: application/json');

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    echo json_encode(['ok' => false, 'msg' => 'Error de conexión a la base de datos']);
    exit;
}

// Leer datos JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['cart']) || !is_array($data['cart'])) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
    exit;
}

$cart = $data['cart'];
$email = trim($data['email'] ?? '');
$telefono = trim($data['telefono'] ?? '');
$usuario_id = is_logged_in() ? current_user()['id'] : null;

// Calcular total
$total = 0;
foreach ($cart as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Insertar pedido
$stmt = $mysqli->prepare("INSERT INTO orders (usuario_id, total, telefono, email, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("idss", $usuario_id, $total, $telefono, $email);
if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar el pedido']);
    exit;
}
$order_id = $stmt->insert_id;
$stmt->close();

// Insertar productos del pedido
$stmt_item = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, nombre, precio, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($cart as $item) {
    $subtotal = $item['precio'] * $item['cantidad'];
    $stmt_item->bind_param("iisddi", $order_id, $item['id'], $item['nombre'], $item['precio'], $item['cantidad'], $subtotal);
    $stmt_item->execute();
}
$stmt_item->close();

echo json_encode(['ok' => true, 'order_id' => $order_id]);


