<?php
// Instalación/migración para los "paquetes" (ofertas especiales) del
// index: crea las tablas necesarias y carga el contenido de los 3
// paquetes que se ven en la portada (Miami + Crucero, Europa Clásica,
// Aventura Patagónica). Se puede volver a correr sin duplicar nada.
//
// Se corre abriendo este archivo en el navegador o por consola:
//   php instalar_datos_paquetes.php

require_once __DIR__ . '/conexion.php';

header('Content-Type: text/plain; charset=utf-8');

echo "== 1. Creando tablas de paquetes ==\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS paquetes (
        IdPaquete INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(150) NOT NULL,
        Slug VARCHAR(80) NOT NULL,
        Descripcion TEXT,
        PrecioOriginal DECIMAL(10,2) NOT NULL,
        PrecioOferta DECIMAL(10,2) NOT NULL,
        Moneda VARCHAR(10) NOT NULL DEFAULT \'USD\',
        Noches INT NOT NULL DEFAULT 0,
        FinOferta DATETIME NULL,
        UNIQUE KEY Slug (Slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla paquetes lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS imagenes_paquete (
        IdImagen INT AUTO_INCREMENT PRIMARY KEY,
        IdPaquete INT NOT NULL,
        Tipo ENUM(\'hero\', \'galeria\') NOT NULL,
        Datos LONGBLOB NOT NULL,
        MimeType VARCHAR(50) NOT NULL DEFAULT \'image/jpeg\',
        Orden INT NOT NULL DEFAULT 0,
        KEY IdPaquete (IdPaquete),
        CONSTRAINT imagenes_paquete_ibfk_1 FOREIGN KEY (IdPaquete) REFERENCES paquetes (IdPaquete) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla imagenes_paquete lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS servicios_paquete (
        IdServicio INT AUTO_INCREMENT PRIMARY KEY,
        IdPaquete INT NOT NULL,
        Descripcion VARCHAR(255) NOT NULL,
        Orden INT NOT NULL DEFAULT 0,
        KEY IdPaquete (IdPaquete),
        CONSTRAINT servicios_paquete_ibfk_1 FOREIGN KEY (IdPaquete) REFERENCES paquetes (IdPaquete) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla servicios_paquete lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS actividades_paquete (
        IdActividad INT AUTO_INCREMENT PRIMARY KEY,
        IdPaquete INT NOT NULL,
        Descripcion VARCHAR(255) NOT NULL,
        Orden INT NOT NULL DEFAULT 0,
        KEY IdPaquete (IdPaquete),
        CONSTRAINT actividades_paquete_ibfk_1 FOREIGN KEY (IdPaquete) REFERENCES paquetes (IdPaquete) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla actividades_paquete lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS itinerario_paquete (
        IdItinerario INT AUTO_INCREMENT PRIMARY KEY,
        IdPaquete INT NOT NULL,
        Dia VARCHAR(50) NOT NULL,
        Titulo VARCHAR(150) NOT NULL,
        Detalle TEXT NOT NULL,
        Orden INT NOT NULL DEFAULT 0,
        KEY IdPaquete (IdPaquete),
        CONSTRAINT itinerario_paquete_ibfk_1 FOREIGN KEY (IdPaquete) REFERENCES paquetes (IdPaquete) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla itinerario_paquete lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS caracteristicas_paquete (
        IdCaracteristica INT AUTO_INCREMENT PRIMARY KEY,
        IdPaquete INT NOT NULL,
        Icono VARCHAR(50) NOT NULL,
        Texto VARCHAR(100) NOT NULL,
        Orden INT NOT NULL DEFAULT 0,
        KEY IdPaquete (IdPaquete),
        CONSTRAINT caracteristicas_paquete_ibfk_1 FOREIGN KEY (IdPaquete) REFERENCES paquetes (IdPaquete) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla caracteristicas_paquete lista.\n";

echo "\n== 2. Cargando los 3 paquetes ==\n";

$paquetes = [
    'miami-crucero' => [
        'nombre' => 'Miami + Crucero Caribe',
        'descripcion' => '7 noches en Miami Beach más 7 noches de crucero por el Caribe con todo incluido.',
        'precio_original' => 3800,
        'precio_oferta' => 2470,
        'noches' => 14,
        'fin_oferta_horas' => 3 * 24,
        'caracteristicas' => [
            ['bi-calendar3', '14 noches'],
            ['bi-airplane', 'Vuelos incl.'],
            ['bi-cup-hot', 'Todo incluido'],
        ],
        'servicios' => [
            'Vuelos internacionales incluidos',
            'Traslados aeropuerto-hotel-puerto',
            'Alojamiento 4 estrellas en Miami Beach',
            'Camarote con balcón en el crucero',
            'Todas las comidas a bordo incluidas',
        ],
        'actividades' => [
            'Día libre en South Beach',
            'Tour de compras en Aventura Mall',
            'Excursión en Cozumel',
            'Snorkel en Great Stirrup Cay',
            'Noche de gala a bordo',
        ],
        'itinerario' => [
            ['Día 1', 'Llegada a Miami', 'Recepción en el aeropuerto y traslado al hotel en Miami Beach. Tarde libre para disfrutar de la playa.'],
            ['Días 2-7', 'Miami a tu ritmo', 'Días libres para recorrer South Beach, Aventura Mall y Wynwood, con actividades opcionales.'],
            ['Día 8', 'Embarque del crucero', 'Traslado al puerto de Miami y embarque rumbo al Caribe.'],
            ['Días 9-13', 'Navegación por el Caribe', 'Escalas en Cozumel, Great Stirrup Cay y otros puertos, con excursiones opcionales.'],
            ['Día 14', 'Regreso', 'Desembarco y traslado al aeropuerto según tu vuelo.'],
        ],
        'imagenes' => [
            'hero' => __DIR__ . '/../img/experiencias/playa2img.jpg',
            'galeria' => [
                __DIR__ . '/../img/experiencias/playa1img.jpg',
                __DIR__ . '/../img/experiencias/playa3img.jpg',
            ],
        ],
    ],
    'europa-clasica' => [
        'nombre' => 'Europa Clásica',
        'descripcion' => 'Madrid, Barcelona, París y Roma en 16 días con guía en español y hotel 4 estrellas.',
        'precio_original' => 4200,
        'precio_oferta' => 3020,
        'noches' => 16,
        'fin_oferta_horas' => 6 * 24,
        'caracteristicas' => [
            ['bi-calendar3', '16 noches'],
            ['bi-translate', 'Guía español'],
            ['bi-star', 'Hotel 4★'],
        ],
        'servicios' => [
            'Vuelos internacionales incluidos',
            'Traslados entre ciudades',
            'Alojamiento 4 estrellas',
            'Desayuno buffet incluido',
            'Guía de habla hispana durante todo el recorrido',
        ],
        'actividades' => [
            'Visita guiada al Museo del Prado',
            'Recorrido por la Sagrada Familia',
            'Crucero por el Sena',
            'Tour por el Coliseo y el Foro Romano',
            'Noche de flamenco en Madrid',
        ],
        'itinerario' => [
            ['Días 1-4', 'Madrid', 'Llegada, tour por el Madrid histórico, Museo del Prado y noche de flamenco.'],
            ['Días 5-8', 'Barcelona', 'Sagrada Familia, Parque Güell y paseo por Las Ramblas.'],
            ['Días 9-12', 'París', 'Torre Eiffel, Louvre y crucero por el Sena.'],
            ['Días 13-16', 'Roma', 'Coliseo, Vaticano y despedida con cena típica romana.'],
        ],
        'imagenes' => [
            'hero' => __DIR__ . '/../img/destinos/parisimg.jpg',
            'galeria' => [],
        ],
    ],
    'patagonia' => [
        'nombre' => 'Aventura Patagónica',
        'descripcion' => 'Torres del Paine, Perito Moreno y los lagos del sur en 10 días de naturaleza pura.',
        'precio_original' => 1870,
        'precio_oferta' => 1495,
        'noches' => 10,
        'fin_oferta_horas' => 2 * 24,
        'caracteristicas' => [
            ['bi-calendar3', '10 noches'],
            ['bi-compass', 'Trek guiado'],
            ['bi-camera', 'Fotos incl.'],
        ],
        'servicios' => [
            'Traslados terrestres incluidos',
            'Alojamiento en refugios y hosterías de montaña',
            'Guía de trekking certificado',
            'Equipo de trekking básico incluido',
            'Seguro de viaje con cobertura de montaña',
        ],
        'actividades' => [
            'Trekking en Torres del Paine',
            'Navegación frente al glaciar Perito Moreno',
            'Cabalgata por estancias patagónicas',
            'Avistaje de fauna en los lagos del sur',
            'Sesión de fotografía de paisajes con guía',
        ],
        'itinerario' => [
            ['Días 1-2', 'Llegada a El Calafate', 'Recepción y aclimatación, primera caminata suave junto al lago Argentino.'],
            ['Días 3-4', 'Glaciar Perito Moreno', 'Navegación frente al glaciar y caminata por pasarelas panorámicas.'],
            ['Días 5-7', 'Torres del Paine', 'Trekking guiado por los senderos más icónicos del parque.'],
            ['Días 8-10', 'Lagos del sur y regreso', 'Cabalgata, avistaje de fauna y traslado de regreso.'],
        ],
        'imagenes' => [
            'hero' => __DIR__ . '/../img/experiencias/montanas1img.jpg',
            'galeria' => [
                __DIR__ . '/../img/experiencias/montanas2img.jpg',
                __DIR__ . '/../img/experiencias/montanas3img.jpg',
            ],
        ],
    ],
];

function mimePorExtension(string $ruta): string
{
    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    return match ($ext) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        default => 'image/jpeg',
    };
}

$stmtBuscar = $pdo->prepare('SELECT IdPaquete FROM paquetes WHERE Slug = :slug');
$stmtInsertaPaquete = $pdo->prepare(
    'INSERT INTO paquetes (Nombre, Slug, Descripcion, PrecioOriginal, PrecioOferta, Noches, FinOferta)
     VALUES (:nombre, :slug, :descripcion, :original, :oferta, :noches, :fin)'
);
$stmtActualizaPaquete = $pdo->prepare(
    'UPDATE paquetes SET Nombre = :nombre, Descripcion = :descripcion, PrecioOriginal = :original,
     PrecioOferta = :oferta, Noches = :noches, FinOferta = :fin WHERE IdPaquete = :id'
);

foreach ($paquetes as $slug => $datos) {
    $stmtBuscar->execute(['slug' => $slug]);
    $idPaquete = $stmtBuscar->fetchColumn();

    $finOferta = date('Y-m-d H:i:s', time() + $datos['fin_oferta_horas'] * 3600);

    if ($idPaquete) {
        $idPaquete = (int) $idPaquete;
        $stmtActualizaPaquete->execute([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'],
            'original' => $datos['precio_original'],
            'oferta' => $datos['precio_oferta'],
            'noches' => $datos['noches'],
            'fin' => $finOferta,
            'id' => $idPaquete,
        ]);
    } else {
        $stmtInsertaPaquete->execute([
            'nombre' => $datos['nombre'],
            'slug' => $slug,
            'descripcion' => $datos['descripcion'],
            'original' => $datos['precio_original'],
            'oferta' => $datos['precio_oferta'],
            'noches' => $datos['noches'],
            'fin' => $finOferta,
        ]);
        $idPaquete = (int) $pdo->lastInsertId();
    }

    foreach (['servicios', 'actividades'] as $tabla) {
        $pdo->prepare("DELETE FROM {$tabla}_paquete WHERE IdPaquete = :id")->execute(['id' => $idPaquete]);
        $stmtIns = $pdo->prepare("INSERT INTO {$tabla}_paquete (IdPaquete, Descripcion, Orden) VALUES (:id, :desc, :orden)");
        foreach ($datos[$tabla] as $orden => $texto) {
            $stmtIns->execute(['id' => $idPaquete, 'desc' => $texto, 'orden' => $orden]);
        }
    }

    $pdo->prepare('DELETE FROM itinerario_paquete WHERE IdPaquete = :id')->execute(['id' => $idPaquete]);
    $stmtIti = $pdo->prepare(
        'INSERT INTO itinerario_paquete (IdPaquete, Dia, Titulo, Detalle, Orden) VALUES (:id, :dia, :titulo, :detalle, :orden)'
    );
    foreach ($datos['itinerario'] as $orden => $item) {
        $stmtIti->execute([
            'id' => $idPaquete,
            'dia' => $item[0],
            'titulo' => $item[1],
            'detalle' => $item[2],
            'orden' => $orden,
        ]);
    }

    $pdo->prepare('DELETE FROM caracteristicas_paquete WHERE IdPaquete = :id')->execute(['id' => $idPaquete]);
    $stmtCar = $pdo->prepare(
        'INSERT INTO caracteristicas_paquete (IdPaquete, Icono, Texto, Orden) VALUES (:id, :icono, :texto, :orden)'
    );
    foreach ($datos['caracteristicas'] as $orden => $car) {
        $stmtCar->execute(['id' => $idPaquete, 'icono' => $car[0], 'texto' => $car[1], 'orden' => $orden]);
    }

    $pdo->prepare('DELETE FROM imagenes_paquete WHERE IdPaquete = :id')->execute(['id' => $idPaquete]);
    $stmtImg = $pdo->prepare(
        'INSERT INTO imagenes_paquete (IdPaquete, Tipo, Datos, MimeType, Orden) VALUES (:id, :tipo, :datos, :mime, :orden)'
    );

    if (is_file($datos['imagenes']['hero'])) {
        $stmtImg->execute([
            'id' => $idPaquete,
            'tipo' => 'hero',
            'datos' => file_get_contents($datos['imagenes']['hero']),
            'mime' => mimePorExtension($datos['imagenes']['hero']),
            'orden' => 0,
        ]);
    } else {
        echo "- [$slug] AVISO: no se encontró la imagen principal " . $datos['imagenes']['hero'] . "\n";
    }

    foreach ($datos['imagenes']['galeria'] as $orden => $ruta) {
        if (!is_file($ruta)) {
            echo "- [$slug] AVISO: no se encontró el archivo $ruta\n";
            continue;
        }
        $stmtImg->execute([
            'id' => $idPaquete,
            'tipo' => 'galeria',
            'datos' => file_get_contents($ruta),
            'mime' => mimePorExtension($ruta),
            'orden' => $orden,
        ]);
    }

    echo "- [$slug] cargado (vence en {$datos['fin_oferta_horas']}h desde ahora).\n";
}

echo "\nListo. Se puede volver a ejecutar cuando quieras sin duplicar filas (actualiza en vez de duplicar).\n";
