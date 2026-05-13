<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) die("Error de conexión");

$id = intval($_GET['id'] ?? 0);
$mensaje = "";

// Obtener datos actuales
$stmt = $mysqli->prepare("SELECT nombre, correo, telefono, role FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();
$stmt->close();

if (!$usuario) {
  echo "Usuario no encontrado.";
  exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $correo = trim($_POST['correo']);
  $telefono = trim($_POST['telefono']);
  $role = $_POST['role'];

  $stmt = $mysqli->prepare("UPDATE usuarios SET nombre = ?, correo = ?, telefono = ?, role = ? WHERE id = ?");
  $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $role, $id);
  $ok = $stmt->execute();
  $stmt->close();

  $mensaje = $ok ? "✅ Usuario actualizado correctamente" : "❌ Error al actualizar usuario";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f9f9f9; }
    form { max-width: 400px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 5px #ccc; }
    input, select { width: 100%; padding: 8px; margin-bottom: 10px; }
    button { background: #8c6c46; color: #fff; padding: 10px 14px; border: none; border-radius: 6px; cursor: pointer; }
    .msg { margin-bottom: 20px; font-weight: bold; color: #444; text-align: center; }
  </style>
</head>
<body>

<h2 style="text-align:center; color:#8c6c46;">Editar Usuario</h2>
<?php if ($mensaje): ?><div class="msg"><?php echo $mensaje; ?></div><?php endif; ?>

<form method="post">
  <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
  <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
  <input type="tel" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
  <select name="role">
    <option value="user" <?php if ($usuario['role'] === 'user') echo 'selected'; ?>>Usuario</option>
    <option value="admin" <?php if ($usuario['role'] === 'admin') echo 'selected'; ?>>Administrador</option>
  </select>
  <button type="submit">Guardar cambios</button>
</form>

<div style="text-align:center; margin-top:20px;">
  <a href="admin_users.php" style="color:#8c6c46; font-weight:bold;">← Volver a usuarios</a>
</div>

</body>
</html>
