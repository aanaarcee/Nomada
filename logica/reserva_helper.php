<?php
// Lógica compartida para guardar una reserva/carrito, usada tanto por
// guardar_reserva.php (usuario ya logueado) como por login.php/registro.php
// (para completar una reserva que quedó pendiente mientras el usuario
// iniciaba sesión).

function guardarReserva(PDO $pdo, int $idUsuario, array $datos): string
{
    $idDestino = (int) ($datos['id_destino'] ?? 0);
    $accion = $datos['accion'] ?? '';
    $cantidadPasajeros = (int) ($datos['pasajeros'] ?? 1);
    $fechaIda = $datos['fecha_ida'] ?? '';
    $fechaVuelta = $datos['fecha_vuelta'] ?? '';

    if ($idDestino <= 0 || $cantidadPasajeros <= 0 || !in_array($accion, ['carrito', 'comprar'], true)) {
        return 'error=1';
    }

    try {
        if ($accion === 'carrito') {
            $stmt = $pdo->prepare('SELECT IdCarrito FROM carrito WHERE IdUsuario = :idUsuario ORDER BY FechaCreacion DESC LIMIT 1');
            $stmt->execute(['idUsuario' => $idUsuario]);
            $carrito = $stmt->fetch();

            if ($carrito) {
                $idCarrito = (int) $carrito['IdCarrito'];
            } else {
                $stmt = $pdo->prepare('INSERT INTO carrito (IdUsuario) VALUES (:idUsuario)');
                $stmt->execute(['idUsuario' => $idUsuario]);
                $idCarrito = (int) $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare(
                'INSERT INTO detallecarrito (IdCarrito, IdDestino, CantidadPasajeros, FechaIda, FechaVuelta)
                 VALUES (:idCarrito, :idDestino, :cantidad, :fechaIda, :fechaVuelta)'
            );
            $stmt->execute([
                'idCarrito' => $idCarrito,
                'idDestino' => $idDestino,
                'cantidad' => $cantidadPasajeros,
                'fechaIda' => $fechaIda !== '' ? $fechaIda : null,
                'fechaVuelta' => $fechaVuelta !== '' ? $fechaVuelta : null,
            ]);

            return 'ok=carrito';
        }

        // $accion === 'comprar'
        $stmt = $pdo->prepare('SELECT Precio FROM destinos WHERE IdDestino = :idDestino');
        $stmt->execute(['idDestino' => $idDestino]);
        $destinoRow = $stmt->fetch();

        if (!$destinoRow) {
            return 'error=1';
        }

        $precioUnitario = (float) $destinoRow['Precio'];
        $total = $precioUnitario * $cantidadPasajeros;

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO reservas (IdUsuario, Total) VALUES (:idUsuario, :total)');
        $stmt->execute([
            'idUsuario' => $idUsuario,
            'total' => $total,
        ]);
        $idReserva = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO detallereserva (IdReserva, IdDestino, CantidadPasajeros, FechaIda, FechaVuelta, PrecioUnitario)
             VALUES (:idReserva, :idDestino, :cantidad, :fechaIda, :fechaVuelta, :precio)'
        );
        $stmt->execute([
            'idReserva' => $idReserva,
            'idDestino' => $idDestino,
            'cantidad' => $cantidadPasajeros,
            'fechaIda' => $fechaIda !== '' ? $fechaIda : null,
            'fechaVuelta' => $fechaVuelta !== '' ? $fechaVuelta : null,
            'precio' => $precioUnitario,
        ]);

        $pdo->commit();

        return 'ok=comprar';
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return 'error=1';
    }
}

// Si el usuario venía de intentar reservar sin sesión, completa esa
// reserva ahora que ya está logueado y devuelve el sufijo de query
// (ok=comprar / ok=carrito / error=1) para agregarlo al redirect, o
// null si no había ninguna reserva pendiente.
function completarReservaPendiente(PDO $pdo, int $idUsuario): ?string
{
    if (empty($_SESSION['reserva_pendiente'])) {
        return null;
    }

    $datos = $_SESSION['reserva_pendiente'];
    unset($_SESSION['reserva_pendiente']);

    return guardarReserva($pdo, $idUsuario, $datos);
}
