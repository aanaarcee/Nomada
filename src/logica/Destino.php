<?php

// Representa un destino con todo lo que la página de detalle necesita
// mostrar. Antes cada destino era un archivo PHP con su propio HTML;
// ahora un destino es un dato (esta clase) que se dibuja siempre con
// la misma plantilla (destinos/destino.php).
class Destino
{
    public int $id = 0;
    public string $nombre = '';
    public string $slug = '';
    public string $pais = '';
    public string $categoria = '';
    public float $precio = 0.0;
    public string $moneda = 'USD';
    public string $descripcion = '';

    public ?int $heroImagenId = null;

    /** @var int[] */
    public array $galeriaImagenIds = [];

    /** @var string[] */
    public array $servicios = [];

    /** @var string[] */
    public array $actividades = [];

    /** @var array<int, array{Dia: string, Titulo: string, Detalle: string}> */
    public array $itinerario = [];

    public static function buscarPorSlug(PDO $pdo, string $slug): ?self
    {
        $stmt = $pdo->prepare(
            'SELECT d.*, c.Nombre AS CategoriaNombre
             FROM destinos d
             JOIN categorias c ON c.IdCategoria = d.IdCategoria
             WHERE d.Slug = :slug'
        );
        $stmt->execute(['slug' => $slug]);
        $fila = $stmt->fetch();

        return $fila ? self::desdeFila($pdo, $fila) : null;
    }

    public static function buscarPorId(PDO $pdo, int $id): ?self
    {
        $stmt = $pdo->prepare(
            'SELECT d.*, c.Nombre AS CategoriaNombre
             FROM destinos d
             JOIN categorias c ON c.IdCategoria = d.IdCategoria
             WHERE d.IdDestino = :id'
        );
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila ? self::desdeFila($pdo, $fila) : null;
    }

    private static function desdeFila(PDO $pdo, array $fila): self
    {
        $destino = new self();
        $destino->id = (int) $fila['IdDestino'];
        $destino->nombre = $fila['Nombre'];
        $destino->slug = $fila['Slug'];
        $destino->pais = $fila['Pais'];
        $destino->categoria = $fila['CategoriaNombre'];
        $destino->precio = (float) $fila['Precio'];
        $destino->moneda = $fila['Moneda'] ?? 'USD';
        $destino->descripcion = $fila['Descripcion'] ?? '';

        $stmt = $pdo->prepare(
            'SELECT IdImagen, Tipo FROM imagenes_destino WHERE IdDestino = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $destino->id]);
        foreach ($stmt->fetchAll() as $imagen) {
            if ($imagen['Tipo'] === 'hero' && $destino->heroImagenId === null) {
                $destino->heroImagenId = (int) $imagen['IdImagen'];
            } else {
                $destino->galeriaImagenIds[] = (int) $imagen['IdImagen'];
            }
        }

        $stmt = $pdo->prepare(
            'SELECT Descripcion FROM servicios_destino WHERE IdDestino = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $destino->id]);
        $destino->servicios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare(
            'SELECT Descripcion FROM actividades_destino WHERE IdDestino = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $destino->id]);
        $destino->actividades = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare(
            'SELECT Dia, Titulo, Detalle FROM itinerario_destino WHERE IdDestino = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $destino->id]);
        $destino->itinerario = $stmt->fetchAll();

        return $destino;
    }

    public function precioFormateado(): string
    {
        return number_format($this->precio, 0, ',', '.');
    }

    // true cuando ya se cargó itinerario/servicios/actividades para este
    // destino. Si es false, la plantilla muestra un aviso de "en
    // preparación" en vez de secciones vacías, pero igual permite reservar.
    public function tieneContenidoCompleto(): bool
    {
        return $this->servicios !== [] && $this->actividades !== [] && $this->itinerario !== [];
    }
}
