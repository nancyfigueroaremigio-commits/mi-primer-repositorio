<?php
// admin_order_view.php

require_once 'auth.php';
require_login();
require_role('admin');

// Validar parámetro
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    echo "ID de pedido inválido.";
    exit;
}

// Conexión a la base de datos
$servidor = "localhost";
$usuario  = "root";
$clave    = "";
$basededatos = "ejemplo";

$mysqli = new mysqli($servidor, $usuario, $clave, $basededatos);
if ($mysqli->connect_errno) {
    die("Error de conexión a la base de datos");
}

// Consultar encabezado del pedido
$stmt = $mysqli->prepare("SELECT id, usuario_id, total, telefono, email, created_at FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Pedido no encontrado.";
    exit;
}

// Consultar productos del pedido
$stmt_items = $mysqli->prepare("SELECT product_id, nombre, precio, cantidad, subtotal FROM order_items WHERE order_id = ?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$res_items = $stmt_items->get_result();
$items = $res_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Detalle del Pedido #<?php echo htmlspecialchars($order_id); ?></title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f7f6f4; }
    h2 { color: #8c6c46; font-family: 'Playfair Display', serif; }
    table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #f2efe9; color: #4d4537; }
    .actions { margin-bottom: 16px; }
    a { color: #4d4537; text-decoration: none; font-weight: bold; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="actions">
    <a href="admin_orders.php">← Volver a pedidos</a> |
    <a href="index.php">Inicio</a> |
    <a href="logout.php">Cerrar sesión</a>
  </div>

  <h2>Pedido #<?php echo htmlspecialchars($order['id']); ?></h2>
  <p><strong>Usuario ID:</strong> <?php echo htmlspecialchars($order['usuario_id']); ?></p>
  <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($order['telefono']); ?></p>
  <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
  <p><strong>Fecha:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
  <p><strong>Total:</strong> $<?php echo htmlspecialchars($order['total']); ?></p>

  <h3>Productos del pedido</h3>
  <?php if (empty($items)): ?>
    <p>No hay productos registrados en este pedido.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID Producto</th>
          <th>Nombre</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?php echo htmlspecialchars($item['product_id']); ?></td>
            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
            <td>$<?php echo htmlspecialchars($item['precio']); ?></td>
            <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
            <td>$<?php echo htmlspecialchars($item['subtotal']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
