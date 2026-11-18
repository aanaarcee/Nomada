<?php
// Sirve una imagen de paquete guardada como BLOB en imagenes_paquete.
// Uso: logica/imagen_paquete.php?id=5
require_once __DIR__ . '/conexion.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT Datos, MimeType FROM imagenes_paquete WHERE IdImagen = :id');
$stmt->execute(['id' => $id]);
$imagen = $stmt->fetch();

if (!$imagen) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $imagen['MimeType']);
header('Cache-Control: public, max-age=86400');
echo $imagen['Datos'];
