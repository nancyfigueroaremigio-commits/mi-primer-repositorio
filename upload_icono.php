<?php
require_once 'auth.php';
if (!is_logged_in()) {
    die("Debes iniciar sesión.");
}

$usuario_id = current_user()['id'];

if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
    $contenido = file_get_contents($_FILES['icono']['tmp_name']);

    $enlace = mysqli_connect("localhost", "root", "", "ejemplo");
    $stmt = $enlace->prepare("UPDATE usuarios SET perfil_icono = ? WHERE id = ?");
    $stmt->bind_param("bi", $contenido, $usuario_id);
    $stmt->send_long_data(0, $contenido);
    $stmt->execute();

    echo "✅ Icono actualizado directamente en la base de datos.";
} else {
    echo "❌ No se subió ningún archivo.";
}
?>
