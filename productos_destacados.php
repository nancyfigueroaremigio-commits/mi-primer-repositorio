<?php
require_once 'auth.php';

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Puedes usar un campo `destacado` en la tabla productos, o simplemente limitar por fecha o ID
$res = $mysqli->query("SELECT id, nombre, descripcion, precio FROM productos ORDER BY RAND() LIMIT 3");
$productos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

header('Content-Type: application/json');
echo json_encode($productos);
