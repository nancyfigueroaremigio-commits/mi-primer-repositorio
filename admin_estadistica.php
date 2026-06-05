<?php
require_once 'auth.php';
require_login();
require_role('admin');

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) die("Error de conexión");

// Ejemplo: Ventas por mes
$query = "SELECT MONTH(created_at) AS mes, SUM(total) AS ventas
          FROM orders
          WHERE YEAR(created_at) = YEAR(CURDATE())
          GROUP BY MONTH(created_at)";
$res = $mysqli->query($query);

$meses = [];
$ventas = [];
while($row = $res->fetch_assoc()){
    $meses[] = $row['mes'];
    $ventas[] = $row['ventas'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Estadísticas</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <h2>📊 Panel de Estadísticas</h2>
  <canvas id="graficoVentas"></canvas>
  <script>
    const ctx = document.getElementById('graficoVentas').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($meses); ?>,
        datasets: [{
          label: 'Ventas por mes',
          data: <?php echo json_encode($ventas); ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }]
      }
    });
  </script>
</body>


</html>
