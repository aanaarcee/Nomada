<?php
session_start();
require_once __DIR__ . '/conexion.php';

$reservasEnCurso = [];
$reservasPasadas = [];

if (!empty($_SESSION['IdUsuario'])) {
    $stmt = $pdo->prepare(
        'SELECT r.IdReserva, r.Total, r.Estado, dr.CantidadPasajeros,
                dr.FechaIda, dr.FechaVuelta, d.IdDestino, d.Nombre, d.Pais, d.Slug,
                img.IdImagen AS IdImagenHero
         FROM reservas r
         JOIN detallereserva dr ON dr.IdReserva = r.IdReserva
         JOIN destinos d ON d.IdDestino = dr.IdDestino
         LEFT JOIN imagenes_destino img ON img.IdDestino = d.IdDestino AND img.Tipo = \'hero\'
         WHERE r.IdUsuario = :idUsuario
         ORDER BY r.FechaReserva DESC'
    );
    $stmt->execute(['idUsuario' => (int) $_SESSION['IdUsuario']]);

    $hoy = date('Y-m-d');

    foreach ($stmt->fetchAll() as $fila) {
        $yaViajo = $fila['FechaVuelta'] !== null && $fila['FechaVuelta'] < $hoy;
        $estado = $fila['Estado'] === 'pendiente'
            ? 'pendiente'
            : ($yaViajo ? 'finalizada' : 'confirmada');

        $reserva = [
            'id'           => (int) $fila['IdReserva'],
            'nombre'       => $fila['Nombre'] . ', ' . $fila['Pais'],
            'imagen'       => $fila['IdImagenHero'] ? '../logica/imagen.php?id=' . (int) $fila['IdImagenHero'] : '',
            'fecha_ida'    => $fila['FechaIda'],
            'fecha_vuelta' => $fila['FechaVuelta'],
            'pais'         => $fila['Pais'],
            'pasajeros'    => (int) $fila['CantidadPasajeros'],
            'estado'       => $estado,
            'precio'       => (float) $fila['Total'],
            'moneda'       => 'USD',
            'link_destino' => '../destinos/destino.php?slug=' . urlencode($fila['Slug']),
        ];

        if ($estado === 'finalizada') {
            $reservasPasadas[] = $reserva;
        } else {
            $reservasEnCurso[] = $reserva;
        }
    }
}

// ── Helpers ──────────────────────────────────────────────────
function formatearFecha(?string $fecha): string {
    if ($fecha === null || $fecha === '') {
        return 'A confirmar';
    }
    $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun',
              'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    [$y, $m, $d] = explode('-', $fecha);
    return (int)$d . ' ' . $meses[(int)$m] . '. ' . $y;
}

