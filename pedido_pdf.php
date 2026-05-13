<?php
require_once 'auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/fpdf186/fpdf.php';

$mysqli = new mysqli("localhost", "root", "", "ejemplo");
if ($mysqli->connect_errno) die("Error de conexión");

$order_id = intval($_GET['order_id'] ?? 0);

// Obtener encabezado del pedido
$stmt = $mysqli->prepare("SELECT o.id, o.total, o.telefono, o.email, o.created_at, o.estado, u.nombre AS cliente
                          FROM orders o
                          LEFT JOIN usuarios u ON o.usuario_id = u.id
                          WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$pedido = $res->fetch_assoc();
$stmt->close();

if (!$pedido) {
  die("Pedido no encontrado");
}
// Verificar si el pedido está pagado
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM pagos WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($pagado);
$stmt->fetch();
$stmt->close();

$estado_pago = ($pagado > 0) ? 'Pagado' : 'No pagado';


// Obtener productos del pedido
$stmt = $mysqli->prepare("SELECT nombre, precio, cantidad, subtotal FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();

// ✅ Logo en la parte superior
$pdf->Image(__DIR__ . '/img/logo.png', 10, 10, 30);
$pdf->Ln(20);


$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Resumen del Pedido #' . $pedido['id'], 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Cliente: ' . utf8_decode($pedido['cliente'] ?? 'Visitante'), 0, 1);
$pdf->Cell(0, 10, 'Fecha: ' . $pedido['created_at'], 0, 1);
$pdf->Cell(0, 10, 'Estado del pago: ' . $estado_pago, 0, 1);
$pdf->Cell(0, 10, 'Email: ' . $pedido['email'], 0, 1);
$pdf->Cell(0, 10, 'Telefono: ' . $pedido['telefono'], 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Producto', 1);
$pdf->Cell(30, 10, 'Precio', 1);
$pdf->Cell(30, 10, 'Cantidad', 1);
$pdf->Cell(40, 10, 'Subtotal', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($items as $item) {
  $pdf->Cell(80, 10, utf8_decode($item['nombre']), 1);
  $pdf->Cell(30, 10, '$' . number_format($item['precio'], 2), 1);
  $pdf->Cell(30, 10, $item['cantidad'], 1);
  $pdf->Cell(40, 10, '$' . number_format($item['subtotal'], 2), 1);
  $pdf->Ln();
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, 'Total del pedido', 1);
$pdf->Cell(40, 10, '$' . number_format($pedido['total'], 2), 1);

$pdf->Output('D', 'pedido_' . $pedido['id'] . '.pdf');
