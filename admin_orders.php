
<?php
// admin_orders.php

require_once 'auth.php';
require_login();
require_role('admin');

// Conexión a la base de datos
$servidor = "localhost";
$usuario  = "root";
$clave    = "";
$basededatos = "ejemplo";

$mysqli = new mysqli($servidor, $usuario, $clave, $basededatos);
if ($mysqli->connect_errno) {
    die("Error de conexión a la base de datos");
}

// Consulta de pedidos
$sql = "SELECT id, usuario_id, total, telefono, email, created_at, estado FROM orders ORDER BY created_at DESC LIMIT 100";
$res = $mysqli->query($sql);
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Panel de Pedidos</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f7f6f4; }
    table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #f2efe9; color: #4d4537; }
    h2 { color: #8c6c46; font-family: 'Playfair Display', serif; }
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

  <h2>Todos los pedidos</h2>

  <?php if (empty($orders)): ?>
    <p>No hay pedidos registrados.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Usuario ID</th>
          <th>Total</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Fecha</th>
          <td>Estado</td>

        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td>
            <a href="admin_ver_pedido.php?order_id=<?php echo urlencode($o['id']); ?>">
            <?php echo htmlspecialchars($o['id']); ?>
            </a>
            </td>
            <td><?php echo htmlspecialchars($o['usuario_id']); ?></td>
            <td>$<?php echo htmlspecialchars($o['total']); ?></td>
            <td><?php echo htmlspecialchars($o['telefono']); ?></td>
            <td><?php echo htmlspecialchars($o['email']); ?></td>
            <td><?php echo htmlspecialchars($o['created_at']); ?></td>
            <td>
  <form method="post" onsubmit="return actualizarEstado(event, <?php echo $o['id']; ?>)">
    <select name="estado" style="padding:4px;">
      <?php
      foreach (['pendiente', 'procesado', 'enviado', 'cancelado'] as $estado_opcion):
        $selected = ($estado_opcion === $o['estado']) ? 'selected' : '';
      ?>
        <option value="<?php echo $estado_opcion; ?>" <?php echo $selected; ?>>
          <?php echo ucfirst($estado_opcion); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" style="margin-left:6px; padding:4px 8px;">Actualizar</button>
  </form>
</td>

            <?php
$estado = strtolower($o['estado']);
$color = match ($estado) {
    'pendiente'  => '#f39c12',
    'procesado'  => '#3498db',
    'enviado'    => '#27ae60',
    'cancelado'  => '#c0392b',
    default      => '#7f8c8d',
};
?>





          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <script>
function actualizarEstado(event, orderId) {
  event.preventDefault();
  const form = event.target;
  const estado = form.estado.value;

  fetch('actualizar_estado.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'order_id=' + encodeURIComponent(orderId) + '&estado=' + encodeURIComponent(estado)
  })
  .then(res => res.json())
  .then(data => {
    alert(data.msg);
    if (data.ok) location.reload();
  })
  .catch(err => {
    console.error(err);
    alert('Error al actualizar el estado');
  });

  return false;
}
</script>


</body>
</html>
