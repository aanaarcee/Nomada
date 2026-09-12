<?php

// Igual que Destino: un paquete es un dato, no un archivo. La plantilla
// paquetes/paquete.php dibuja cualquier paquete a partir de un objeto de
// esta clase.
class Paquete
{
    public int $id = 0;
    public string $nombre = '';
    public string $slug = '';
    public string $descripcion = '';
    public float $precioOriginal = 0.0;
    public float $precioOferta = 0.0;
    public string $moneda = 'USD';
    public int $noches = 0;
    public ?string $finOferta = null; // datetime 'Y-m-d H:i:s' o null si no hay oferta con vencimiento

    public ?int $heroImagenId = null;

    /** @var int[] */
    public array $galeriaImagenIds = [];

    /** @var string[] */
    public array $servicios = [];

    /** @var string[] */
    public array $actividades = [];

    /** @var array<int, array{Dia: string, Titulo: string, Detalle: string}> */
    public array $itinerario = [];

    /** @var array<int, array{Icono: string, Texto: string}> */
    public array $caracteristicas = [];

    public static function buscarPorSlug(PDO $pdo, string $slug): ?self
    {
        $stmt = $pdo->prepare('SELECT * FROM paquetes WHERE Slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $fila = $stmt->fetch();

        return $fila ? self::desdeFila($pdo, $fila) : null;
    }

    private static function desdeFila(PDO $pdo, array $fila): self
    {
        $paquete = new self();
        $paquete->id = (int) $fila['IdPaquete'];
        $paquete->nombre = $fila['Nombre'];
        $paquete->slug = $fila['Slug'];
        $paquete->descripcion = $fila['Descripcion'] ?? '';
        $paquete->precioOriginal = (float) $fila['PrecioOriginal'];
        $paquete->precioOferta = (float) $fila['PrecioOferta'];
        $paquete->moneda = $fila['Moneda'] ?? 'USD';
        $paquete->noches = (int) $fila['Noches'];
        $paquete->finOferta = $fila['FinOferta'];

        $stmt = $pdo->prepare(
            'SELECT IdImagen, Tipo FROM imagenes_paquete WHERE IdPaquete = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $paquete->id]);
        foreach ($stmt->fetchAll() as $imagen) {
            if ($imagen['Tipo'] === 'hero' && $paquete->heroImagenId === null) {
                $paquete->heroImagenId = (int) $imagen['IdImagen'];
            } else {
                $paquete->galeriaImagenIds[] = (int) $imagen['IdImagen'];
            }
        }

        $stmt = $pdo->prepare(
            'SELECT Descripcion FROM servicios_paquete WHERE IdPaquete = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $paquete->id]);
        $paquete->servicios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare(
            'SELECT Descripcion FROM actividades_paquete WHERE IdPaquete = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $paquete->id]);
        $paquete->actividades = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare(
            'SELECT Dia, Titulo, Detalle FROM itinerario_paquete WHERE IdPaquete = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $paquete->id]);
        $paquete->itinerario = $stmt->fetchAll();

        $stmt = $pdo->prepare(
            'SELECT Icono, Texto FROM caracteristicas_paquete WHERE IdPaquete = :id ORDER BY Orden ASC'
        );
        $stmt->execute(['id' => $paquete->id]);
        $paquete->caracteristicas = $stmt->fetchAll();

        return $paquete;
    }

    public function descuentoPorcentaje(): int
    {
        if ($this->precioOriginal <= 0) {
            return 0;
        }
        return (int) round((1 - $this->precioOferta / $this->precioOriginal) * 100);
    }

    public function precioOriginalFormateado(): string
    {
        return number_format($this->precioOriginal, 0, ',', '.');
    }

    public function precioOfertaFormateada(): string
    {
        return number_format($this->precioOferta, 0, ',', '.');
    }

    public function tieneContenidoCompleto(): bool
    {
        return $this->servicios !== [] && $this->actividades !== [] && $this->itinerario !== [];
    }
}
