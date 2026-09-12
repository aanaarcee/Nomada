<?php
// Script de instalación/migración: se ejecuta UNA sola vez (se puede
// volver a correr sin problema, no duplica nada) para:
//   1) Ajustar la tabla `destinos` (agregar Moneda, sacar Imagen).
//   2) Crear las tablas nuevas: imagenes_destino, servicios_destino,
//      actividades_destino, itinerario_destino.
//   3) Cargar los datos que antes estaban escritos a mano dentro de
//      destino_cancun.php y destino_paris.php, y las imágenes de los
//      6 destinos (leídas desde /img y guardadas como BLOB en la base).
//
// Se corre abriendo este archivo en el navegador, ej:
//   http://localhost/eito/logica/instalar_datos_destinos.php
// o por consola: php instalar_datos_destinos.php

require_once __DIR__ . '/conexion.php';

header('Content-Type: text/plain; charset=utf-8');

function columnaExiste(PDO $pdo, string $tabla, string $columna): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla AND COLUMN_NAME = :columna'
    );
    $stmt->execute(['tabla' => $tabla, 'columna' => $columna]);
    return (int) $stmt->fetchColumn() > 0;
}

echo "== 1. Ajustando tabla destinos ==\n";

if (!columnaExiste($pdo, 'destinos', 'Moneda')) {
    $pdo->exec("ALTER TABLE destinos ADD COLUMN Moneda VARCHAR(10) NOT NULL DEFAULT 'USD' AFTER Precio");
    echo "- Columna Moneda agregada.\n";
} else {
    echo "- Columna Moneda ya existía.\n";
}

if (columnaExiste($pdo, 'destinos', 'Imagen')) {
    $pdo->exec('ALTER TABLE destinos DROP COLUMN Imagen');
    echo "- Columna Imagen eliminada (las imágenes ahora viven en imagenes_destino).\n";
} else {
    echo "- Columna Imagen ya no existía.\n";
}

