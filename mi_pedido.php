<?php
require_once 'auth.php';
require_login();

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    die("Error de conexión");
}

$order_id = intval($_GET['order_id'] ?? 0);
$usuario_id = current_user()['id'] ?? null;

// Verificar que el pedido pertenece al usuario
$stmt = $mysqli->prepare("SELECT id, total, telefono, email, created_at, estado FROM orders WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $order_id, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
$pedido = $res->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo "Pedido no encontrado o no autorizado.";
    exit;
}

// Obtener productos del pedido
$stmt = $mysqli->prepare("SELECT nombre, precio, cantidad, subtotal FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Verificar si tiene pago
$stmt = $mysqli->prepare("SELECT id, direccion, creado_en FROM pagos WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$pago = $res->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle del Pedido #<?php echo $order_id; ?></title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    h2 { color: #8c6c46; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    .estado { font-weight: bold; color: #444; }
    .pagado { color: green; font-weight: bold; }
    .no-pagado { color: red; font-weight: bold; }
  </style>
</head>
<a href="pedido_pdf.php?order_id=<?php echo urlencode($pedido['id']); ?>" style="background:#8c6c46; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold;">
    📄 Descargar PDF del pedido
  </a>
<body>

<h2>Pedido #<?php echo $pedido['id']; ?></h2>
<p><strong>Fecha:</strong> <?php echo $pedido['created_at']; ?></p>
<p><strong>Total:</strong> $<?php echo $pedido['total']; ?></p>
<p><strong>Estado:</strong> <span class="estado"><?php echo ucfirst($pedido['estado']); ?></span></p>
<p><strong>Pago:</strong>
  <?php if ($pago): ?>
    <span class="pagado">✅ Pagado el <?php echo $pago['creado_en']; ?></span>
  <?php else: ?>
    <span class="no-pagado">❌ No pagado</span>
  <?php endif; ?>
</p>

<h3>Productos en este pedido:</h3>
<table>
  <thead>
    <tr>
      <th>Producto</th>
      <th>Precio</th>
      <th>Cantidad</th>
      <th>Subtotal</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $item): ?>
      <tr>
        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
        <td>$<?php echo $item['precio']; ?></td>
        <td><?php echo $item['cantidad']; ?></td>
        <td>$<?php echo $item['subtotal']; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<p style="margin-top:20px;">
  <a href="index.php" style="text-decoration:none; color:#8c6c46;">← Volver a Mi cuenta</a>
</p>

</body>
</html>
