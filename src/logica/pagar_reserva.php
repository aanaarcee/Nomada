<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (empty($_SESSION['IdUsuario'])) {
    header('Location: ../login/login.php?redirect=../logica/mis-reservas.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mis-reservas.php');
    exit;
}

$idUsuario = (int) $_SESSION['IdUsuario'];
$idReserva = (int) ($_POST['id_reserva'] ?? 0);

if ($idReserva > 0) {
    $stmt = $pdo->prepare(
        "UPDATE reservas SET Estado = 'pagada' WHERE IdReserva = :idReserva AND IdUsuario = :idUsuario AND Estado = 'pendiente'"
    );
    $stmt->execute([
        'idReserva' => $idReserva,
        'idUsuario' => $idUsuario,
    ]);
}

header('Location: mis-reservas.php');
exit;
