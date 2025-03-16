# Reservations App - Laravel API

¡Bienvenido al proyecto **Reservations App**! Esta es una API desarrollada en Laravel que utiliza Sail para la gestión de contenedores Docker. A continuación, encontrarás una guía detallada para configurar y ejecutar el proyecto en tu entorno local.

---

## Requisitos previos

Antes de comenzar, asegúrate de tener instalado lo siguiente:

- **Docker**: Para gestionar los contenedores.
- **Git** (opcional): Para clonar el repositorio.

---

## Configuración del proyecto

Sigue estos pasos para configurar y ejecutar el proyecto:

### 1. Clonar el repositorio

Clona el repositorio en tu máquina local:

```bash
git clone <URL_DEL_REPOSITORIO>
```
### 2. Ir a la raíz del proyecto

Accede a la carpeta del proyecto:

```bash
cd reservations-app
```
### 3. Instalar dependencias de Composer

Instala las dependencias del proyecto utilizando un contenedor temporal de Docker:

```bash
docker run --rm \
    -v $(pwd):/opt \
    -w /opt \
    laravelsail/php82-composer:latest \
    composer install
```
### 4. Configurar el archivo `.env`

Copia el archivo `.env.example` y renómbralo a `.env`. Ya está configurado para Sail,  sólo asegúrate de revisar y ajustar las variables de entorno según sea necesario:

```bash
cp .env.example .env
```
### 5. Levantar los contenedores

Levanta los contenedores utilizando Laravel Sail:

```bash
./vendor/bin/sail up -d
```
### 6. Generar la clave de Laravel

Genera la clave de aplicación de Laravel:

```bash
./vendor/bin/sail artisan key:generate
```

### 7. Generar la clave JWT

Genera la clave secreta para JWT (JSON Web Tokens):

```bash
./vendor/bin/sail artisan jwt:secret
```
### 8. Ejecutar migraciones

Ejecuta las migraciones para crear las tablas en la base de datos:

```bash
./vendor/bin/sail artisan migrate
```
### 9.- Acceso a la API

Una vez que los contenedores estén en funcionamiento, puedes acceder a la API y su documentación en la siguiente ruta:

```bash
http://localhost/api/documentation
```