function badgeEstado(string $estado): string {
    return match ($estado) {
        'pendiente'   => '<span class="reserva-badge reserva-badge--pendiente"><i class="bi bi-clock me-1"></i>Pendiente de pago</span>',
        'confirmada'  => '<span class="reserva-badge reserva-badge--confirmada"><i class="bi bi-check-circle me-1"></i>Confirmada</span>',
        'finalizada'  => '<span class="reserva-badge reserva-badge--finalizada"><i class="bi bi-flag-fill me-1"></i>Finalizada</span>',
        default       => '',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mis reservas | Nómada</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../style.css?v=final"/>
  <style>
    /* ── Badges de estado ──────────────────────────────────── */
    .reserva-badge {
      display: inline-flex;
      align-items: center;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .05em;
      text-transform: uppercase;
      padding: .3rem .75rem;
      border-radius: 999px;
      white-space: nowrap;
    }
    .reserva-badge--pendiente  { background: #fff7e6; color: #b45309; border: 1px solid #fcd34d; }
    .reserva-badge--confirmada { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
    .reserva-badge--finalizada { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }

    /* ── Card horizontal de reserva ────────────────────────── */
    .reserva-card {
      display: flex;
      background: var(--surface, #fff);
      border: 1px solid var(--border, #e8e5e0);
      border-radius: 16px;
      overflow: hidden;
      transition: transform .2s ease, box-shadow .2s ease;
      box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .reserva-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 28px rgba(0,0,0,.11);
    }
    .reserva-card__img-wrap {
      flex: 0 0 220px;
      max-width: 220px;
      overflow: hidden;
      position: relative;
    }
    .reserva-card__img {
      width: 100%; height: 100%;
      object-fit: cover; display: block;
      transition: transform .4s ease;
    }
    .reserva-card:hover .reserva-card__img { transform: scale(1.04); }

    .reserva-card__body {
      flex: 1;
      padding: 1.5rem 1.75rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 1rem;
    }

    /* Cabecera */
    .reserva-card__header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .reserva-card__nombre {
      font-family: 'Playfair Display', serif;
      font-size: 1.25rem; font-weight: 700;
      color: var(--text-main, #1a1a1a);
      margin: 0 0 .3rem;
    }
    .reserva-card__id {
      font-size: .75rem;
      color: var(--text-light, #6b6b6b);
    }

    /* Precio */
    .reserva-card__precio-wrap { text-align: right; flex-shrink: 0; }
    .reserva-card__desde {
      font-size: .7rem; font-weight: 700;
      letter-spacing: .06em; text-transform: uppercase;
      color: var(--text-light, #6b6b6b);
    }
    .reserva-card__precio {
      font-family: 'Playfair Display', serif;
      font-size: 1.4rem; font-weight: 700;
      color: var(--text-main, #1a1a1a); white-space: nowrap;
    }
    .reserva-card__precio small {
      font-size: .75rem; font-weight: 400;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: var(--text-light, #6b6b6b);
    }

    /* Grilla de datos */
    .reserva-card__datos {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: .6rem 1.5rem;
    }
    .reserva-card__dato {
      display: flex; align-items: flex-start; gap: .45rem;
    }
    .reserva-card__dato > i {
      font-size: .9rem; color: var(--accent, #b8945a);
      margin-top: .15rem; flex-shrink: 0;
    }
    .reserva-card__dato-lbl {
      font-size: .68rem; font-weight: 700;
      letter-spacing: .06em; text-transform: uppercase;
      color: var(--text-light, #6b6b6b);
      display: block; line-height: 1; margin-bottom: .1rem;
    }
    .reserva-card__dato-val {
      font-size: .85rem;
      color: var(--text-main, #1a1a1a);
      display: block; line-height: 1.3;
    }

    /* Footer de la card */
    .reserva-card__footer {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: .75rem;
      padding-top: .875rem;
      border-top: 1px solid var(--border, #e8e5e0);
      flex-wrap: wrap;
    }

    /* Pantallas pequeñas */
    @media (max-width: 640px) {
      .reserva-card { flex-direction: column; }
      .reserva-card__img-wrap { flex: 0 0 180px; max-width: 100%; height: 180px; }
      .reserva-card__body { padding: 1.1rem 1.1rem; }
      .reserva-card__datos { grid-template-columns: 1fr; }
      .reserva-card__header { flex-direction: column; gap: .4rem; }
      .reserva-card__precio-wrap { text-align: left; }
    }

    /* ── Layout de la página ───────────────────────────────── */
    .mis-reservas-section { padding: 4.5rem 0 5rem; }
    .reservas-subseccion { margin-top: 3rem; }
    .reservas-subseccion-titulo {
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem; font-weight: 700;
      color: var(--text-main, #1a1a1a);
      margin-bottom: 1.25rem;
      padding-bottom: .6rem;
      border-bottom: 2px solid var(--border, #e8e5e0);
      display: flex; align-items: center; gap: .5rem;
    }
    .reservas-subseccion-titulo .contador {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: .75rem; font-weight: 700;
      background: var(--border, #e8e5e0);
      color: var(--text-light, #6b6b6b);
      border-radius: 999px;
      padding: .15rem .6rem;
    }
    .reservas-lista { display: flex; flex-direction: column; gap: 1.25rem; }

    /* Estado vacío */
    .reservas-empty {
      text-align: center;
      padding: 3rem 1rem;
      color: var(--text-light, #6b6b6b);
      border: 1px dashed var(--border, #e8e5e0);
      border-radius: 16px;
    }
    .reservas-empty i { font-size: 2.5rem; margin-bottom: .75rem; display: block; opacity: .35; }

    /* Sin sesión — minimalista */
    .no-sesion-wrap {
      text-align: center;
      padding: 4rem 1rem;
    }
    .no-sesion-wrap i { font-size: 1.75rem; color: var(--gris-700, #6b6b6b); margin-bottom: .75rem; display: block; opacity: .6; }
    .no-sesion-wrap p  { color: var(--gris-700, #6b6b6b); font-size: .95rem; margin-bottom: 1.25rem; }
    .btn-login-mini {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      background: var(--acento, #E8623A);
      color: var(--white, #fff);
      font-size: .8rem;
      font-weight: 600;
      padding: .45rem 1.1rem;
      border-radius: 999px;
      text-decoration: none;
      transition: background .2s;
    }
    .btn-login-mini i { color: var(--white, #fff); font-size: .85rem; }
    .btn-login-mini:hover { background: var(--acento-oscuro, #C94E28); color: var(--white, #fff); }

    /* ── Navbar siempre visible: esta página no tiene hero detrás, así
       que el navbar transparente (pensado para flotar sobre el hero)
       quedaría invisible hasta hacer scroll. Lo fijamos "sólido" siempre. */
    .navbar-nomada {
      background: var(--white, #fff);
      box-shadow: 0 1px 0 var(--border, #e8e5e0), 0 2px 8px rgba(26,77,110,.08);
      padding: .7rem 0;
    }
    .navbar-nomada .nav-logo { color: var(--azul-principal, #1A4D6E) !important; }
    .navbar-nomada .nav-link-item { color: var(--gris-700, #3E5468) !important; }
    .navbar-nomada .nav-link-item:hover { color: var(--azul-principal, #1A4D6E) !important; }
    .navbar-nomada .btn-login-nav {
      border-color: var(--azul-principal, #1A4D6E);
      color: var(--azul-principal, #1A4D6E);
    }
    .navbar-nomada .btn-login-nav:hover {
      background: var(--azul-principal, #1A4D6E);
      color: var(--white, #fff);
    }
  </style>
</head>
<body>

<!-- ── NAVBAR ── -->
<nav class="navbar-nomada navbar navbar-expand-lg px-3 px-lg-5">
  <div class="container-fluid">
    <a class="nav-logo" href="../index.php">Nómada<span class="dot">.</span></a>
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="navMenu">
      <ul class="navbar-nav gap-0 mx-auto">
        <li class="nav-item"><a class="nav-link-item nav-link" href="../index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link-item nav-link" href="../index.php#destinos">Destinos</a></li>
        <li class="nav-item"><a class="nav-link-item nav-link" href="../index.php#paquetes">Paquetes</a></li>
        <li class="nav-item"><a class="nav-link-item nav-link active" href="mis-reservas.php">Mis reservas</a></li>
        <li class="nav-item"><a class="nav-link-item nav-link" href="../index.php#contacto">Contacto</a></li>
      </ul>
      <?php if (!empty($_SESSION['IdUsuario'])) : ?>
        <div class="d-flex align-items-center gap-3 mt-2 mt-lg-0">
          <span class="nav-link-item" style="cursor:default;">
            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['NombreUsuario'] ?? 'Mi cuenta'); ?>
          </span>
          <a href="../login/logout.php" class="btn btn-login-nav">Cerrar sesión</a>
        </div>
      <?php else : ?>
        <a href="../login/login.php" class="btn btn-login-nav mt-2 mt-lg-0">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- ── CONTENIDO ── -->
<main class="mis-reservas-section">
  <div class="container">

    <?php if (empty($_SESSION['IdUsuario'])) : ?>
      <!-- ── Sin sesión ── -->
      <div class="no-sesion-wrap">
        <i class="bi bi-lock"></i>
        <p>Debés iniciar sesión para ver tus reservas.</p>
        <a href="../login/login.php?redirect=../logica/mis-reservas.php" class="btn-login-mini">
          <i class="bi bi-box-arrow-in-right"></i>Iniciar sesión
        </a>
      </div>

    <?php else : ?>
      <!-- ── Con sesión ── -->
      <div class="row mb-2">
        <div class="col">
          <span class="section-label">Tu cuenta</span>
          <h1 class="section-title">Mis reservas</h1>
          <p class="section-sub">Revisá el estado de tus viajes confirmados y pasados.</p>
        </div>
      </div>

      <!-- ─────────────────────────────────────────────────────
           MACRO reutilizable: tarjeta de reserva
           Recibe $reserva (array) y $tipo ('en_curso' | 'pasada')
      ──────────────────────────────────────────────────────── -->
      <?php
      /**
       * Renderiza una card horizontal de reserva.
       *
       * @param array  $reserva   Datos de la reserva (ver arrays de arriba).
       * @param string $tipo      'en_curso' | 'pasada'
       *
       * TODO: cuando conectes la BD, asegurate de que $reserva tenga
       *       exactamente los mismos keys que usa esta función.
       */
      function renderReservaCard(array $reserva, string $tipo): void {
          $labelPrecio = $tipo === 'pasada' ? 'Total abonado' : 'Total';
      ?>
        <div class="reserva-card">

          <!-- Imagen -->
          <div class="reserva-card__img-wrap">
            <img
              src="<?php echo htmlspecialchars($reserva['imagen']); ?>"
              alt="<?php echo htmlspecialchars($reserva['nombre']); ?>"
              class="reserva-card__img"
              loading="lazy"
            />
          </div>

          <!-- Cuerpo -->
          <div class="reserva-card__body">

            <!-- Cabecera: nombre + precio -->
            <div class="reserva-card__header">
              <div>
                <h2 class="reserva-card__nombre"><?php echo htmlspecialchars($reserva['nombre']); ?></h2>
                <span class="reserva-card__id">Reserva #<?php echo (int) $reserva['id']; ?></span>
              </div>
              <div class="reserva-card__precio-wrap">
                <span class="reserva-card__desde"><?php echo htmlspecialchars($labelPrecio); ?></span>
                <div class="reserva-card__precio">
                  $<?php echo number_format((float) $reserva['precio'], 0, ',', '.'); ?>
                  <small><?php echo htmlspecialchars($reserva['moneda']); ?></small>
                </div>
              </div>
            </div>

            <!-- Datos -->
            <div class="reserva-card__datos">
              <div class="reserva-card__dato">
                <i class="bi bi-calendar3"></i>
                <div>
                  <span class="reserva-card__dato-lbl">Fechas</span>
                  <span class="reserva-card__dato-val">
                    <?php echo formatearFecha($reserva['fecha_ida']); ?> → <?php echo formatearFecha($reserva['fecha_vuelta']); ?>
                  </span>
                </div>
              </div>
              <div class="reserva-card__dato">
                <i class="bi bi-geo-alt"></i>
                <div>
                  <span class="reserva-card__dato-lbl">País</span>
                  <span class="reserva-card__dato-val"><?php echo htmlspecialchars($reserva['pais']); ?></span>
                </div>
              </div>
              <div class="reserva-card__dato">
                <i class="bi bi-people"></i>
                <div>
                  <span class="reserva-card__dato-lbl">Pasajeros</span>
                  <span class="reserva-card__dato-val"><?php echo (int) $reserva['pasajeros']; ?></span>
                </div>
              </div>
              <div class="reserva-card__dato">
                <i class="bi bi-info-circle"></i>
                <div>
                  <span class="reserva-card__dato-lbl">Estado</span>
                  <span class="reserva-card__dato-val"><?php echo badgeEstado($reserva['estado']); ?></span>
                </div>
              </div>
            </div>

            <!-- Footer: botones -->
            <?php if ($reserva['estado'] === 'pendiente') : ?>
              <div class="reserva-card__footer">
                <form method="post" action="pagar_reserva.php">
                  <input type="hidden" name="id_reserva" value="<?php echo (int) $reserva['id']; ?>"/>
                  <button type="submit" class="btn btn-search" style="font-size:.82rem; padding:.45rem 1.1rem;">
                    <i class="bi bi-credit-card me-1"></i>Ir a pagar
                  </button>
                </form>
              </div>
            <?php endif; ?>

          </div><!-- /body -->
        </div><!-- /reserva-card -->
      <?php } /* fin renderReservaCard */ ?>

      <!-- ── Reservas en curso ── -->
      <div class="reservas-subseccion">
        <h2 class="reservas-subseccion-titulo">
          <i class="bi bi-airplane-fill"></i>
          Reservas en curso
          <span class="contador"><?php echo count($reservasEnCurso); ?></span>
        </h2>
        <?php if (empty($reservasEnCurso)) : ?>
          <div class="reservas-empty">
            <i class="bi bi-suitcase2"></i>
            <p class="mb-0">No tenés reservas activas por el momento.</p>
          </div>
        <?php else : ?>
          <div class="reservas-lista">
            <?php foreach ($reservasEnCurso as $reserva) : ?>
              <?php renderReservaCard($reserva, 'en_curso'); ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── Reservas pasadas ── -->
      <div class="reservas-subseccion">
        <h2 class="reservas-subseccion-titulo">
          <i class="bi bi-clock-history"></i>
          Reservas pasadas
          <span class="contador"><?php echo count($reservasPasadas); ?></span>
        </h2>
        <?php if (empty($reservasPasadas)) : ?>
          <div class="reservas-empty">
            <i class="bi bi-archive"></i>
            <p class="mb-0">Todavía no tenés viajes completados.</p>
          </div>
        <?php else : ?>
          <div class="reservas-lista">
            <?php foreach ($reservasPasadas as $reserva) : ?>
              <?php renderReservaCard($reserva, 'pasada'); ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    <?php endif; /* fin if sesión */ ?>

  </div>
</main>

<!-- ── FOOTER ── -->
<footer id="contacto">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-3 col-md-6">
        <a class="footer-logo" href="../index.php">Nómada<span class="dot">.</span></a>
        <p class="footer-tagline">Transformamos rutas en recuerdos. Viajes que te cambian desde adentro.</p>
        <div class="footer-socials">
          <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-6 col-6">
        <div class="footer-col-title">Destinos</div>
        <ul class="footer-list">
          <li><a href="#">Caribe</a></li>
          <li><a href="#">Europa</a></li>
          <li><a href="#">Asia</a></li>
          <li><a href="#">EEUU y Canadá</a></li>
          <li><a href="#">Sudamérica</a></li>
          <li><a href="#">Oceanía</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-6 col-6">
        <div class="footer-col-title">Servicios</div>
        <ul class="footer-list">
          <li><a href="#">Paquetes</a></li>
          <li><a href="#">Vuelos</a></li>
          <li><a href="#">Hoteles</a></li>
          <li><a href="#">Traslados</a></li>
          <li><a href="#">Seguros de viaje</a></li>
          <li><a href="#">Cruceros</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-6 col-6">
        <div class="footer-col-title">Empresa</div>
        <ul class="footer-list">
          <li><a href="#">Sobre Nómada</a></li>
          <li><a href="#">Trabajá con nosotros</a></li>
          <li><a href="#">Blog de viajes</a></li>
          <li><a href="#">Prensa</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="footer-col-title">Contacto</div>
        <div class="footer-contact-row">
          <i class="bi bi-geo-alt-fill"></i>
          <span>Av. Corrientes 1234, Piso 8<br>CABA, Argentina</span>
        </div>
        <div class="footer-contact-row">
          <i class="bi bi-telephone-fill"></i>
          <span>+54 11 4800-1234</span>
        </div>
        <div class="footer-contact-row">
          <i class="bi bi-envelope-fill"></i>
          <span>hola@nomada.com.ar</span>
        </div>
        <div class="footer-contact-row">
          <i class="bi bi-whatsapp"></i>
          <span>+54 9 11 3456-7890</span>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2025 Nómada Viajes S.A. Legajo IATA 12-3 45678/0</span>
      <div class="d-flex gap-3 flex-wrap justify-content-center">
        <a href="#">Términos y condiciones</a>
        <a href="#">Privacidad</a>
        <a href="#">Cookies</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
