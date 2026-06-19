<?php
// index.php
require_once 'auth.php';
// ----------------------------------------
// 1. CONFIGURACIÓN Y CONEXIÓN A LA BD
// ----------------------------------------
$servidor    = "localhost";
$usuario     = "root";
$clave       = "";
$basededatos = "ejemplo";

// Evitar que mysqli lance excepciones automáticas (manejamos errores manualmente)
mysqli_report(MYSQLI_REPORT_OFF);

// Conectar a la base de datos
$enlace = mysqli_connect($servidor, $usuario, $clave, $basededatos);
if (!$enlace) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Variable para mensajes que se mostrarán al usuario
$mensaje = "";

// ----------------------------------------
// 2. PROCESAR ENVÍO DEL FORMULARIO DE REGISTRO
// ----------------------------------------
if (isset($_POST['registro'])) {
    $mensaje = '';
    $valid = true;

    // Validar CSRF
    if (!isset($_POST['csrf']) || !verify_csrf($_POST['csrf'])) {
        $mensaje = "❌ Token CSRF inválido. Recarga la página.";
        $valid = false;
    }

    // Capturar y sanitizar datos
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $role     = 'user'; // Rol por defecto
    $password = $_POST['contraseña'] ?? '';

    // Validar campos obligatorios
    if ($valid && ($nombre === '' || $correo === '' || $telefono === '' || $password === '')) {
        $mensaje = "❌ Todos los campos son obligatorios.";
        $valid = false;
    }

    // Validar correo
    if ($valid && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "❌ El correo no es válido.";
        $valid = false;
    }

    if ($valid) {
        // Verificar si el correo ya existe
        $stmt = $enlace->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
        if ($stmt) {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $mensaje = "❌ Ese correo ya está registrado.";
                $valid = false;
            }
        } else {
            $mensaje = "❌ Error al verificar el correo.";
            $valid = false;
        }
    }

    if ($valid) {
        // Encriptar contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar nuevo usuario
        $stmt = $enlace->prepare("INSERT INTO usuarios (nombre, correo, contraseña, telefono, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $nombre, $correo, $hash, $telefono, $role);
            if ($stmt->execute()) {
                $mensaje = "✅ Registro exitoso, bienvenido " . htmlspecialchars($nombre);
            } else {
                if ($stmt->errno === 1062) {
                    $mensaje = "❌ Ese correo ya está registrado.";
                } else {
                    $mensaje = "❌ Error al registrar: " . $stmt->error;
                    error_log("Error de registro: " . $stmt->error, 3, "errores.log");
                }
            }
            $stmt->close();
        } else {
            $mensaje = "❌ Error interno al preparar el registro.";
        }
    }

    echo htmlspecialchars($mensaje);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Creaciones Mileth</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&display=swap" rel="stylesheet">
  <!-- Añado Poppins con peso bold para el título de Quiénes Somos -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* ---------------------------
       Reset y tipografía
       --------------------------- */
    body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background-color: #e8ddcf;
  background-image: 
    radial-gradient(circle at 20% 20%, rgba(205, 172, 132, 0.3) 0%, transparent 60%),
    radial-gradient(circle at 80% 40%, rgba(176, 137, 97, 0.25) 0%, transparent 70%),
    radial-gradient(circle at 30% 80%, rgba(154, 121, 84, 0.2) 0%, transparent 70%);
  background-blend-mode: multiply;
  color: #3d3323;
  overflow-x: hidden;
}
section {
  display: none;
}

section.active {
  display: block;
}

section.active {
  display: block;
}

h2, h3 {
  color: #8c6c46;
}
.register-section {
  background-color: rgba(255, 250, 245, 0.95);
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  padding: 30px 20px;
  margin: 30px auto;
  max-width: 1000px;
}


p, td, th, label {
  color: #4d4537;
}
table {
  width: 100%;
  border-collapse: collapse;
  background: #fffdf8;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  margin-top: 20px;
}

th, td {
  padding: 10px;
  border-bottom: 1px solid #e6e2d8;
  text-align: left;
  color: #4d4537;
}

thead {
  background-color: #f5ede3;
}
.register-section .cta {
  margin-top: 20px;
}

/* ---------------------------
   Quiénes Somos — diseño en dos columnas
   --------------------------- */
.about-section {
  display: flex;
  gap: 30px;
  align-items: stretch;
  max-width: 1200px;
  margin: 40px auto;
  padding: 30px;
  background: transparent;
}
.about-left, .about-right {
  flex: 1 1 50%;
  min-width: 280px;
  border-radius: 12px;
  overflow: hidden;
}
.about-left {
  display:flex;
  align-items:center;
  justify-content:center;
}
.about-left .about-image {
  width:100%;
  height:600px;
  object-fit:cover;
  border-radius:12px;
  box-shadow:0 8px 30px rgba(0,0,0,0.15);
}

