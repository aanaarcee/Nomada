# Nomada - Proyecto Web

Sitio web de turismo desarrollado en **PHP** con **programación orientada a objetos (POO)**, basado en una consigna del torneo del INET. El foco del proyecto está en la lógica POO del backend más que en el diseño.

## Lo que lo hace especial

En lugar de tener un archivo por cada página (una para cada destino, una para cada paquete, etc.), el proyecto **reutiliza una única plantilla** con el diseño y **cambia solo los datos según el `id`** recibido, que se consultan a la base de datos. Agregar un destino o un paquete nuevo no requiere crear ningún archivo: alcanza con cargarlo en la base.

## Características

- Arquitectura orientada a objetos (clases separadas de la vista).
- Páginas dinámicas con una sola plantilla por sección (destinos, paquetes, experiencias).
- Contenido cargado desde base de datos.
- Sistema de login.
- Despliegue con Docker Compose.

## Tecnologías

- PHP
- MySQL / MariaDB *(completar según la que uses)*
- HTML y CSS
- Docker y Docker Compose

## Estructura del proyecto

```
eito/
├── destinos/        # Plantilla de destinos (se rellena por id)
├── paquetes/        # Plantilla de paquetes (se rellena por id)
├── logica/          # Clases y lógica POO
├── login/           # Inicio de sesión
├── img/             # Imágenes
├── index.php        # Página principal
├── style.css        # Estilos globales
└── experiencias.css # Estilos de experiencias
```

## Instalación y uso

### Con Docker Compose (recomendado)

```bash
git clone https://github.com/aanaarcee/eito
cd eito
docker compose up -d
```

El sitio queda disponible en `http://localhost:PUERTO` *(completar con el puerto de tu compose)*.

### Configuración

Copiá el archivo de variables de entorno y completalo con tus datos:

```bash
cp .env.example .env
```

| Variable | Descripción |
|---|---|
| `DB_HOST` | Host de la base de datos |
| `DB_NAME` | Nombre de la base |
| `DB_USER` | Usuario |
| `DB_PASS` | Contraseña |

*(Ajustar a las variables reales del proyecto.)*

## Cómo funciona la plantilla dinámica

```
/destinos/index.php?id=3
        │
        ▼
Se consulta el destino con id = 3 en la base de datos
        │
        ▼
Se carga en la misma plantilla (mismo diseño, distintos datos)
```

## Autor

Hecho por [aanaarcee](https://github.com/aanaarcee) como trabajo práctico.

## Licencia

*(Elegir una: MIT, etc.)*
