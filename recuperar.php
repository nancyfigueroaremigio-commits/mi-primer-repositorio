<?php
require_once 'auth.php';
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $correo = trim($_POST['correo'] ?? '');
  if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $mensaje = "❌ Ingresa un correo válido.";
  } else {
    // Conexión
    $enlace = mysqli_connect("localhost", "root", "", "ejemplo");
    $stmt = $enlace->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
      $mensaje = "❌ No hay cuenta registrada con ese correo.";
    } else {
      // Generar token y guardar
      $token = bin2hex(random_bytes(16));
      $stmt = $enlace->prepare("UPDATE usuarios 
                                SET reset_token = ?, reset_expira = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                                WHERE correo = ?");
      $stmt->bind_param("ss", $token, $correo);
      $stmt->execute();

      // Construir enlace
      $link = "http://localhost/Ejemplo/reset.php?token=$token";

      // Mostrar en pantalla (modo pruebas)
      $mensaje = "✅ Se ha generado un enlace de recuperación.<br>
                  <a href='$link' target='_blank'>$link</a>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
</head>
<body>
  <h2>Recuperar contraseña</h2>
  <?php if ($mensaje): ?>
    <p><?php echo $mensaje; ?></p>
  <?php endif; ?>
  <form method="post">
    <input type="email" name="correo" placeholder="Tu correo" required>
    <button type="submit">Enviar enlace</button>
  </form>
</body>
</html>