echo "\n== 2. Creando tablas nuevas ==\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS imagenes_destino (
        IdImagen INT AUTO_INCREMENT PRIMARY KEY,
        IdDestino INT NOT NULL,
        Tipo ENUM(\'hero\', \'galeria\') NOT NULL,
        Datos LONGBLOB NOT NULL,
        MimeType VARCHAR(50) NOT NULL DEFAULT \'image/jpeg\',
        Orden INT NOT NULL DEFAULT 0,
        KEY IdDestino (IdDestino),
        CONSTRAINT imagenes_destino_ibfk_1 FOREIGN KEY (IdDestino) REFERENCES destinos (IdDestino) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla imagenes_destino lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS servicios_destino (
        IdServicio INT AUTO_INCREMENT PRIMARY KEY,
        IdDestino INT NOT NULL,
        Descripcion VARCHAR(255) NOT NULL,
        Orden INT NOT NULL DEFAULT 0,
        KEY IdDestino (IdDestino),
        CONSTRAINT servicios_destino_ibfk_1 FOREIGN KEY (IdDestino) REFERENCES destinos (IdDestino) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla servicios_destino lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS actividades_destino (
        IdActividad INT AUTO_INCREMENT PRIMARY KEY,
        IdDestino INT NOT NULL,
        Descripcion VARCHAR(255) NOT NULL,
        Orden INT NOT NULL DEFAULT 0,
        KEY IdDestino (IdDestino),
        CONSTRAINT actividades_destino_ibfk_1 FOREIGN KEY (IdDestino) REFERENCES destinos (IdDestino) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla actividades_destino lista.\n";

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS itinerario_destino (
        IdItinerario INT AUTO_INCREMENT PRIMARY KEY,
        IdDestino INT NOT NULL,
        Dia VARCHAR(50) NOT NULL,
        Titulo VARCHAR(150) NOT NULL,
        Detalle TEXT NOT NULL,
        Orden INT NOT NULL DEFAULT 0,
        KEY IdDestino (IdDestino),
        CONSTRAINT itinerario_destino_ibfk_1 FOREIGN KEY (IdDestino) REFERENCES destinos (IdDestino) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
);
echo "- Tabla itinerario_destino lista.\n";

echo "\n== 3. Cargando contenido (servicios / actividades / itinerario) ==\n";

$contenido = [
    'cancun' => [
        'servicios' => [
            'Alojamiento 5 estrellas',
            'Traslados aeropuerto-hotel-aeropuerto',
            'Desayuno incluido',
            'Asistencia en español 24/7',
            'Seguro básico de viaje',
        ],
        'actividades' => [
            'Snorkel en arrecifes de coral',
            'Visita a Tulum y Chichén Itzá',
            'Paseo en catamarán',
            'Tour en kayak por manglares',
            'Cena frente al mar',
        ],
        'itinerario' => [
            ['Día 1', 'Llegada y bienvenida', 'Recepción en el aeropuerto y traslado al hotel. Tarde libre para instalarse y disfrutar de la playa.'],
            ['Día 2', 'Recorrido por la Riviera Maya', 'Excursión de día completo a Tulum y al cenote Azul, con almuerzo incluido.'],
            ['Día 3', 'Día de playa y relax', 'Jornada libre para disfrutar del resort, actividades opcionales y piscina.'],
            ['Día 4', 'Aventura acuática', 'Snorkel en el Parque Nacional de Cancún y paseo en catamarán al atardecer.'],
            ['Día 5', 'Regreso a casa', 'Mañana libre y traslado al aeropuerto según tu vuelo.'],
        ],
    ],
    'paris' => [
        'servicios' => [
            'Alojamiento 5 estrellas',
            'Traslados aeropuerto-hotel-aeropuerto',
            'Desayuno incluido',
            'Asistencia en español 24/7',
            'Seguro básico de viaje',
        ],
        'actividades' => [
            'Visita a la Torre Eiffel',
            'Tour por el Louvre',
            'Paseo en barco por el río Sena',
            'Cena en un restaurante histórico',
            'Compras en el mercado de Saint-Germain',
        ],
        'itinerario' => [
            ['Día 1', 'Bienvenida en la ciudad luz', 'Llegada a París, traslado al hotel y paseo de bienvenida por el Sena al anochecer.'],
            ['Día 2', 'Louvre y corazón histórico', 'Visita guiada al Museo del Louvre y recorrido por la Île de la Cité, con entrada a Notre-Dame.'],
            ['Día 3', 'Torre Eiffel y Montmartre', 'Ascenso a la Torre Eiffel, tarde en Montmartre y cena en un bistrot tradicional.'],
            ['Día 4', 'Versalles y jardines reales', 'Excursión de medio día al Palacio de Versalles y tarde libre para descubrir el Barrio Latino.'],
            ['Día 5', 'Compras y despedida', 'Mañana libre para compras en Saint-Germain y traslado al aeropuerto para el vuelo de regreso.'],
        ],
    ],
    'dubai' => [
        'servicios' => [
            'Alojamiento 5 estrellas',
            'Traslados aeropuerto-hotel-aeropuerto',
            'Desayuno incluido',
            'Asistencia en español 24/7',
            'Seguro básico de viaje',
        ],
        'actividades' => [
            'Safari en 4x4 por el desierto',
            'Visita al Burj Khalifa',
            'Paseo en dhow por Dubai Marina',
            'Tour de compras en el Dubai Mall',
            'Cena beduina bajo las estrellas',
        ],
        'itinerario' => [
            ['Día 1', 'Llegada y bienvenida', 'Recepción en el aeropuerto y traslado al hotel. Tarde libre para instalarse.'],
            ['Día 2', 'Dubái moderno', 'Visita al Burj Khalifa, el Dubai Mall y la fuente danzante.'],
            ['Día 3', 'Aventura en el desierto', 'Safari en 4x4, paseo en camello y cena beduina con show de danza.'],
            ['Día 4', 'Dubái histórico y costero', 'Recorrido por el Dubai Creek, el zoco de oro y paseo en dhow por la Marina.'],
            ['Día 5', 'Regreso a casa', 'Mañana libre y traslado al aeropuerto según tu vuelo.'],
        ],
    ],
    'tokio' => [
        'servicios' => [
            'Alojamiento 5 estrellas',
            'Traslados aeropuerto-hotel-aeropuerto',
            'Desayuno incluido',
            'Asistencia en español 24/7',
            'Seguro básico de viaje',
        ],
        'actividades' => [
            'Recorrido por Shibuya y Shinjuku',
            'Visita al templo Senso-ji en Asakusa',
            'Paseo en tren bala a Hakone',
            'Cena en un restaurante de sushi tradicional',
            'Tarde de tecnología y cultura pop en Akihabara',
        ],
        'itinerario' => [
            ['Día 1', 'Llegada a Tokio', 'Recepción en el aeropuerto y traslado al hotel. Tarde libre en Shinjuku.'],
            ['Día 2', 'Tokio tradicional', 'Templo Senso-ji, barrio de Asakusa y paseo por los jardines del Palacio Imperial.'],
            ['Día 3', 'Tokio moderno', 'Shibuya, Harajuku y tarde en Akihabara.'],
            ['Día 4', 'Excursión a Hakone', 'Día completo con vistas al monte Fuji y baños termales tradicionales.'],
            ['Día 5', 'Regreso a casa', 'Mañana libre para las últimas compras y traslado al aeropuerto.'],
        ],
    ],
    'maceio' => [
        'servicios' => [
            'Alojamiento 5 estrellas',
            'Traslados aeropuerto-hotel-aeropuerto',
            'Desayuno incluido',
            'Asistencia en español 24/7',
            'Seguro básico de viaje',
        ],
        'actividades' => [
            'Paseo en jangada por las piscinas naturales',
            'Visita a la Praia do Francês',
            'Excursión a Porto de Galinhas',
            'Tour gastronómico de mariscos',
            'Atardecer en la Lagoa Mundaú',
        ],
        'itinerario' => [
            ['Día 1', 'Llegada y bienvenida', 'Recepción en el aeropuerto y traslado al hotel frente al mar. Tarde libre en la playa.'],
            ['Día 2', 'Piscinas naturales', 'Paseo en jangada hasta los arrecifes de Pajuçara.'],
            ['Día 3', 'Praia do Francês', 'Día de playa y surf en una de las costas más lindas de Alagoas.'],
            ['Día 4', 'Porto de Galinhas', 'Excursión de día completo con snorkel y compras de artesanías.'],
            ['Día 5', 'Regreso a casa', 'Mañana libre y traslado al aeropuerto según tu vuelo.'],
        ],
    ],
    'munich' => [
        'servicios' => [
            'Alojamiento 5 estrellas',
            'Traslados aeropuerto-hotel-aeropuerto',
            'Desayuno incluido',
            'Asistencia en español 24/7',
            'Seguro básico de viaje',
        ],
        'actividades' => [
            'Visita a la Marienplatz y el Ayuntamiento Nuevo',
            'Recorrido por el Englischer Garten',
            'Tour por una cervecería tradicional',
            'Excursión al castillo de Neuschwanstein',
            'Visita al Deutsches Museum',
        ],
        'itinerario' => [
            ['Día 1', 'Llegada y bienvenida', 'Recepción en el aeropuerto y traslado al hotel. Tarde libre en la Marienplatz.'],
            ['Día 2', 'Múnich histórica', 'Ayuntamiento Nuevo, Frauenkirche y mercado de Viktualienmarkt.'],
            ['Día 3', 'Excursión a Neuschwanstein', 'Día completo al castillo de los cuentos de hadas bávaros.'],
            ['Día 4', 'Cultura y cerveza', 'Recorrido por una cervecería tradicional y tarde libre en el Englischer Garten.'],
            ['Día 5', 'Regreso a casa', 'Mañana libre y traslado al aeropuerto según tu vuelo.'],
        ],
    ],
];

$stmtSlug = $pdo->prepare('SELECT IdDestino FROM destinos WHERE Slug = :slug');
$stmtBorraServ = $pdo->prepare('DELETE FROM servicios_destino WHERE IdDestino = :id');
$stmtBorraAct = $pdo->prepare('DELETE FROM actividades_destino WHERE IdDestino = :id');
$stmtBorraIti = $pdo->prepare('DELETE FROM itinerario_destino WHERE IdDestino = :id');
$stmtInsServ = $pdo->prepare('INSERT INTO servicios_destino (IdDestino, Descripcion, Orden) VALUES (:id, :desc, :orden)');
$stmtInsAct = $pdo->prepare('INSERT INTO actividades_destino (IdDestino, Descripcion, Orden) VALUES (:id, :desc, :orden)');
$stmtInsIti = $pdo->prepare(
    'INSERT INTO itinerario_destino (IdDestino, Dia, Titulo, Detalle, Orden) VALUES (:id, :dia, :titulo, :detalle, :orden)'
);

foreach ($contenido as $slug => $datos) {
    $stmtSlug->execute(['slug' => $slug]);
    $idDestino = $stmtSlug->fetchColumn();

    if (!$idDestino) {
        echo "- [$slug] no existe en destinos, se salteó.\n";
        continue;
    }

    $idDestino = (int) $idDestino;

    $stmtBorraServ->execute(['id' => $idDestino]);
    foreach ($datos['servicios'] as $orden => $descripcion) {
        $stmtInsServ->execute(['id' => $idDestino, 'desc' => $descripcion, 'orden' => $orden]);
    }

    $stmtBorraAct->execute(['id' => $idDestino]);
    foreach ($datos['actividades'] as $orden => $descripcion) {
        $stmtInsAct->execute(['id' => $idDestino, 'desc' => $descripcion, 'orden' => $orden]);
    }

    $stmtBorraIti->execute(['id' => $idDestino]);
    foreach ($datos['itinerario'] as $orden => $dia) {
        $stmtInsIti->execute([
            'id' => $idDestino,
            'dia' => $dia[0],
            'titulo' => $dia[1],
            'detalle' => $dia[2],
            'orden' => $orden,
        ]);
    }

    echo "- [$slug] servicios, actividades e itinerario cargados.\n";
}

echo "\n== 4. Cargando imágenes (se guardan como BLOB dentro de MySQL) ==\n";

$imagenesPorDestino = [
    'cancun' => [
        'hero' => __DIR__ . '/../img/destinos/cancun.jpg',
        'galeria' => [
            __DIR__ . '/../img/experiencias/playa1img.jpg',
            __DIR__ . '/../img/experiencias/playa2img.jpg',
            __DIR__ . '/../img/experiencias/playa3img.jpg',
        ],
    ],
    'paris' => [
        'hero' => __DIR__ . '/../img/destinos/parisimg.jpg',
        'galeria' => [
            __DIR__ . '/../img/destinos/parisimg.jpg',
        ],
    ],
    'dubai' => [
        'hero' => __DIR__ . '/../img/destinos/dubaiimg.jpg',
        'galeria' => [],
    ],
    'tokio' => [
        'hero' => __DIR__ . '/../img/destinos/tokioimg.jpg',
        'galeria' => [],
    ],
    'maceio' => [
        'hero' => __DIR__ . '/../img/destinos/brasilimg.jpg',
        'galeria' => [],
    ],
    'munich' => [
        'hero' => __DIR__ . '/../img/destinos/munichimg.jpg',
        'galeria' => [],
    ],
];

$stmtBorraImg = $pdo->prepare('DELETE FROM imagenes_destino WHERE IdDestino = :id');
$stmtInsImg = $pdo->prepare(
    'INSERT INTO imagenes_destino (IdDestino, Tipo, Datos, MimeType, Orden) VALUES (:id, :tipo, :datos, :mime, :orden)'
);

function mimePorExtension(string $ruta): string
{
    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    return match ($ext) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        default => 'image/jpeg',
    };
}

foreach ($imagenesPorDestino as $slug => $imagenes) {
    $stmtSlug->execute(['slug' => $slug]);
    $idDestino = $stmtSlug->fetchColumn();

    if (!$idDestino) {
        echo "- [$slug] no existe en destinos, se salteó.\n";
        continue;
    }

    $idDestino = (int) $idDestino;
    $stmtBorraImg->execute(['id' => $idDestino]);

    if (is_file($imagenes['hero'])) {
        $stmtInsImg->execute([
            'id' => $idDestino,
            'tipo' => 'hero',
            'datos' => file_get_contents($imagenes['hero']),
            'mime' => mimePorExtension($imagenes['hero']),
            'orden' => 0,
        ]);
        echo "- [$slug] imagen principal cargada.\n";
    } else {
        echo "- [$slug] AVISO: no se encontró el archivo " . $imagenes['hero'] . "\n";
    }

    foreach ($imagenes['galeria'] as $orden => $ruta) {
        if (!is_file($ruta)) {
            echo "- [$slug] AVISO: no se encontró el archivo $ruta\n";
            continue;
        }
        $stmtInsImg->execute([
            'id' => $idDestino,
            'tipo' => 'galeria',
            'datos' => file_get_contents($ruta),
            'mime' => mimePorExtension($ruta),
            'orden' => $orden,
        ]);
    }
}

echo "\nListo. Ya podés borrar o dejar este archivo; se puede volver a ejecutar cuando quieras sin duplicar datos.\n";
