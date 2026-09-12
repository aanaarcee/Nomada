<?php
session_start();
require_once __DIR__ . '/../logica/conexion.php';
require_once __DIR__ . '/../logica/Paquete.php';

$slug = $_GET['slug'] ?? '';
$paquete = $slug !== '' ? Paquete::buscarPorSlug($pdo, $slug) : null;

if (!$paquete) {
    header('Location: ../index.php?no_disponible=1#ofertas');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Paquete — <?php echo htmlspecialchars($paquete->nombre); ?> | Nómada</title>
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
        <li class="nav-item"><a class="nav-link-item nav-link" href="../index.php#ofertas">Paquetes</a></li>
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
          <a href="../login/login.php?redirect=<?php echo urlencode('../paquetes/paquete.php?slug=' . $paquete->slug); ?>" class="btn btn-login-nav">Iniciar sesión</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<section class="hero-dest">
  <div class="hero-bg" style="background-image: url('<?php echo $paquete->heroImagenId ? '../logica/imagen_paquete.php?id=' . $paquete->heroImagenId : ''; ?>');"></div>
  <div class="container hero-inner py-5">
    <div class="row align-items-end">
      <div class="col-lg-7">
        <span class="dest-badge">−<?php echo $paquete->descuentoPorcentaje(); ?>% OFF</span>
        <h1 class="hero-title"><?php echo htmlspecialchars($paquete->nombre); ?></h1>
        <p class="hero-sub"><?php echo htmlspecialchars($paquete->descripcion); ?></p>
        <?php if ($paquete->caracteristicas) : ?>
          <div class="offer-meta mt-3">
            <?php foreach ($paquete->caracteristicas as $caracteristica) : ?>
              <div class="offer-meta-item"><i class="bi <?php echo htmlspecialchars($caracteristica['Icono']); ?>"></i> <?php echo htmlspecialchars($caracteristica['Texto']); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="col-lg-5 text-lg-end">
        <div class="dest-info-card mt-4 mt-lg-0">
          <div class="dest-meta mb-3">
            <div class="dest-label">Precio por persona</div>
            <div class="offer-old">USD <?php echo $paquete->precioOriginalFormateado(); ?></div>
            <div class="dest-value">$<?php echo $paquete->precioOfertaFormateada(); ?> <small><?php echo htmlspecialchars($paquete->moneda); ?></small></div>
          </div>
          <div class="dest-meta mb-3">
            <div class="dest-label">Duración</div>
            <div class="dest-value"><?php echo $paquete->noches; ?> noches</div>
          </div>
          <div class="dest-meta">
            <div class="dest-label">Descuento</div>
            <div class="dest-value"><?php echo $paquete->descuentoPorcentaje(); ?>% OFF</div>
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
          <div class="section-label">Detalle del paquete</div>
          <h2 class="section-title">Qué incluye esta oferta</h2>
          <p class="section-sub">Conoce todo lo que este paquete tiene preparado para vos.</p>

          <?php if ($paquete->tieneContenidoCompleto()) : ?>
            <div class="row gy-4 mt-4">
              <div class="col-md-6">
                <div class="dest-info-card p-4 h-100">
                  <h3 class="mb-3">Servicios incluidos</h3>
                  <ul class="list-unstyled m-0">
                    <?php foreach ($paquete->servicios as $servicio) : ?>
                      <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><?php echo htmlspecialchars($servicio); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
              <div class="col-md-6">
                <div class="dest-info-card p-4 h-100">
                  <h3 class="mb-3">Actividades destacadas</h3>
                  <ul class="list-unstyled m-0">
                    <?php foreach ($paquete->actividades as $actividad) : ?>
                      <li class="mb-2"><i class="bi bi-star-fill text-warning me-2"></i><?php echo htmlspecialchars($actividad); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </div>

            <div class="mt-5">
              <h3 class="mb-3">Itinerario</h3>
              <div class="timeline">
                <?php foreach ($paquete->itinerario as $index => $item) : ?>
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

            <?php if ($paquete->galeriaImagenIds) : ?>
              <div class="mt-5">
                <h3 class="mb-3">Galería</h3>
                <div class="gallery-grid">
                  <?php foreach ($paquete->galeriaImagenIds as $idImagen) : ?>
                    <img src="../logica/imagen_paquete.php?id=<?php echo $idImagen; ?>" alt="Galería de <?php echo htmlspecialchars($paquete->nombre); ?>" loading="lazy"/>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          <?php else : ?>
            <div class="dest-info-card p-4 mt-4">
              <h3 class="mb-2">Estamos preparando esta oferta</h3>
              <p class="text-muted mb-0">Todavía estamos cargando el detalle completo de <?php echo htmlspecialchars($paquete->nombre); ?>. Consultanos y te contamos todo.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-lg-4">
          <div class="dest-info-card p-4 sticky-top" style="top: 92px;">
            <h3 class="mb-3">¿Te interesa este paquete?</h3>
            <p class="text-muted">Esta oferta vence en:</p>
            <div class="countdown mb-4" data-fin="<?php echo htmlspecialchars(str_replace(' ', 'T', $paquete->finOferta ?? '')); ?>">
              <div class="cdblock"><div class="cdnum" data-dias>00</div><div class="cdlbl">días</div></div>
              <div class="cdblock"><div class="cdnum" data-horas>00</div><div class="cdlbl">hs</div></div>
              <div class="cdblock"><div class="cdnum" data-minutos>00</div><div class="cdlbl">min</div></div>
              <div class="cdblock"><div class="cdnum" data-segundos>00</div><div class="cdlbl">seg</div></div>
            </div>
            <div class="d-grid gap-2">
              <a href="#" class="btn btn-cta-white" style="color:#1a1a1a; border:1px solid var(--border,#e8e5e0);">
                <i class="bi bi-whatsapp me-2"></i>Consultar por WhatsApp
              </a>
            </div>
            <p class="text-muted mt-3 mb-0" style="font-size:.8rem;">* Precio final sujeto a disponibilidad y fecha de viaje.</p>
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
        <a href="../index.php#ofertas" class="btn btn-cta-outline">Ver otras ofertas</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const cd = document.querySelector('.countdown');
  if (cd && cd.dataset.fin) {
    const fin = new Date(cd.dataset.fin).getTime();
    const pad = n => String(Math.max(0, n)).padStart(2, '0');
    function tick() {
      const diff = Math.max(0, fin - Date.now());
      cd.querySelector('[data-dias]').textContent = pad(Math.floor(diff / 86400000));
      cd.querySelector('[data-horas]').textContent = pad(Math.floor((diff % 86400000) / 3600000));
      cd.querySelector('[data-minutos]').textContent = pad(Math.floor((diff % 3600000) / 60000));
      cd.querySelector('[data-segundos]').textContent = pad(Math.floor((diff % 60000) / 1000));
    }
    tick();
    setInterval(tick, 1000);
  }
</script>
</body>
</html>
