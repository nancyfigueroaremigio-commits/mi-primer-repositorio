<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) {
    die("Error de conexión");
}

$mensaje = '';

// Insertar nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $imagen = trim($_POST['imagen'] ?? '');

    if ($nombre !== '' && $precio > 0) {
        $stmt = $mysqli->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $nombre, $descripcion, $precio, $imagen);
        if ($stmt->execute()) {
            $mensaje = '✅ Producto agregado correctamente';
        } else {
            $mensaje = '❌ Error al guardar: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensaje = '❌ Nombre y precio son obligatorios';
    }
}

// Eliminar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $id = (int)$_POST['eliminar_id'];
    $stmt = $mysqli->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Obtener productos
$res = $mysqli->query("SELECT id, nombre, precio, imagen FROM productos ORDER BY creado_en DESC");
$productos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Gestión de Productos</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f7f6f4; }
    h2 { color: #8c6c46; font-family: 'Playfair Display', serif; }
    form { max-width: 500px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
    label { display: block; margin-top: 12px; font-weight: bold; }
    input, textarea { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; }
    button { margin-top: 16px; background: #8c6c46; color: #fff; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; }
    .msg { margin-top: 20px; font-weight: bold; color: #c0392b; text-align:center; }
    table { width: 100%; border-collapse: collapse; margin-top: 40px; background: #fff; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #f2efe9; color: #4d4537; }
    form.inline { display:inline; }
    .edit { background:#3498db; color:#fff; padding:6px 10px; border:none; border-radius:4px; cursor:pointer; }
    .delete { background:#c0392b; color:#fff; padding:6px 10px; border:none; border-radius:4px; cursor:pointer; }
  </style>
</head>
<body>
  <a href="index.php">← Volver al sitio</a>
  <h2>Agregar nuevo producto</h2>
<form id="product-form" method="post" enctype="multipart/form-data">
  <label>Nombre</label>
  <input type="text" name="nombre" required>

  <label>Descripción</label>
  <textarea name="descripcion" rows="4"></textarea>

  <label>Precio</label>
  <input type="number" name="precio" step="0.01" required>

  <label>Imagen del producto</label>
  <input type="file" name="imagen" accept="image/*" required>

  <button type="submit">Guardar producto</button>
  <div id="form-msg" style="margin-top:10px;font-weight:600;"></div>
</form>
<script>
document.getElementById('product-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const form = e.target;
  const msg = document.getElementById('form-msg');
  msg.textContent = 'Guardando...';
  msg.style.color = 'black';

  const fd = new FormData(form);
  try {
    const res = await fetch('upload_product_pdo.php', {
      method: 'POST',
      body: fd
    });
    const json = await res.json();
    if (json.ok) {
      msg.textContent = json.msg || 'Producto guardado';
      msg.style.color = 'green';
      setTimeout(() => location.reload(), 1000);
    } else {
      msg.textContent = json.msg || 'Error al guardar';
      msg.style.color = 'red';
    }
  } catch (err) {
    console.error(err);
    msg.textContent = 'Error de conexión';
    msg.style.color = 'red';
  }
});
</script>


  <?php if ($mensaje): ?>
    <div class="msg"><?php echo htmlspecialchars($mensaje); ?></div>
  <?php endif; ?>

  <h2>Productos existentes</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Imagen</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($productos as $p): ?>
        <tr>
          <td><?php echo htmlspecialchars($p['id']); ?></td>
          <td><?php echo htmlspecialchars($p['nombre']); ?></td>
          <td>$<?php echo htmlspecialchars($p['precio']); ?></td>
          <td>
          <img src="serve_image_pdo.php?id=<?php echo $p['id']; ?>" alt="Imagen del producto" style="max-width:80px; border-radius:6px;">
          </td>

          <td>
            <a href="editar_producto.php?id=<?php echo $p['id']; ?>" class="edit">Editar</a>
            <form method="post" class="inline" onsubmit="return confirm('¿Eliminar este producto?')">
              <input type="hidden" name="eliminar_id" value="<?php echo $p['id']; ?>">
              <button type="submit" class="delete">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
