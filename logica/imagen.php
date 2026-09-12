<?php
// Sirve una imagen guardada como BLOB en la tabla imagenes_destino.
// Se usa como src de <img> o background-image: logica/imagen.php?id=5
require_once __DIR__ . '/conexion.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT Datos, MimeType FROM imagenes_destino WHERE IdImagen = :id');
$stmt->execute(['id' => $id]);
$imagen = $stmt->fetch();

if (!$imagen) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $imagen['MimeType']);
header('Cache-Control: public, max-age=86400');
echo $imagen['Datos'];
