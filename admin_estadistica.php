<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) die("Error de conexión");

// Modo de visualización (año, mes, día)
$modo = $_GET['modo'] ?? 'mes';

switch($modo){
  case 'anio':
    $query = "SELECT YEAR(created_at) AS periodo, SUM(total) AS ventas 
              FROM orders 
              GROUP BY YEAR(created_at)";
    break;
  case 'dia':
    $query = "SELECT DATE(created_at) AS periodo, SUM(total) AS ventas 
              FROM orders 
              WHERE YEAR(created_at)=YEAR(CURDATE()) 
              GROUP BY DATE(created_at)";
    break;
  default: // mes
    $query = "SELECT MONTH(created_at) AS periodo, SUM(total) AS ventas 
              FROM orders 
              WHERE YEAR(created_at)=YEAR(CURDATE()) 
              GROUP BY MONTH(created_at)";
}

$res = $mysqli->query($query);
$periodos = [];
$ventas = [];
while($row = $res->fetch_assoc()){
    $periodos[] = $row['periodo'];
    $ventas[] = $row['ventas'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>📊 Estadísticas</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Arial; padding: 20px; background: #f9f9f9; }
    h2 { color: #8c6c46; }
    form { margin-bottom: 20px; }
    select, button { padding: 6px 10px; margin-right: 10px; }
  </style>
</head>
<body>
  <h2>Panel de Estadísticas</h2>

  <!-- Selector de modo -->
  <form method="get">
    <label>Ver estadísticas por:</label>
    <select name="modo">
      <option value="anio" <?php if($modo==='anio') echo 'selected'; ?>>Año</option>
      <option value="mes" <?php if($modo==='mes') echo 'selected'; ?>>Mes</option>
      <option value="dia" <?php if($modo==='dia') echo 'selected'; ?>>Día</option>
    </select>
    <button type="submit">Actualizar</button>
  </form>

  <!-- Gráfico -->
  <canvas id="graficoVentas"></canvas>
  <script>
    const ctx = document.getElementById('graficoVentas').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($periodos); ?>,
        datasets: [{
          label: 'Ventas',
          data: <?php echo json_encode($ventas); ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }]
      }
    });
  </script>
</body>
</html>
