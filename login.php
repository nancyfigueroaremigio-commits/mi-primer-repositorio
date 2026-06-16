<?php
// login.php
require_once 'auth.php';

// Si ya está autenticado, redirigir al index
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Config DB
$servidor = "localhost";
$usuario  = "root";
$clave    = "";
$basededatos = "ejemplo";

$mysqli = new mysqli($servidor, $usuario, $clave, $basededatos);
if ($mysqli->connect_errno) {
    die("Error de conexión a la base de datos");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !verify_csrf($_POST['csrf'])) {
        $error = 'Token inválido';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($email === '' || $password === '') {
            $error = 'Completa todos los campos';
        } else {
            if (!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION['captcha']) {
                $error = '❌ Captcha incorrecto';
            } else {
                $stmt = $mysqli->prepare("SELECT id, nombre, correo, contraseña, role FROM usuarios WHERE correo = ?");
                if ($stmt) {
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) {
                        if (password_verify($password, $row['contraseña'])) {
                            session_regenerate_id(true);
                            $_SESSION['user'] = [
                                'id' => (int)$row['id'],
                                'nombre' => $row['nombre'],
                                'correo' => $row['correo'],
                                'role' => $row['role']
                            ];
                            $_SESSION['last_activity'] = time();
                            header('Location: index.php');
                            exit;
                        } else {
                            $error = 'Credenciales incorrectas';
                        }
                    } else {
                        $error = 'Usuario no encontrado';
                    }
                    $stmt->close();
                } else {
                    $error = 'Error interno';
                }
            }
        }
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar sesión - Creaciones Mileth</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: linear-gradient(135deg, #f7f6f4, #e3d5c3);
      color: #222;
    }

    .card {
      max-width: 420px;
      margin: 24px auto;
      padding: 18px;
      border-radius: 8px;
      background: #fff;
      border: 1px solid #e3e0d8;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
      animation: fadeIn 0.9s ease;
    }
    .card:hover { transform: translateY(-4px); }
    h2 { color:#8c6c46; font-family: 'Playfair Display', serif; text-align:center; }
    label { display:block; margin:10px 0; font-size:14px; }
    input { width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; }
    button {
      background: linear-gradient(90deg, #8c6c46, #a07a50);
      color: #fff;
      border: none;
      padding: 10px 14px;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 12px;
      font-weight: bold;
      transition: background 0.3s ease;
    }
    button:hover { background:#a07a50; }
    .error { color:#c0392b; font-weight:600; margin-bottom:10px; }
    .actions { display:flex; justify-content:space-between; align-items:center; margin-top:12px; }
    a { color:#4d4537; text-decoration:none; }
    @keyframes fadeIn {
      from { opacity:0; transform:translateY(20px); }
      to { opacity:1; transform:translateY(0); }
    }
  </style>
</head>
<body>
  <div class="card">
    <img src="logo.png" alt="Logo Creaciones Mileth" style="width:80px; display:block; margin:0 auto 20px;">
    <h2>Iniciar sesión</h2>
    <hr style="border:0; height:2px; background:#8c6c46; margin:12px 0;">
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" action="login.php">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($token); ?>">

      <label>
        Correo
        <input type="email" name="email" required>
      </label>

      <label>
        Contraseña
        <div style="display:flex; align-items:center;">
          <input type="password" id="password" name="password" required style="flex:1;">
          <button type="button" onclick="togglePassword()" style="margin-left:6px;">👁️</button>
        </div>
      </label>

      <!-- Captcha externo -->
      <label>
        Ingresa el código:
        <?php include("captcha.php"); ?>
        <input type="text" name="captcha" placeholder="Código" required>
      </label>

      <div class="actions">
        <button type="submit">Entrar</button>
        <p style="margin-top:10px;">
          <a href="recuperar.php" style="color:#8c6c46; font-weight:600;">¿Olvidaste tu contraseña?</a>
        </p>
        <a href="index.php">Volver</a>
      </div>
    </form>
  </div>
<script>
function togglePassword() {
  const input = document.getElementById('password');
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>

