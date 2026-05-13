<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    die("Error de conexión");
}

$mensaje = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "ID inválido";
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $imagen = trim($_POST['imagen'] ?? '');

    if ($nombre === '' || $precio <= 0) {
        $mensaje = '❌ Nombre y precio son obligatorios';
    } else {
        $stmt = $mysqli->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $nombre, $descripcion, $precio, $imagen, $id);
        if ($stmt->execute()) {
            $mensaje = '✅ Producto actualizado correctamente';
        } else {
            $mensaje = '❌ Error al actualizar: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Obtener datos actuales del producto
$stmt = $mysqli->prepare("SELECT nombre, descripcion, precio, imagen FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$producto = $res->fetch_assoc();
$stmt->close();

if (!$producto) {
    echo "Producto no encontrado";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Producto</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f7f6f4; }
    h2 { color: #8c6c46; font-family: 'Playfair Display', serif; }
    form { max-width: 500px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
    label { display: block; margin-top: 12px; font-weight: bold; }
    input, textarea { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; }
    button { margin-top: 16px; background: #8c6c46; color: #fff; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; }
    .msg { margin-top: 20px; font-weight: bold; color: #c0392b; }
  </style>
</head>
<body>
  <a href="admin_products.php">← Volver a productos</a>
  <h2>Editar producto</h2>
  <form method="post">
  <label>Nombre</label>
  <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

  <label>Descripción</label>
  <textarea name="descripcion" rows="4"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>

  <label>Precio</label>
  <input type="number" name="precio" step="0.01" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>

  <button type="submit">Guardar cambios</button>
</form>
<?php if ($mensaje): ?>
  <div class="msg"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>



