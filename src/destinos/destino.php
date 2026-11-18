<?php
session_start();
require_once __DIR__ . '/../logica/conexion.php';
require_once __DIR__ . '/../logica/Destino.php';

$slug = $_GET['slug'] ?? '';
$destino = $slug !== '' ? Destino::buscarPorSlug($pdo, $slug) : null;

// El destino no existe en la base: volvemos al listado, igual que hacía
// buscar.php antes de que existiera esta plantilla.
if (!$destino) {
    header('Location: ../index.php?no_disponible=1#destinos');
    exit;
}

$volverA = '../destinos/destino.php?slug=' . urlencode($destino->slug);

$origenPrefill = $_GET['origen'] ?? '';
$fechaIdaPrefill = $_GET['fecha_ida'] ?? '';
$fechaVueltaPrefill = $_GET['fecha_vuelta'] ?? '';
$pasajerosPrefill = $_GET['pasajeros'] ?? '1';

$mensaje = null;
$mensajeTipo = 'info';
if (isset($_GET['ok']) && $_GET['ok'] === 'carrito') {
    $mensaje = 'Se agregó al carrito correctamente.';
    $mensajeTipo = 'success';
} elseif (isset($_GET['ok']) && $_GET['ok'] === 'comprar') {
    $mensaje = '¡Reserva confirmada con éxito!';
    $mensajeTipo = 'success';
} elseif (isset($_GET['error'])) {
    $mensaje = 'No pudimos procesar tu pedido. Iniciá sesión e intentá de nuevo.';
    $mensajeTipo = 'danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Destino — <?php echo htmlspecialchars($destino->nombre); ?> | Nómada</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../style.css?v=final"/>
</head>
<body>

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
        <li class="nav-item"><a class="nav-link-item nav-link" href="../logica/mis-reservas.php">Mis reservas</a></li>
        <li class="nav-item"><a class="nav-link-item nav-link" href="../index.php#contacto">Contacto</a></li>
      </ul>
      <div class="d-flex align-items-center gap-3 mt-2 mt-lg-0">
        <?php if (!empty($_SESSION['IdUsuario'])) : ?>
          <span class="nav-link-item" style="cursor:default;">
            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['NombreUsuario'] ?? 'Mi cuenta'); ?>
          </span>
          <a href="../login/logout.php" class="btn btn-login-nav">Cerrar sesión</a>
        <?php else : ?>
          <a href="../login/login.php?redirect=<?php echo urlencode($volverA); ?>" class="btn btn-login-nav">Iniciar sesión</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<section class="hero-dest">
  <div class="hero-bg" style="background-image: url('<?php echo $destino->heroImagenId ? '../logica/imagen.php?id=' . $destino->heroImagenId : ''; ?>');"></div>
  <div class="container hero-inner py-5">
    <div class="row align-items-end">
      <div class="col-lg-7">
        <span class="dest-badge"><?php echo htmlspecialchars($destino->categoria); ?></span>
        <h1 class="hero-title"><?php echo htmlspecialchars($destino->nombre); ?>, <?php echo htmlspecialchars($destino->pais); ?></h1>
        <p class="hero-sub"><?php echo htmlspecialchars($destino->descripcion); ?></p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <div class="dest-info-card mt-4 mt-lg-0">
          <div class="dest-meta mb-3">
            <div class="dest-label">Precio desde</div>
            <div class="dest-value">$<?php echo $destino->precioFormateado(); ?> <small><?php echo htmlspecialchars($destino->moneda); ?></small></div>
          </div>
          <div class="dest-meta mb-3">
            <div class="dest-label">Categoría</div>
            <div class="dest-value"><?php echo htmlspecialchars($destino->categoria); ?></div>
          </div>
          <div class="dest-meta">
            <div class="dest-label">País</div>
            <div class="dest-value"><?php echo htmlspecialchars($destino->pais); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<main>
  <section class="dest-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="section-label">Detalle del destino</div>
          <h2 class="section-title">Qué incluye esta experiencia</h2>
          <p class="section-sub">Conoce las mejores actividades y servicios que te esperan en este paquete pensado para disfrutar sin preocupaciones.</p>

          <?php if ($destino->tieneContenidoCompleto()) : ?>
            <div class="row gy-4 mt-4">
              <div class="col-md-6">
                <div class="dest-info-card p-4 h-100">
                  <h3 class="mb-3">Servicios incluidos</h3>
                  <ul class="list-unstyled m-0">
                    <?php foreach ($destino->servicios as $servicio) : ?>
                      <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><?php echo htmlspecialchars($servicio); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
              <div class="col-md-6">
                <div class="dest-info-card p-4 h-100">
                  <h3 class="mb-3">Actividades destacadas</h3>
                  <ul class="list-unstyled m-0">
                    <?php foreach ($destino->actividades as $actividad) : ?>
                      <li class="mb-2"><i class="bi bi-star-fill text-warning me-2"></i><?php echo htmlspecialchars($actividad); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </div>

            <div class="mt-5">
              <h3 class="mb-3">Itinerario</h3>
              <div class="timeline">
                <?php foreach ($destino->itinerario as $index => $item) : ?>
                  <div class="timeline-item" style="--i:<?php echo $index; ?>;">
                    <div class="timeline-marker">
                      <span class="timeline-badge"><?php echo $index + 1; ?></span>
                    </div>
                    <div class="timeline-card">
                      <div class="timeline-card-header">
                        <span class="timeline-day"><?php echo htmlspecialchars($item['Dia']); ?></span>
                        <h4 class="timeline-title"><?php echo htmlspecialchars($item['Titulo']); ?></h4>
                      </div>
                      <p class="timeline-desc"><?php echo htmlspecialchars($item['Detalle']); ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <?php if ($destino->galeriaImagenIds) : ?>
              <div class="mt-5">
                <h3 class="mb-3">Galería</h3>
                <div class="gallery-grid">
                  <?php foreach ($destino->galeriaImagenIds as $idImagen) : ?>
                    <img src="../logica/imagen.php?id=<?php echo $idImagen; ?>" alt="Galería de <?php echo htmlspecialchars($destino->nombre); ?>" loading="lazy"/>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          <?php else : ?>
            <div class="dest-info-card p-4 mt-4">
              <h3 class="mb-2">Estamos preparando esta experiencia</h3>
              <p class="text-muted mb-0">Todavía estamos cargando el itinerario completo de <?php echo htmlspecialchars($destino->nombre); ?>. Podés reservar tu lugar ahora mismo y te contactamos con todos los detalles.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-lg-4">
          <div id="reserva" class="dest-info-card p-4 sticky-top" style="top: 92px;">
            <h3 class="mb-3">Reserva tu viaje</h3>
            <p class="text-muted">Completá los datos para reservar tu lugar.</p>
            <?php if ($mensaje) : ?>
              <div class="alert alert-<?php echo htmlspecialchars($mensajeTipo); ?>"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            <form method="post" action="../logica/guardar_reserva.php">
              <input type="hidden" name="id_destino" value="<?php echo $destino->id; ?>"/>
              <input type="hidden" name="volver_a" value="<?php echo htmlspecialchars($volverA); ?>"/>
              <div class="mb-3">
                <label class="form-label">Origen</label>
                <select class="form-select" name="origen">
                  <option value="" <?php echo $origenPrefill === '' ? 'selected' : ''; ?>>Seleccioná tu ciudad</option>
                  <?php foreach (['Buenos Aires (EZE)', 'Córdoba (COR)', 'Rosario (ROS)', 'Mendoza (MDZ)'] as $ciudad) : ?>
                    <option value="<?php echo htmlspecialchars($ciudad); ?>" <?php echo $origenPrefill === $ciudad ? 'selected' : ''; ?>><?php echo htmlspecialchars($ciudad); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Fecha de ida</label>
                <input type="date" class="form-control" name="fecha_ida" value="<?php echo htmlspecialchars($fechaIdaPrefill); ?>"/>
              </div>
              <div class="mb-3">
                <label class="form-label">Fecha de vuelta</label>
                <input type="date" class="form-control" name="fecha_vuelta" value="<?php echo htmlspecialchars($fechaVueltaPrefill); ?>"/>
              </div>
              <div class="mb-3">
                <label class="form-label">Pasajeros</label>
                <select class="form-select" name="pasajeros">
                  <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <option value="<?php echo $i; ?>" <?php echo (string) $pasajerosPrefill === (string) $i ? 'selected' : ''; ?>><?php echo $i; ?> pasajero<?php echo $i > 1 ? 's' : ''; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="d-grid gap-2">
                <button type="submit" name="accion" value="comprar" class="btn btn-search">Reservar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<footer class="mt-5">
  <div class="container py-5">
    <div class="row">
      <div class="col-lg-8">
        <p class="mb-1"><strong>¿Necesitás ayuda?</strong></p>
        <p class="text-muted">Contactanos para recibir asesoramiento personalizado o reservar tu viaje con un agente.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="../index.php#destinos" class="btn btn-cta-outline">Ver otros destinos</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
