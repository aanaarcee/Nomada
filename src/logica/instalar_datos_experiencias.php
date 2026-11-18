<?php
// Instalación/migración para la sección "Experiencias" del index
// (pestañas Playa / Montaña / Aventura / Gastronomía / Familiar). Crea
// la tabla `experiencias` y carga 3 paquetes por categoría. Se puede
// volver a correr: borra e inserta de nuevo, no duplica.
//
//   php instalar_datos_experiencias.php

require_once __DIR__ . '/conexion.php';

header('Content-Type: text/plain; charset=utf-8');

echo "== 1. Creando tabla experiencias ==\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS experiencias (
        IdExperiencia INT AUTO_INCREMENT PRIMARY KEY,
        Categoria VARCHAR(50) NOT NULL,
        Nombre VARCHAR(150) NOT NULL,
        Pais VARCHAR(100) NOT NULL,
        Duracion VARCHAR(50) NOT NULL,
        Temporada VARCHAR(50) NOT NULL,
        Hotel VARCHAR(150) NOT NULL,
        Pasajeros INT NOT NULL DEFAULT 2,
        Excursiones VARCHAR(255) DEFAULT NULL,
        Precio DECIMAL(10,2) NOT NULL,
        Moneda VARCHAR(10) NOT NULL DEFAULT \'USD\',
        Link VARCHAR(255) NOT NULL DEFAULT \'#\',
        KEY Categoria (Categoria)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla experiencias lista.\n";

echo "\n== 2. Cargando experiencias por categoría ==\n";

$experiencias = [
    'Playa' => [
        ['Cancún Paradise', 'México', '7 días / 6 noches', 'Temporada alta', 'Hotel Grand Ocean', 2, 'Snorkel, cenote y tour en catamarán', 1240, '../destinos/destino.php?slug=cancun'],
        ['Playa del Carmen', 'México', '6 días / 5 noches', 'Temporada alta', 'Resort Coral Beach', 4, '-', 980, '#'],
        ['Punta Cana', 'República Dominicana', '8 días / 7 noches', 'Temporada alta', 'Boutique Mar Azul', 2, 'Catamarán y spa', 1560, '#'],
    ],
    'Montaña' => [
        ['Aventura en los Andes', 'Argentina', '6 días / 5 noches', 'Temporada alta', 'Refugio Cumbre Blanca', 2, 'Trekking y laguna glaciar', 1150, '#'],
        ['Refugios de Bariloche', 'Argentina', '5 días / 4 noches', 'Temporada invernal', 'Hotel Nahuel Lodge', 4, 'Excursión al Cerro Catedral', 1320, '#'],
        ['Glaciares del Sur', 'Argentina', '7 días / 6 noches', 'Temporada alta', 'Hostería Glaciar Azul', 2, 'Navegación de glaciares', 1480, '#'],
    ],
    'Aventura' => [
        ['Safari Extremo Africano', 'Sudáfrica', '8 días / 7 noches', 'Temporada alta', 'Lodge Kruger Vista', 4, 'Safari fotográfico y rafting', 2350, '#'],
        ['Rafting en Ríos Salvajes', 'Costa Rica', '5 días / 4 noches', 'Temporada alta', 'Eco Lodge Pacuare', 2, 'Descenso de rápidos clase IV', 980, '#'],
        ['Expedición 4x4 al Desierto', 'Chile', '6 días / 5 noches', 'Temporada seca', 'Campamento Atacama Base', 2, 'Travesía por el Salar de Atacama', 1190, '#'],
    ],
    'Gastronomía' => [
        ['Sabores de Italia', 'Italia', '7 días / 6 noches', 'Temporada alta', 'Hotel Trattoria Reale', 2, 'Clases de cocina y cata de vinos', 1980, '#'],
        ['Ruta de Bodegas Mendocinas', 'Argentina', '4 días / 3 noches', 'Temporada alta', 'Bodega Boutique Los Álamos', 2, 'Degustación de vinos y asado criollo', 760, '#'],
        ['Mercados y Cocina Local', 'México', '5 días / 4 noches', 'Temporada alta', 'Hotel Casa Sabores', 4, 'Tour de mercados y clase de cocina', 890, '#'],
    ],
    'Familiar' => [
        ['Vacaciones en Familia en Orlando', 'Estados Unidos', '8 días / 7 noches', 'Temporada alta', 'Resort Family Fun', 5, 'Entradas a parques temáticos incluidas', 2480, '#'],
        ['Playa para Todos en Río de Janeiro', 'Brasil', '6 días / 5 noches', 'Temporada alta', 'Hotel Copacabana Kids', 4, 'Excursión al Pan de Azúcar', 1340, '#'],
        ['Estadía Familiar en la Costa', 'Uruguay', '5 días / 4 noches', 'Temporada alta', 'Aparthotel Costa Azul', 5, 'Actividades acuáticas y club infantil', 980, '#'],
    ],
];

$stmtBorra = $pdo->prepare('DELETE FROM experiencias WHERE Categoria = :categoria');
$stmtInserta = $pdo->prepare(
    'INSERT INTO experiencias (Categoria, Nombre, Pais, Duracion, Temporada, Hotel, Pasajeros, Excursiones, Precio, Link)
     VALUES (:categoria, :nombre, :pais, :duracion, :temporada, :hotel, :pasajeros, :excursiones, :precio, :link)'
);

foreach ($experiencias as $categoria => $items) {
    $stmtBorra->execute(['categoria' => $categoria]);
    foreach ($items as $item) {
        $stmtInserta->execute([
            'categoria' => $categoria,
            'nombre' => $item[0],
            'pais' => $item[1],
            'duracion' => $item[2],
            'temporada' => $item[3],
            'hotel' => $item[4],
            'pasajeros' => $item[5],
            'excursiones' => $item[6],
            'precio' => $item[7],
            'link' => $item[8],
        ]);
    }
    echo "- [$categoria] " . count($items) . " experiencias cargadas.\n";
}

echo "\nListo. Se puede volver a ejecutar sin duplicar (borra por categoría antes de insertar).\n";
