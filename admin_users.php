<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    die("Error de conexión");
}

$res = $mysqli->query("SELECT id, nombre, correo, telefono, role FROM usuarios ORDER BY id DESC");
$usuarios = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios registrados</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    h2 { color: #8c6c46; }
    table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    th { background: #f2efe9; color: #4d4537; }
    .actions { margin-bottom: 16px; }
    a { color: #4d4537; text-decoration: none; font-weight: bold; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="actions">
    <a href="index.php">← Volver al sitio</a> |
    <a href="logout.php">Cerrar sesión</a>
  </div>
<div style="margin-top:20px;">
  <a href="generar_pdf.php" style="background:#8c6c46; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none; font-weight:bold;">
    📄 Descargar PDF de productos
  </a>
</div>
  <h2>Usuarios registrados</h2>

  <?php if (empty($usuarios)): ?>
    <p>No hay usuarios registrados.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Teléfono</th>
          <th>Rol</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?php echo htmlspecialchars($u['id']); ?></td>
            <td><?php echo htmlspecialchars($u['nombre']); ?></td>
            <td><?php echo htmlspecialchars($u['correo']); ?></td>
            <td><?php echo htmlspecialchars($u['telefono']); ?></td>
            <td><?php echo htmlspecialchars($u['role']); ?></td>
            <td>
            <a href="admin_editar_usuario.php?id=<?php echo urlencode($u['id']); ?>">Editar</a>
            </td>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <h2 id="proveedores" style="margin-top:40px;">Proveedores registrados</h2>

<?php
$resProv = $mysqli->query("SELECT id, nombre, contacto, telefono, email, entregan, creado_en FROM proveedores ORDER BY creado_en DESC");
$proveedores = $resProv ? $resProv->fetch_all(MYSQLI_ASSOC) : [];
?>

<?php if (empty($proveedores)): ?>
  <p>No hay proveedores registrados.</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Contacto</th>
        <th>Teléfono</th>
        <th>Email</th>
        <th>Entregan</th>
        <th>Registrado el</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($proveedores as $p): ?>
        <tr>
          <td><?php echo htmlspecialchars($p['id']); ?></td>
          <td><?php echo htmlspecialchars($p['nombre']); ?></td>
          <td><?php echo htmlspecialchars($p['contacto']); ?></td>
          <td><?php echo htmlspecialchars($p['telefono']); ?></td>
          <td><?php echo htmlspecialchars($p['email']); ?></td>
          <td><?php echo htmlspecialchars($p['entregan']); ?></td>
          <td><?php echo htmlspecialchars($p['creado_en']); ?></td>
          <td>
            <a href="admin_editar_proveedor.php?id=<?php echo urlencode($p['id']); ?>">Editar</a> |
            <a href="admin_eliminar_proveedor.php?id=<?php echo urlencode($p['id']); ?>" onclick="return confirm('¿Eliminar proveedor?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>


</body>
</html>
