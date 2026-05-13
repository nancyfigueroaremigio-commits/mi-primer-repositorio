<?php
require_once 'auth.php';
require_login();
require_role('admin');

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$nuevo_estado = trim($_POST['estado'] ?? '');

$estados_validos = ['pendiente', 'procesado', 'enviado', 'cancelado'];
if ($order_id <= 0 || !in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    echo json_encode(['ok' => false, 'msg' => 'Error de conexión']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE orders SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $nuevo_estado, $order_id);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true, 'msg' => 'Estado actualizado']);
