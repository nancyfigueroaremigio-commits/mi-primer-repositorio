<?php
// productos_api.php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    echo json_encode([]);
    exit;
}

$res = $mysqli->query("SELECT id, nombre, descripcion, precio FROM productos ORDER BY creado_en DESC");
$productos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
echo json_encode($productos);
