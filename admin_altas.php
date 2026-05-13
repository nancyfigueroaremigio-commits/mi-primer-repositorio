<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) die("Error de conexión");

$mensaje = "";

// Alta proveedor
if (isset($_POST['alta_proveedor'])) {
  $nombre = trim($_POST['prov_nombre']);
  $contacto = trim($_POST['prov_contacto']);
  $telefono = trim($_POST['prov_telefono']);
  $email = trim($_POST['prov_email']);
  $entregan = trim($_POST['prov_entregan']);

  $stmt = $mysqli->prepare("INSERT INTO proveedores (nombre, contacto, telefono, email, entregan) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $nombre, $contacto, $telefono, $email, $entregan);
  $ok = $stmt->execute();
  $stmt->close();
  $mensaje = $ok ? "✅ Proveedor registrado" : "❌ Error al registrar proveedor";
}


// Alta cliente
if (isset($_POST['alta_cliente'])) {
  $nombre    = trim($_POST['cli_nombre']);
  $telefono  = trim($_POST['cli_telefono']);
  $email     = trim($_POST['cli_email']);
  $direccion = trim($_POST['cli_direccion']);
  $password  = $_POST['cli_password'];

  // Validar que no exista ya como usuario
  $check = $mysqli->prepare("SELECT id FROM usuarios WHERE correo = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $mensaje = "❌ Ya existe un usuario con ese correo.";
    $check->close();
  } else {
    $check->close();

    // Insertar en clientes
    $stmt1 = $mysqli->prepare("INSERT INTO clientes (nombre, telefono, email, direccion) VALUES (?, ?, ?, ?)");
    $stmt1->bind_param("ssss", $nombre, $telefono, $email, $direccion);
    $ok1 = $stmt1->execute();
    $stmt1->close();

    // Insertar en usuarios
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $rol = 'user';
    $stmt2 = $mysqli->prepare("INSERT INTO usuarios (nombre, correo, contraseña, telefono, role) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("sssss", $nombre, $email, $hash, $telefono, $rol);
    $ok2 = $stmt2->execute();
    $stmt2->close();

    $mensaje = ($ok1 && $ok2) ? "✅ Cliente y usuario registrados correctamente" : "❌ Error al registrar cliente o usuario";
  }
}


// Alta usuario
if (isset($_POST['alta_usuario'])) {
  $nombre = trim($_POST['usu_nombre']);
  $correo = trim($_POST['usu_correo']);
  $telefono = trim($_POST['usu_telefono']);
  $password = password_hash($_POST['usu_password'], PASSWORD_DEFAULT);
  $role = $_POST['usu_rol'];

  $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, correo, contraseña, telefono, role) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $nombre, $correo, $password, $telefono, $role);
  $ok = $stmt->execute();
  $stmt->close();
  $mensaje = $ok ? "✅ Usuario registrado" : "❌ Error al registrar usuario";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Altas administrativas</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f9f9f9; }
    h2 { color: #8c6c46; }
    form { margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 5px #ccc; }
    input, select { width: 100%; padding: 8px; margin-bottom: 10px; }
    button { background: #8c6c46; color: #fff; padding: 10px 14px; border: none; border-radius: 6px; cursor: pointer; }
    .msg { margin-bottom: 20px; font-weight: bold; color: #444; }
  </style>
  <div style="margin-top:20px; text-align:center;">
  <a href="index.php" style="background:#8c6c46; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none; font-weight:bold;">
    ← Volver al Home
  </a>
</div>

</head>
<body>
  

<h2>Panel de Altas</h2>
<?php if ($mensaje): ?><div class="msg"><?php echo $mensaje; ?></div><?php endif; ?>

<form method="post">
  <h3>Alta de Proveedor</h3>
  <input type="text" name="prov_nombre" placeholder="Nombre del proveedor" required>
  <input type="text" name="prov_contacto" placeholder="Nombre del contacto">
  <input type="tel" name="prov_telefono" placeholder="Teléfono">
  <input type="email" name="prov_email" placeholder="Email">
  <input type="text" name="prov_entregan" placeholder="Que entregan">
  <button type="submit" name="alta_proveedor">Registrar proveedor</button>
</form>

<form method="post">
  <h3>Alta de Cliente</h3>
  <input type="text" name="cli_nombre" placeholder="Nombre del cliente" required>
  <input type="tel" name="cli_telefono" placeholder="Teléfono">
  <input type="email" name="cli_email" placeholder="Correo" required>
  <input type="password" name="cli_password" placeholder="Contraseña de acceso" required>
  <input type="text" name="cli_direccion" placeholder="Dirección">
  <button type="submit" name="alta_cliente">Registrar cliente</button>
</form>

<form method="post">
  <h3>Alta de Usuario</h3>
  <input type="text" name="usu_nombre" placeholder="Nombre" required>
  <input type="email" name="usu_correo" placeholder="Correo" required>
  <input type="tel" name="usu_telefono" placeholder="Teléfono">
  <input type="password" name="usu_password" placeholder="Contraseña" required>
  <select name="usu_rol">
    <option value="user">Usuario</option>
    <option value="admin">Administrador</option>
  </select>
  <button type="submit" name="alta_usuario">Registrar usuario</button>
</form>

</body>
</html>
