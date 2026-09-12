<?php

// Una fila de la tabla `experiencias`: un paquete corto agrupado por
// categoría (Playa, Montaña, Aventura, Gastronomía, Familiar). A
// diferencia de Destino/Paquete no tiene página propia por slug; se
// listan varias juntas en paquetes/experiencias.php?categoria=Playa.
class Experiencia
{
    public int $id = 0;
    public string $categoria = '';
    public string $nombre = '';
    public string $pais = '';
    public string $duracion = '';
    public string $temporada = '';
    public string $hotel = '';
    public int $pasajeros = 0;
    public string $excursiones = '';
    public float $precio = 0.0;
    public string $moneda = 'USD';
    public string $link = '#';

    /** @return Experiencia[] */
    public static function listarPorCategoria(PDO $pdo, string $categoria): array
    {
        $stmt = $pdo->prepare('SELECT * FROM experiencias WHERE Categoria = :categoria ORDER BY IdExperiencia ASC');
        $stmt->execute(['categoria' => $categoria]);

        return array_map([self::class, 'desdeFila'], $stmt->fetchAll());
    }

    private static function desdeFila(array $fila): self
    {
        $experiencia = new self();
        $experiencia->id = (int) $fila['IdExperiencia'];
        $experiencia->categoria = $fila['Categoria'];
        $experiencia->nombre = $fila['Nombre'];
        $experiencia->pais = $fila['Pais'];
        $experiencia->duracion = $fila['Duracion'];
        $experiencia->temporada = $fila['Temporada'];
        $experiencia->hotel = $fila['Hotel'];
        $experiencia->pasajeros = (int) $fila['Pasajeros'];
        $experiencia->excursiones = $fila['Excursiones'] ?? '-';
        $experiencia->precio = (float) $fila['Precio'];
        $experiencia->moneda = $fila['Moneda'] ?? 'USD';
        $experiencia->link = $fila['Link'] ?? '#';

        return $experiencia;
    }

    public function precioFormateado(): string
    {
        return number_format($this->precio, 0, ',', '.');
    }

    public function pasajerosTexto(): string
    {
        return $this->pasajeros . ' pasajero' . ($this->pasajeros > 1 ? 's' : '');
    }
}
