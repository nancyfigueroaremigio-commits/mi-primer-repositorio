<?php
require_once 'auth.php';
require_login();

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$usuario_id = current_user()['id'];

if ($order_id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    echo json_encode(['ok' => false, 'msg' => 'Error de conexión']);
    exit;
}

// Verificar que el pedido es del usuario y está pendiente
$stmt = $mysqli->prepare("SELECT estado FROM orders WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $order_id, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['ok' => false, 'msg' => 'Pedido no encontrado']);
    exit;
}

if ($order['estado'] !== 'pendiente') {
    echo json_encode(['ok' => false, 'msg' => 'Solo puedes cancelar pedidos pendientes']);
    exit;
}

// Cancelar el pedido
$stmt = $mysqli->prepare("UPDATE orders SET estado = 'cancelado' WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true, 'msg' => 'Pedido cancelado']);
