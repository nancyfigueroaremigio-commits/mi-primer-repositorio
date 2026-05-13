<?php
require_once 'auth.php';
if (!is_logged_in()) exit;

$usuario_id = current_user()['id'];
$enlace = mysqli_connect("localhost", "root", "", "ejemplo");

$stmt = $enlace->prepare("SELECT perfil_icono FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($icono);
$stmt->fetch();

if ($stmt->num_rows === 0 || !$icono) {
    header("Content-Type: image/png");
    readfile("img/Iconopred.png"); // ✅ imagen predeterminada
    exit;
}

header("Content-Type: image/png"); // o image/jpeg si corresponde
echo $icono;
?>
