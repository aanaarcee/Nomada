<?php
session_start();
require_once __DIR__ . '/conexion.php';

$destinoSlug = $_GET['destino'] ?? '';
$origen = $_GET['origen'] ?? '';
$fechaIda = $_GET['fecha_ida'] ?? '';
$fechaVuelta = $_GET['fecha_vuelta'] ?? '';
$pasajeros = $_GET['pasajeros'] ?? '';

$stmt = $pdo->prepare('SELECT Slug FROM destinos WHERE Slug = :slug');
$stmt->execute(['slug' => $destinoSlug]);
$destino = $stmt->fetch();

// Si el destino no existe en la base, volvemos al listado con un aviso.
if (!$destino) {
    header('Location: ../index.php?no_disponible=1#destinos');
    exit;
}

// Una sola plantilla dibuja cualquier destino: solo cambia el slug.
$queryParams = http_build_query([
    'slug' => $destino['Slug'],
    'origen' => $origen,
    'fecha_ida' => $fechaIda,
    'fecha_vuelta' => $fechaVuelta,
    'pasajeros' => $pasajeros,
]);

header('Location: ../destinos/destino.php?' . $queryParams);
exit;