/* Parte derecha con fondo beige claro */
.about-right {
  background: #F8F5F0;
  padding: 55px;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

/* Título grande y elegante */
.about-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  color: #A88434; /* café dorado solicitado */
  font-size: 42px;
  margin: 0 0 18px 0;
  line-height: 1.05;
}

/* Texto descriptivo */
.about-text {
  color: #3e3a33; /* gris oscuro cálido */
  font-size: 16px;
  line-height: 1.75;
  margin-bottom: 18px;
  text-align: left;
  letter-spacing: 0.1px;
}

/* Pequeño detalle estético */
.about-divider {
  width: 60px;
  height: 4px;
  background: linear-gradient(90deg, rgba(168,132,52,1), rgba(140,108,70,0.9));
  margin: 18px 0;
  border-radius: 3px;
}

/* Responsive: en pantallas pequeñas apilar columnas */
@media (max-width: 900px) {
  .about-section { flex-direction: column; gap:18px; padding: 0 12px; }
  .about-left .about-image { height: 320px; }
  .about-right { padding: 28px; }
  .about-title { font-size: 30px; }
}
/* Estilos adicionales para Quiénes Somos */

#about {
  background: #f5f2ed;
  padding: 50px 20px;
}

.about-right {
  border-left: 5px solid #A88434;
}

.about-left .about-image {
  transition: transform 0.4s ease;
}

.about-left .about-image:hover {
  transform: scale(1.03);
}


