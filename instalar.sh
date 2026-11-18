#!/bin/sh
# NO hace falta para levantar el sitio: db/01_nomada.sql ya trae todos los datos.
# Sirve para volver a cargar destinos, paquetes, experiencias e imágenes desde
# src/logica/instalar_datos_*.php y src/img/ (por ejemplo, después de cambiar fotos).
# Se puede correr las veces que quieras: no duplica nada.
set -e
cd "$(dirname "$0")"
for s in destinos paquetes experiencias; do
  echo ">>> instalar_datos_$s"
  docker compose exec -T web php logica/instalar_datos_$s.php
done
echo "Listo. Abrí http://localhost:8085"
