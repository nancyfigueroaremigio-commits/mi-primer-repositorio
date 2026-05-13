<?php
require_once 'auth.php';
$mensaje = "";
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['token'] ?? '';
  $nueva = $_POST['nueva'] ?? '';
  $confirmar = $_POST['confirmar'] ?? '';

  if ($nueva === '' || $confirmar === '') {
    $mensaje = "❌ Completa ambos campos.";
  } elseif ($nueva !== $confirmar) {
    $mensaje = "❌ Las contraseñas no coinciden.";
  } else {
    $enlace = mysqli_connect("localhost", "root", "", "ejemplo");
    $stmt = $enlace->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
      $mensaje = "❌ Token inválido o expirado.";
    } else {
      $usuario = $res->fetch_assoc();
      $hash = password_hash($nueva, PASSWORD_DEFAULT);
      $stmt = $enlace->prepare("UPDATE usuarios SET contraseña = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?");
      $stmt->bind_param("si", $hash, $usuario['id']);
      $stmt->execute();
      $mensaje = "✅ Contraseña actualizada. Ya puedes iniciar sesión.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Restablecer contraseña</title>
</head>
<body>
  <h2>Restablecer contraseña</h2>
  <?php if ($mensaje): ?><p><?php echo $mensaje; ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <input type="password" name="nueva" placeholder="Nueva contraseña" required>
    <input type="password" name="confirmar" placeholder="Confirmar contraseña" required>
    <button type="submit">Actualizar contraseña</button>
  </form>
</body>
</html>
