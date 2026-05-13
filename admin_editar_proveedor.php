<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) die("Error de conexión");

$id = intval($_GET['id'] ?? 0);
$mensaje = "";

// Obtener datos actuales
$stmt = $mysqli->prepare("SELECT nombre, contacto, telefono, email, entregan FROM proveedores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$prov = $res->fetch_assoc();
$stmt->close();

if (!$prov) {
  echo "Proveedor no encontrado.";
  exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $contacto = trim($_POST['contacto']);
  $telefono = trim($_POST['telefono']);
  $email = trim($_POST['email']);
  $entregan = trim($_POST['entregan']);

  $stmt = $mysqli->prepare("UPDATE proveedores SET nombre = ?, contacto = ?, telefono = ?, email = ?, entregan = ? WHERE id = ?");
  $stmt->bind_param("sssssi", $nombre, $contacto, $telefono, $email, $entregan, $id);
  $ok = $stmt->execute();
  $stmt->close();

  $mensaje = $ok ? "✅ Proveedor actualizado" : "❌ Error al actualizar proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar proveedor</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f9f9f9; }
    form { max-width: 400px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 5px #ccc; }
    input { width: 100%; padding: 8px; margin-bottom: 10px; }
    button { background: #8c6c46; color: #fff; padding: 10px 14px; border: none; border-radius: 6px; cursor: pointer; }
    .msg { margin-bottom: 20px; font-weight: bold; color: #444; text-align: center; }
  </style>
</head>
<body>

<h2 style="text-align:center; color:#8c6c46;">Editar proveedor</h2>
<?php if ($mensaje): ?><div class="msg"><?php echo $mensaje; ?></div><?php endif; ?>

<form method="post">
  <input type="text" name="nombre" value="<?php echo htmlspecialchars($prov['nombre']); ?>" required>
  <input type="text" name="contacto" value="<?php echo htmlspecialchars($prov['contacto']); ?>">
  <input type="tel" name="telefono" value="<?php echo htmlspecialchars($prov['telefono']); ?>">
  <input type="email" name="email" value="<?php echo htmlspecialchars($prov['email']); ?>">
  <input type="text" name="entregan" value="<?php echo htmlspecialchars($prov['entregan']); ?>" placeholder="¿Qué entregan?">
  <button type="submit">Guardar cambios</button>
</form>

<div style="text-align:center; margin-top:20px;">
  <a href="admin_users.php" style="color:#8c6c46; font-weight:bold;">← Volver</a>
</div>

</body>
</html>
