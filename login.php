<?php
// login.php
require_once 'auth.php';

// Si ya está autenticado, redirigir al index
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Config DB (ajusta si hace falta)
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
    // verificar CSRF
    if (!isset($_POST['csrf']) || !verify_csrf($_POST['csrf'])) {
        $error = 'Token inválido';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Completa todos los campos';
        } else {
            // Validar captcha
            if (!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION['captcha']) {
                $error = '❌ Captcha incorrecto';
            } else {
                // si el captcha es correcto, sigue con la consulta SQL
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
    body{font-family:Arial, sans-serif;margin:20px;background:#f7f6f4;color:#222;}
    .card{max-width:420px;margin:24px auto;padding:18px;border-radius:8px;background:#fff;border:1px solid #e3e0d8;}
    h2{color:#8c6c46;font-family: 'Playfair Display', serif;}
    label{display:block;margin:10px 0;font-size:14px;}
    input{width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;}
    button{background:#8c6c46;color:#fff;border:none;padding:10px 14px;border-radius:6px;cursor:pointer;margin-top:12px;}
    .error{color:#c0392b;font-weight:600;margin-bottom:10px;}
    .actions{display:flex;justify-content:space-between;align-items:center;margin-top:12px;}
    a{color:#4d4537;text-decoration:none;}
  </style>
</head>
<body>
  <div class="card">
    <h2>Iniciar sesión</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" action="login.php">
  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($token); ?>">

  <label>
    Correo
    <input type="email" name="email" required>
  </label>

  <label>
    Contraseña
    <input type="password" name="password" required>
  </label>

  <!-- Captcha matemático -->
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
</body>
</html>

