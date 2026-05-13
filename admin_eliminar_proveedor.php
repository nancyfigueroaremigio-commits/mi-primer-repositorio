<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) die("Error de conexión");

$id = intval($_GET['id'] ?? 0);

$stmt = $mysqli->prepare("DELETE FROM proveedores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: admin_users.php");
exit;
