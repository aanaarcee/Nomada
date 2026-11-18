<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/reserva_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$volverA = $_POST['volver_a'] ?? 'index.php';

// Nota: el "origen" elegido en el form no se persiste porque la tabla
// `detallecarrito`/`detallereserva` no tiene columna para eso en el
// esquema actual. Si lo necesitás guardado, hay que sumar una columna
// `Origen varchar(150)` a ambas tablas.
$datos = [
    'id_destino' => $_POST['id_destino'] ?? 0,
    'accion' => $_POST['accion'] ?? '',
    'pasajeros' => $_POST['pasajeros'] ?? 1,
    'fecha_ida' => $_POST['fecha_ida'] ?? '',
    'fecha_vuelta' => $_POST['fecha_vuelta'] ?? '',
];

// Requiere sesión iniciada. Si todavía no inició sesión, guardamos la
// reserva que estaba por hacer para completarla automáticamente apenas
// inicie sesión (login.php/registro.php la retoman con este mismo dato).
if (empty($_SESSION['IdUsuario'])) {
    $_SESSION['reserva_pendiente'] = $datos;
    header('Location: ../login/login.php?redirect=' . urlencode($volverA));
    exit;
}

$resultado = guardarReserva($pdo, (int) $_SESSION['IdUsuario'], $datos);
header('Location: ' . $volverA . '?' . $resultado);
exit;