/* ---------------------------
   Resto de estilos existentes (no modificados)
   --------------------------- */
    /* ---------------------------
       Encabezado y navegación
       --------------------------- */
    header {
      background: linear-gradient(to right, #645444, #c5ae92);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      padding: 10px 20px;
      border-bottom: 1px solid #ccc;
    }
    .logo-title { display:flex; align-items:center; gap:16px; }
    .logo-title img { height:125px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.2); transition:transform .3s; }
    .logo-title img:hover { transform:scale(1.05); }
    .title-header { font-family:'Playfair Display', serif; font-size:28px; color:#fff; margin:0; }

    nav { display:flex; gap:20px; flex-wrap:wrap; }
    nav a { text-decoration:none; font-size:14px; color:#000; font-weight:600; cursor:pointer; }
    nav a:hover { color:#8c6c46; }

    /* ---------------------------
       Secciones
       --------------------------- */
    section { display:none; padding:20px; background-color:#f1f0ee; }
    section.active { display:block; }

    /* ---------------------------
       Alert (mensaje) — aparece y desaparece
       --------------------------- */
    .alert {
      max-width:400px;
      margin:20px auto;
      padding:10px;
      border:1px solid #f5c6cb;
      background-color:#fdecea;
      color:#611a15;
      border-radius:4px;
      text-align:center;
      font-weight:600;
      opacity:1;
      transition:opacity .5s ease;
    }
    .alert.hidden { opacity:0; }

    /* ---------------------------
       Home
       --------------------------- */
    .home-text { font-family:'Playfair Display', serif; font-size:16px; font-style:italic; color:#4d4537; text-align:center; margin-bottom:10px; }
    #home {
  max-width: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  background: transparent;
  border-radius: 0;
  box-shadow: none;
}

.home-banner {
  width: 100vw;
  height: 500px;
  margin: 0;
  overflow: hidden;
  border-radius: 0;
  box-shadow: none;
  border: none;
}

.home-banner img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.3s ease;
  filter: brightness(0.85) contrast(1.1);
  animation: zoom 20s infinite alternate ease-in-out;
}

.home-banner img:hover {
  transform: scale(1.02);
}

.home-slogan {
  text-align: center;
  font-family: 'Playfair Display', serif;
  font-size: 22px;
  color: #4d4537;
  margin: 10px 0 20px;
}

@keyframes zoom {
  0% { transform: scale(1); }
  100% { transform: scale(1.1); }
}

    /* ---------------------------
       Galería de productos
       --------------------------- */
    .featured-products { text-align:center; margin-top:40px; }
    .featured-products h3 { font-family:'Playfair Display', serif; font-size:22px; color:#8c6c46; margin-bottom:20px; }
    .product-grid {
  display: flex;
  justify-content: center;
  gap: 40px;
  flex-wrap: wrap;
}

.product-card {
  background: #fffdf8;
  border-radius: 14px;
  box-shadow: 0 6px 14px rgba(0,0,0,0.15);
  overflow: hidden;
  width: 240px;
  transition: transform 0.4s ease, box-shadow 0.4s ease;
  text-align: center;
  margin-bottom: 30px;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}

.product-card img {
  width: 100%;
  height: 230px;
  object-fit: cover;
}

.product-card p {
  padding: 12px;
  color: #5a4b38;
  font-weight: 500;
  font-size: 15px;
}


    /* ---------------------------
       Contacto / Registro / Login
       --------------------------- */
    .contact-section, .register-section, .log-section { max-width:400px; margin:0 auto; text-align:center; }
    .contact-section h2, .register-section h2, .log-section h2 { color:#8c6c46; font-family:'Playfair Display', serif; font-size:28px; margin-bottom:20px; }
    form { display:flex; flex-direction:column; gap:14px; }
    input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
      padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; font-style:italic; color:#4d4537; width:100%; box-sizing:border-box;
    }
    button.cta,
button[type="submit"] {
  background-color: #8c6c46;
  color: #fffaf0;
  border: none;
  padding: 10px 16px;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  transition: background-color 0.3s ease;
}

button.cta:hover,
button[type="submit"]:hover {
  background-color: #6e5634;
}


/*------------------------------
SIDEBAR (NUEVO)
------------------------------*/

.sidebar {
  position: fixed;
  top: 0;
  right: -250px; /* Comienza oculto a la derecha */
  width: 250px;
  height: 100%;
  background: #fff;
  box-shadow: -4px 0 12px rgba(0,0,0,0.15);
  border-left: 1px solid #e6e2d8;
  z-index: 1000;
  transition: right 0.3s ease;
  display: flex;
  flex-direction: column;
  padding: 20px;
  box-sizing: border-box;
}

.sidebar.open {
  right: 0; /* Muestra el sidebar */
}

.sidebar h2 {
  font-size: 18px;
  margin-bottom: 15px;
  color: #8c6c46;
}

.sidebar a {
  text-decoration: none;
  color: #4d4537;
  padding: 10px;
  border-radius: 4px;
  transition: background 0.3s ease, color 0.3s ease;
  display: block;
}

.sidebar a:hover {
  background: #f1f0ee;
  color: #8c6c46;
}

.sidebar .close-btn {
  background: transparent;
  border: none;
  color: #4d4537;
  font-size: 18px;
  cursor: pointer;
  margin-bottom: 20px;
}

/* Botón para abrir el sidebar */
.open-sidebar-btn {
  background: #8c6c46;
  color: #fff;
  border: none;
  padding: 10px 16px;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  z-index: 1001;
  margin: 10px;
}

.open-sidebar-btn:hover {
  background: #6e5634;
}

/* Fin SIDEBAR */

  </style>
  <script>
  const usuarioAutenticado = <?php echo is_logged_in() ? 'true' : 'false'; ?>;
</script>
</head>
<body>
  <?php if ($mensaje): ?>
  <div class="alert" id="mensaje-usuario"><?php echo htmlspecialchars($mensaje); ?></div>
 <?php endif; ?>

  

  <header>
  <div class="logo-title" onclick="showSection('home')" style="cursor:pointer">
    <img src="logo2.png" alt="Logo Cerámica Mileth">
    <h1 class="title-header">Creaciones Mileth</h1>
  </div>

  <nav>
    <a onclick="showSection('home')">Inicio</a>
    <a onclick="showSection('about')">Quiénes Somos</a>
    <!-- Catálogo: enlace que abre la sección del catálogo -->
    <a onclick="showSection('catalogo')">Catálogo</a>
    <a onclick="showSection('contact')">Contacto</a>

    <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
      <?php
$icono = current_user()['perfil_icono'] ?? '';
$ruta = ($icono && file_exists($icono)) ? $icono : 'img/Iconopred.png';

?>
<div style="display:flex; flex-direction:column; align-items:center; margin-right:10px;">
  <img src="mostrar_icono.php?v=<?php echo time(); ?>" 
     alt="Perfil" 
     class="icono-perfil" 
     onclick="showSection('mi_cuenta')" 
     style="cursor:pointer;">
  <a onclick="showSection('mi_cuenta')" 
     style="font-size:13px; font-weight:600; color:#4d4537; text-decoration:none; margin-top:4px;">
     Mi cuenta
  </a>
</div>


            

      <?php if (function_exists('current_role') && current_role() === 'admin'): ?>
  <a href="admin_orders.php">Pedidos</a>
  <a href="admin_altas.php">Altas</a>
  <a href="admin_products.php">Productos</a>
  <a href="admin_users.php">Usuarios</a>
  <a href="admin_estadisticas.php">Estadísticas</a>
<?php endif; ?>

      


      <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars(current_user()['nombre'] ?? ''); ?>)</a>
    <?php else: ?>
      <a href="login.php">Iniciar Sesión</a>
      <a onclick="showSection('register')">Registro</a>
    <?php endif; ?>
  </nav>
</header>

  <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
    <?php
    $pedidos_usuario = [];
if (is_logged_in()) {
    $usuario_id = current_user()['id'];
    $stmt = $enlace->prepare("SELECT id, total, telefono, email, created_at, estado FROM orders WHERE usuario_id = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $pedidos_usuario = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}?>
  <!-- Sección Mi cuenta visible solo para usuarios autenticados -->
   
  <section id="mi_cuenta" class="register-section">
    

    <h2>Mi cuenta</h2>
    <p>Bienvenido, <strong><?php echo htmlspecialchars(current_user()['nombre'] ?? ''); ?></strong></p>
    <p><strong>Correo:</strong> <?php echo htmlspecialchars(current_user()['correo'] ?? ''); ?></p>
    <p><strong>Rol:</strong> <?php echo htmlspecialchars(current_role() ?? 'user'); ?></p>
    <form action="upload_icono.php" method="post" enctype="multipart/form-data">
  <label for="icono">Subir icono de perfil:</label>
  <input type="file" name="icono" id="icono" accept="image/*" required>
  <button type="submit">Actualizar icono</button>
</form>
    <h3 style="margin-top:30px;">Mis pedidos</h3>
    
<?php if (empty($pedidos_usuario)): ?>
  <p>No has realizado ningún pedido aún.</p>
<?php else: ?>
  <table style="width:100%; border-collapse:collapse; background:#fff; margin-top:10px;">
    
    <thead>
      <tr>
        
        <th style="padding:8px; border:1px solid #ccc;">ID</th>
        <th style="padding:8px; border:1px solid #ccc;">Total</th>
        <th style="padding:8px; border:1px solid #ccc;">Teléfono</th>
        <th style="padding:8px; border:1px solid #ccc;">Email</th>
        <th style="padding:8px; border:1px solid #ccc;">Fecha</th>
        <th style="padding:8px; border:1px solid #ccc;">Estado</th>
        <th style="padding:8px; border:1px solid #ccc;">Acción</th>


      </tr>
    </thead>
    <tbody>
      <?php foreach ($pedidos_usuario as $p): ?>
        <tr>
          <td style="padding:8px; border:1px solid #ccc;">
            <a href="mi_pedido.php?order_id=<?php echo urlencode($p['id']); ?>">
              <?php echo htmlspecialchars($p['id']); ?>
            </a>
          </td>
          <td style="padding:8px; border:1px solid #ccc;">$<?php echo htmlspecialchars($p['total']); ?></td>
          <td style="padding:8px; border:1px solid #ccc;"><?php echo htmlspecialchars($p['telefono']); ?></td>
          <td style="padding:8px; border:1px solid #ccc;"><?php echo htmlspecialchars($p['email']); ?></td>
          <td style="padding:8px; border:1px solid #ccc;"><?php echo htmlspecialchars($p['created_at']); ?></td>
          <td style="padding:8px; border:1px solid #ccc;">
  <?php if ($p['estado'] === 'pendiente'): ?>
    <form method="post" onsubmit="return cancelarPedido(event, <?php echo $p['id']; ?>)">
      <button type="submit" style="background:#c0392b; color:#fff; border:none; padding:6px 10px; border-radius:4px; cursor:pointer;">
        Cancelar
      </button>
    </form>
  <?php else: ?>
    —
  <?php endif; ?>
</td>

          <?php
$estado = strtolower($p['estado']);
$color = match ($estado) {
    'pendiente'  => '#f39c12', // naranja
    'procesado'  => '#3498db', // azul
    'enviado'    => '#27ae60', // verde
    'cancelado'  => '#c0392b', // rojo
    default      => '#7f8c8d', // gris
};
?>
<td style="padding:8px; border:1px solid #ccc; color:<?php echo $color; ?>; font-weight:bold;">
  <?php echo htmlspecialchars($p['estado']); ?>
</td>


        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
<?php endif; ?>


    <div style="margin-top:16px;">
      <!-- Catálogo: botón dentro de Mi cuenta que lleva a la sección del catálogo -->
      <button class="cta" onclick="showSection('catalogo')">Ir al catálogo</button>
      <a href="logout.php" style="margin-left:12px; color:#4d4537; font-weight:600; text-decoration:none;">Cerrar sesión</a>
    </div>
  </section>

  <?php if (function_exists('current_role') && current_role() === 'admin'): ?>
    <!-- Panel rápido para administradores -->
    <section id="admin" style="max-width:800px;margin:20px auto;padding:12px;background:#fff;border-radius:8px;border:1px solid #e6e2d8;">
      <h2 style="color:#8c6c46;font-family:'Playfair Display',serif;margin-top:0;">Panel Admin</h2>
      <p>Accesos rápidos:</p>
      <ul>
        <li><a href="admin_orders.php">Ver pedidos</a></li>
        <li><a href="admin_products.php">Gestionar productos</a></li>
        <li><a href="admin_users.php">Gestionar usuarios</a></li>
        <li><a href="admin_products.php">Agregar productos</a></li>

      </ul>
    </section>
  <?php endif; ?>

<?php else: ?>
  <!-- Contenido para visitantes no autenticados -->
  <section id="bienvenida-publica" class="home-text" style="text-align:center; padding:14px 20px;">
    <p>Regístrate o inicia sesión para acceder a tu cuenta, historial de pedidos y opciones administrativas si corresponde.</p>
    <div style="margin-top:12px;">
      <a href="login.php" class="cta" style="text-decoration:none; padding:8px 14px; display:inline-block;">Iniciar sesión</a>
      <button class="cta" onclick="showSection('register')" style="margin-left:10px;">Registrarse</button>
    </div>
  </section>
<?php endif; ?>



    <!-- HOME (si no lo tienes duplicado, deja uno solo) -->
  <section id="home" class="active">

  <div class="home-text">
    <p>"Tu espacio merece un detalle hecho a mano."</p>
  </div>

  <div class="home-banner">
    <img src="Fondo.png" alt="Cerámica en proceso de moldeado">
  </div>

  <div class="home-slogan">
    Transformamos barro en creaciones únicas
  </div>

  <div class="featured-products">
    <h3>Algunas de nuestras creaciones</h3>

    <div class="product-grid" id="creaciones-destacadas">
      <!-- Se llenará dinámicamente -->
    </div>
  </div>

  <div style="
    margin-top:60px;
    background:#f5f3ef;
    padding:50px;
    border-radius:14px;
  ">

    <div style="
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:40px;
      flex-wrap:wrap;
    ">

      <div style="flex:1; min-width:300px;">
        <h2 style="
          font-size:38px;
          color:#2d241b;
          line-height:1.3;
          font-family:'Poppins', sans-serif;
          font-weight:600;
        ">
          Contamos con impresión de logo o nombres
          para hacer tu artículo memorable
        </h2>
      </div>

      <div style="flex:1; display:flex; justify-content:center;">
        <img src="imagenpromocional5.jpg"
             alt="Cerámica personalizada"
             style="
               width:100%;
               max-width:600px;
               border-radius:12px;
             ">
      </div>

    </div>

  </div>

  </section> <!-- ← AGREGA ESTE CIERRE -->

<!-- QUIÉNES SOMOS -->
<section id="about" class="about-section">

  <div class="about-left">
    <img class="about-image"
         src="quienessomos.jpg"
         alt="Artesanía en barro">
  </div>

  <div class="about-right">
    <h2 class="about-title">Creaciones Mileth</h2>

    <div class="about-divider"></div>

    <p class="about-text">
      Creaciones Mileth es un emprendimiento dedicado a la elaboración y comercialización de piezas artesanales de barro, creadas con dedicación, creatividad y respeto por las tradiciones mexicanas. Cada producto refleja el trabajo manual y el valor cultural que caracteriza a la artesanía de nuestra región.
    </p>

    <p class="about-text">
      Nuestro objetivo es preservar y compartir la belleza del arte en barro a través de piezas únicas que combinan tradición y calidad. En Creaciones Mileth buscamos ofrecer productos que decoren espacios, cuenten historias y mantengan viva una de las expresiones artesanales más representativas de México.
    </p>

  </div>

</section>

  


  <!-- REGISTRO -->
  <?php $token = csrf_token(); ?>
<section id="register" class="register-section">
  <h2>Registro</h2>
  <form action="index.php" method="post">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($token); ?>">
    <input type="text"     name="nombre"       placeholder="Nombre"      required>
    <input type="email"    name="correo"       placeholder="Correo"      required>
    <input type="tel"      name="telefono"     placeholder="Teléfono"    required>
    <input type="password" name="contraseña"   placeholder="Contraseña"  required>
    <button type="submit"  name="registro" class="cta">Registrarse</button>
  </form>
</section>


  <!-- INICIO DE SESIÓN (placeholder) -->
  <section id="log" class="log-section">
    <h2>Iniciar Sesión</h2>
    <!-- Si usas login.php separado, mantén aquí solo información o un enlace -->
    <p>Usa <a href="login.php">la página de inicio de sesión</a> para acceder.</p>
  </section>

  <!-- SCRIPTS: catálogo, carrito y checkout -->
  <script>
    let intervaloDestacados = null;

async function cargarCreacionesDestacadas() {
  try {
    const res = await fetch('productos_destacados.php');
    const destacados = await res.json();
    const grid = document.getElementById('creaciones-destacadas');
    if (!grid) return;

    grid.innerHTML = '';
    destacados.forEach(p => {
      const card = document.createElement('div');
      card.className = 'product-card';
      card.innerHTML = `
        <img src="serve_image_pdo.php?id=${p.id}" alt="${p.nombre}" style="cursor:pointer;" onclick='abrirModalProducto(${JSON.stringify(p)})'>
        <p>${p.nombre}</p>
        <p style="font-weight:600;color:#4d4537;">$${p.precio}</p>
      `;
      grid.appendChild(card);
    });
  } catch (e) {
    console.error('Error al cargar creaciones destacadas', e);
  }
}

function iniciarRotacionDestacados() {
  cargarCreacionesDestacadas(); // primera carga
  intervaloDestacados = setInterval(cargarCreacionesDestacadas, 15000); // cada 15 segundos
}

window.addEventListener('DOMContentLoaded', () => {
  // Catálogo: inicializa la carga de productos del catálogo
  cargarProductos();
  actualizarCarrito();
  iniciarRotacionDestacados(); // ⏱️ activa la rotación
  const btn = document.getElementById('checkout-btn');
  if (btn) btn.addEventListener('click', abrirModalPago);
});


    // Manejo de secciones
    function showSection(id) {
      document.querySelectorAll('section').forEach(s => s.classList.remove('active'));
      const el = document.getElementById(id);
      if (el) el.classList.add('active');
      window.scrollTo(0, 0);
    }

    // Auto ocultar mensaje
    window.addEventListener('DOMContentLoaded', () => {
  const alerta = document.getElementById('mensaje-usuario');
  if (alerta) {
    setTimeout(() => {
      alerta.classList.add('hidden');
      alerta.addEventListener('transitionend', () => alerta.remove());
    }, 5000);
  }

  cargarProductos(); // ✅ esta función es async y hace await internamente
  actualizarCarrito();

  const btn = document.getElementById('checkout-btn');
  if (btn) btn.addEventListener('click', abrirModalPago);

});





    // Catálogo
    let productos = [];

    // Catálogo: obtiene los datos desde productos_api.php
async function cargarProductos() {
  try {
    const res = await fetch('productos_api.php');
    productos = await res.json(); // ✅ ESTA LÍNEA ES CLAVE
    console.log('Productos cargados:', productos);
    renderCatalogo();
  } catch (e) {
    console.error('Error al cargar productos', e);
  }
}

function renderCatalogo() {
  const grid = document.getElementById('catalogo-grid');
  if (!grid) return;
  grid.innerHTML = '';

  productos.forEach(p => {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.id = 'product-' + p.id;

    card.innerHTML = `
      <img src="serve_image_pdo.php?id=${p.id}" alt="${p.nombre}" style="cursor:pointer;" onclick='abrirModalProducto(${JSON.stringify(p)})'>

      <p>${p.nombre}</p>
      <p style="font-weight:600;color:#4d4537;">$${p.precio}</p>
    `;

    const btn = document.createElement('button');
    btn.textContent = 'Agregar al carrito';
    btn.addEventListener('click', () => agregarAlCarrito(p.id));
    card.appendChild(btn);

    grid.appendChild(card);
  });
}



    // Carrito (memoria + localStorage)
    let carrito = [];
    try {
      const stored = localStorage.getItem('carrito_mileth');
      if (stored) carrito = JSON.parse(stored);
    } catch (e) { carrito = []; }

    function guardarCarritoLocalStorage(){ try{ localStorage.setItem('carrito_mileth', JSON.stringify(carrito)); }catch(e){} }
    function agregarAlCarrito(productId) {
  const producto = productos.find(p => Number(p.id) === Number(productId));
  if (!producto) return;

  const existente = carrito.find(i => Number(i.id) === Number(productId));
  if (existente) {
    existente.cantidad += 1;
  } else {
    carrito.push({
      id: producto.id,
      nombre: producto.nombre,
      precio: producto.precio,
      cantidad: 1
    });
  }

  guardarCarritoLocalStorage();
  actualizarCarrito();
}


    function quitarDelCarrito(productId) {
  carrito = carrito.filter(i => Number(i.id) !== Number(productId));
  guardarCarritoLocalStorage();
  actualizarCarrito();
}


    function disminuirCantidad(productId){ 
      const it=carrito.find(i=>i.id===productId); if(!it) return; it.cantidad--; if(it.cantidad<=0) quitarDelCarrito(productId); else { guardarCarritoLocalStorage(); actualizarCarrito(); } }
    function aumentarCantidad(productId){ const it=carrito.find(i=>i.id===productId); if(!it) return; it.cantidad++; guardarCarritoLocalStorage(); actualizarCarrito(); }

    function actualizarCarrito(){
  const lista = document.getElementById('lista-carrito'); if(!lista) return;
  lista.innerHTML = '';
  if(carrito.length === 0){
    lista.innerHTML = '<li>El carrito está vacío</li>';
    document.getElementById('carrito-total').textContent = '';
    console.log('Carrito actualizado:', carrito); // ✅ aquí también es válido
    return;
  }

  let total = 0;
  carrito.forEach(item => {
    total += item.precio * item.cantidad;
    const li = document.createElement('li');
    li.innerHTML = `
      <span>${item.nombre} — $${item.precio} x ${item.cantidad}</span>
      <span>
        <input type="number" min="1" value="${item.cantidad}" style="width:50px;" onchange="actualizarCantidad(${item.id}, this.value)">
        <button class="remove" onclick="quitarDelCarrito(${item.id})">Eliminar</button>
      </span>
    `;
    lista.appendChild(li);
  });

  document.getElementById('carrito-total').textContent = 'Total: $' + total;

  // ✅ Aquí es donde lo necesitas
  console.log('Carrito actualizado:', carrito);
}


    function actualizarCantidad(productId, nuevaCantidad) {
  const cantidad = parseInt(nuevaCantidad);
  if (isNaN(cantidad) || cantidad <= 0) return;
  const item = carrito.find(i => Number(i.id) === Number(productId));

  if (item) {
    item.cantidad = cantidad;
    guardarCarritoLocalStorage();
    actualizarCarrito();
  }
}


    // Abrir catálogo y resaltar producto
    function openProduct(productId){
      // Catálogo: muestra la sección del catálogo y resalta el producto
      renderCatalogo();
      showSection('catalogo');
      const target = document.getElementById('product-' + productId);
      if(!target) return;
      target.scrollIntoView({behavior:'smooth', block:'center'});
      target.classList.add('product-highlight');
      setTimeout(()=>target.classList.remove('product-highlight'), 1500);
    }

    // Enviar pedido al servidor (place_order.php)
    async function placeOrder(){
      if(!carrito || carrito.length === 0){ alert('El carrito está vacío'); return; }
      const buyerEmail = prompt('Introduce tu correo para el pedido (opcional):', '') || null;
      const buyerTelefono = prompt('Introduce tu teléfono para el pedido (opcional):', '') || null;
      const payload = { cart: carrito, email: buyerEmail, telefono: buyerTelefono, usuario_id: null };
      try {
        const res = await fetch('place_order.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.ok) {
          alert('Pedido enviado. ID: ' + json.order_id);
          carrito = [];
          guardarCarritoLocalStorage();
          actualizarCarrito();
        } else {
          alert('Error: ' + json.msg);
        }
      } catch (e){
        console.error(e);
        alert('Error al conectar con el servidor');
      }
    }
    function cancelarPedido(event, orderId) {
  event.preventDefault();
  if (!confirm("¿Estás seguro de cancelar este pedido?")) return false;

  fetch('cancelar_pedido.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'order_id=' + encodeURIComponent(orderId)
  })
  .then(res => res.json())
  .then(data => {
    alert(data.msg);
    if (data.ok) location.reload();
  })
  .catch(err => {
    console.error(err);
    alert('Error al cancelar el pedido');
  });

  return false;
}

function abrirModalPago() {
  if (!usuarioAutenticado) {
    alert('Debes iniciar sesión para realizar una compra.');
    showSection('log'); // opcional: redirige al login
    return;
  }
  document.getElementById('modal-pago').style.display = 'block';
}


function cerrarModal() {
  document.getElementById('modal-pago').style.display = 'none';
}

async function enviarPedido() {
if (!usuarioAutenticado) {
    alert('Debes iniciar sesión para confirmar tu pedido.');
    showSection('log');
    return;
  }
  const email = document.getElementById('pago-correo').value.trim();
  const telefono = document.getElementById('pago-telefono').value.trim();
  const direccion = document.getElementById('pago-direccion').value.trim();
  const nombre = document.getElementById('pago-nombre').value.trim();
  const numero = document.getElementById('pago-numero').value.trim();
  const expiracion = document.getElementById('pago-expiracion').value.trim();
  const cvv = document.getElementById('pago-cvv').value.trim();

  if (direccion === '' || nombre === '' || numero === '' || expiracion === '' || cvv === '') {
    alert('Por favor completa todos los campos obligatorios de pago.');
    return;
  }

  // 1️⃣ Crear el pedido primero
  const pedidoPayload = {
    cart: carrito,
    email,
    telefono,
    usuario_id: null // si estás autenticado, puedes enviar el ID aquí
  };

  let orderId = null;
  try {
    const resPedido = await fetch('place_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(pedidoPayload)
    });
    const jsonPedido = await resPedido.json();
    if (!jsonPedido.ok || !jsonPedido.order_id) {
      alert('❌ Error al crear el pedido: ' + jsonPedido.msg);
      return;
    }
    orderId = jsonPedido.order_id;
    console.log('Pedido creado con ID:', orderId);
  } catch (e) {
    console.error(e);
    alert('❌ Error al conectar con el servidor de pedidos');
    return;
  }

  // 2️⃣ Enviar los datos de pago con el order_id
  const pagoPayload = {
    order_id: orderId,
    email,
    telefono,
    direccion,
    tarjeta: { nombre, numero, expiracion, cvv }
  };

  try {
    const resPago = await fetch('guardar_pago.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(pagoPayload)
    });
    const jsonPago = await resPago.json();
    if (jsonPago.ok) {
      alert('✅ Pedido confirmado. Gracias por tu compra.');
      carrito = [];
      guardarCarritoLocalStorage();
      actualizarCarrito();
      cerrarModal();
    } else {
      alert('❌ Error al guardar el pago: ' + jsonPago.msg);
    }
  } catch (err) {
    console.error(err);
    alert('❌ Error de conexión al guardar el pago');
  }
}
function abrirModalProducto(producto) {
  console.log('Producto recibido:', producto);
  document.getElementById('modal-img').src = `serve_image_pdo.php?id=${producto.id}`;
  document.getElementById('modal-nombre').textContent = producto.nombre;
  document.getElementById('modal-precio').textContent = '$' + producto.precio;
  document.getElementById('modal-descripcion').textContent = producto.descripcion || 'Sin descripción';
  document.getElementById('modal-producto').style.display = 'block';
}

function cerrarModalProducto() {
  document.getElementById('modal-producto').style.display = 'none';
}

/*------------------------------
FUNCIONES DEL SIDEBAR (NUEVO)
------------------------------*/
function abrirSidebar() {
  document.querySelector('.sidebar').classList.add('open');
  document.body.style.overflow = 'hidden'; /* Previene el scroll del fondo */
}

function cerrarSidebar() {
  document.querySelector('.sidebar').classList.remove('open');
  document.body.style.overflow = 'auto'; /* Restaura el scroll del fondo */
}

/* Evento para cerrar el sidebar al hacer clic en un enlace */
document.querySelectorAll('.sidebar a').forEach(link => {
  link.addEventListener('click', () => {
    cerrarSidebar();
  });
});

/* Fin funciones SIDEBAR */

/* Menu responsive: abrir/cerrar sidebar */
document.querySelector('.open-sidebar-btn').addEventListener('click', abrirSidebar);

/* Cerrar sidebar al hacer clic fuera de él */
window.addEventListener('click', (e) => {
  const sidebar = document.querySelector('.sidebar');
  if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !document.querySelector('.open-sidebar-btn').contains(e.target)) {
    cerrarSidebar();
  }
});
  </script>
  <!-- Modal de pago -->
<div id="modal-pago" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;">
  <div style="background:#fff; max-width:400px; margin:60px auto; padding:20px; border-radius:8px; position:relative;">
    <h3 style="margin-top:0;">Confirmar pedido</h3>
    <label>Correo alternativo(opcional)</label>
    <input type="email" id="pago-correo" style="width:100%; margin-bottom:10px;">
    <label>Teléfono alternativo(opcional)</label>
    <input type="tel" id="pago-telefono" style="width:100%; margin-bottom:10px;">
    <label>Dirección de entrega</label>
    <input type="text" id="pago-direccion" required style="width:100%; margin-bottom:10px;">
    <label>Nombre en la tarjeta</label>
    <input type="text" id="pago-nombre" style="width:100%; margin-bottom:10px;">
    <label>Número de tarjeta</label>
    <input type="text" id="pago-numero" maxlength="16" style="width:100%; margin-bottom:10px;">
    <label>Expiración (MM/AA)</label>
    <input type="text" id="pago-expiracion" placeholder="MM/AA" style="width:100%; margin-bottom:10px;">
    <label>CVV</label>
    <input type="text" id="pago-cvv" maxlength="4" style="width:100%; margin-bottom:10px;">
    <button onclick="enviarPedido()" style="background:#8c6c46; color:#fff; border:none; padding:10px 14px; border-radius:6px; cursor:pointer;">Finalizar pedido</button>
    <button onclick="cerrarModal()" style="margin-left:10px; background:#ccc; border:none; padding:10px 14px; border-radius:6px; cursor:pointer;">Cancelar</button>
  </div>
</div>
<!-- Modal de producto ampliado -->
<div id="modal-producto" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999;">
  <div style="background:#fff; max-width:600px; margin:60px auto; padding:20px; border-radius:10px; position:relative;">
    <span onclick="cerrarModalProducto()" style="position:absolute; top:10px; right:20px; cursor:pointer; font-size:20px;">✖</span>
    <img id="modal-img" src="" style="width:100%; border-radius:8px;">
    <h3 id="modal-nombre" style="margin-top:10px; color:#8c6c46;"></h3>
    <p id="modal-precio" style="font-weight:bold; color:#4d4537;"></p>
    <p id="modal-descripcion" style="font-style:italic; color:#4d4537;"></p>
  </div>
</div>
<!-- SIDEBAR (NUEVO) -->
<div class="sidebar">
  <button class="close-btn" onclick="cerrarSidebar()">✖</button>
  <h2>Menú</h2>
  <a href="javascript:void(0)" onclick="showSection('home'); cerrarSidebar()">Inicio</a>
  <a href="javascript:void(0)" onclick="showSection('about'); cerrarSidebar()">Quiénes Somos</a>
  <a href="javascript:void(0)" onclick="showSection('catalogo'); cerrarSidebar()">Catálogo</a>
  <a href="javascript:void(0)" onclick="showSection('contact'); cerrarSidebar()">Contacto</a>
  
  <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
    <a href="javascript:void(0)" onclick="showSection('mi_cuenta'); cerrarSidebar()">Mi cuenta</a>
    <a href="logout.php">Cerrar sesión</a>   
  <?php else: ?>
    <a href="login.php">Iniciar Sesión</a>
    <a href="javascript:void(0)" onclick="showSection('register'); cerrarSidebar()">Registro</a>
  <?php endif; ?>
</div>

<!-- Botón flotante para abrir el sidebar en móviles -->
<button class="open-sidebar-btn">
  ☰ Abrir menú
</button>


</body>
</html>
