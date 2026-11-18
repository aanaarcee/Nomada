# Nomada - Proyecto Web

Sitio web de turismo desarrollado en **PHP** con **programación orientada a objetos (POO)**, basado en una consigna del torneo del INET. El foco del proyecto está en la lógica POO del backend más que en el diseño.

Demo: https://nomadaviaje.duckdns.org

## Lo que lo hace especial

En lugar de tener un archivo por cada página (una para cada destino, una para cada paquete, etc.), el proyecto **reutiliza una única plantilla** con el diseño y **cambia solo los datos según el destino o paquete** recibido, que se consultan a la base de datos. Agregar un destino o un paquete nuevo no requiere crear ningún archivo: alcanza con cargarlo en la base.

## Características

- Arquitectura orientada a objetos (clases separadas de la vista).
- Páginas dinámicas con una sola plantilla por sección (destinos, paquetes, experiencias).
- Contenido e imágenes cargados desde base de datos (las imágenes se guardan como BLOB).
- Sistema de login, carrito y reservas.
- Despliegue con Docker Compose: un solo comando levanta la web y la base con todos los datos.

## Tecnologías

- PHP 8.3 + Apache
- MySQL 8.4
- HTML, CSS y Bootstrap 5
- Docker y Docker Compose

## Estructura del proyecto

```
Nomada/
├── docker-compose.yml   # web (PHP + Apache) y base de datos (MySQL)
├── Dockerfile           # imagen de la web
├── apache-nomada.conf   # configuración de Apache
├── .env.example         # plantilla de usuario y contraseñas de la base
├── instalar.sh          # (opcional) recarga los datos desde los scripts PHP
├── db/
│   └── 01_nomada.sql    # estructura + datos + imágenes; se carga sola al iniciar
└── src/                 # el sitio web
    ├── destinos/        # Plantilla de destinos (se rellena según el destino)
    ├── paquetes/        # Plantilla de paquetes y experiencias
    ├── logica/          # Clases y lógica POO
    ├── login/           # Inicio de sesión y registro
    ├── img/             # Imágenes originales
    ├── index.php        # Página principal
    ├── style.css        # Estilos globales
    └── experiencias.css # Estilos de experiencias
```

## Instalación y uso

### Con Docker Compose (recomendado)

```bash
git clone https://github.com/aanaarcee/Nomada
cd Nomada
cp .env.example .env      # y cambiar las contraseñas
docker compose up -d --build
```

El sitio queda disponible en `http://localhost:8085`.

La primera vez, MySQL carga `db/01_nomada.sql` automáticamente (tarda unos segundos). Ese archivo solo se aplica cuando la base está vacía; para empezar de cero: `docker compose down -v && docker compose up -d`.

### Configuración

Variables del archivo `.env`:

| Variable | Descripción |
|---|---|
| `DB_NAME` | Nombre de la base (`nomada`) |
| `DB_USER` | Usuario de la base que usa la web |
| `DB_PASS` | Contraseña de ese usuario |
| `DB_ROOT_PASS` | Contraseña de root de MySQL |

El host de la base (`DB_HOST=db`) ya lo define el `docker-compose.yml`.

### Exportar la base (para guardar cambios hechos desde la web)

```bash
docker compose exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --hex-blob --default-character-set=utf8mb4 nomada' > db/01_nomada.sql
```

## Cómo funciona la plantilla dinámica

```
/destinos/destino.php?slug=paris
        │
        ▼
Se consulta el destino "paris" en la base de datos
        │
        ▼
Se carga en la misma plantilla (mismo diseño, distintos datos)
```

## Autor

Hecho por [aanaarcee](https://github.com/aanaarcee) como trabajo práctico.
