# Sistema de Control de Abastecimiento de Combustible en el Municipio de La Paz

Sistema web para controlar el abastecimiento de combustible en las estaciones de servicio del municipio de La Paz: existencias, ventas, solicitudes y envíos de cisternas, alertas, reportes ciudadanos, recomendaciones de abastecimiento (Teoría de Decisiones) y predicción de disponibilidad (Cadenas de Markov).

**Tecnologías:** Laravel 13, Jetstream + Livewire, Fortify, Spatie Permission, MySQL 8, Tailwind CSS, Vite, FontAwesome, SweetAlert2.

## Requisitos

| Herramienta | Versión recomendada |
|---|---|
| PHP | 8.3 o superior |
| Composer | 2.x |
| Node.js y npm | 20 o superior |
| MySQL | 8.0 |
| Git | cualquiera reciente |

En Windows se recomienda **Laragon** (trae PHP, MySQL, Composer y Git) y **MySQL Workbench** para la base de datos.

## Instalación

### 1. Clonar el repositorio

Si usas Laragon, clónalo dentro de `C:\laragon\www`:

```bash
cd C:\laragon\www
git clone https://github.com/Gonza-skin/combustibles.git
cd combustibles
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de JavaScript

```bash
npm install
```

### 4. Crear el archivo de entorno

```bash
copy .env.example .env
php artisan key:generate
```

(En Linux o Mac usa `cp .env.example .env`.)

### 5. Preparar la base de datos

La base de datos **no está en el repositorio**. El responsable del proyecto te entregará el archivo de respaldo (`.sql`) exportado desde MySQL Workbench. Para cargarlo:

1. Abre MySQL Workbench y conéctate a tu servidor local.
2. Crea la base de datos vacía:
   ```sql
   CREATE DATABASE combustible_lp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Importa el archivo: menú **Server → Data Import → Import from Self-Contained File**, elige el `.sql`, selecciona `combustible_lp` como *Default Target Schema* y pulsa **Start Import**.

### 6. Configurar el `.env`

Abre `.env` y ajusta los datos de tu MySQL local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=combustible_lp
DB_USERNAME=root
DB_PASSWORD=
```

Pon en `DB_PASSWORD` la contraseña de tu MySQL (en Laragon normalmente va vacía).

Si el sistema usa la API de Google Maps (mapa de estaciones), agrega también la clave que te entregue el responsable, en la variable que aparezca en `.env.example`. **Nunca subas tu `.env` al repositorio.**

### 7. Compilar los estilos y scripts

```bash
npm run build
```

Para desarrollar con recarga automática usa `npm run dev` en una terminal aparte.

### 8. Limpiar la caché y levantar el servidor

```bash
php artisan optimize:clear
php artisan serve
```

Abre <http://127.0.0.1:8000> en el navegador. Si usas Laragon también puedes entrar por `http://combustibles.test`.

## Usuarios de prueba

Todos con la contraseña `25242322`:

| Rol | Correo |
|---|---|
| ciudadano | `ciudadano@combustible.test` |
| encargado_estacion | `encargado_estacion@combustible.test` |
| sub_administrador | `sub_administrador@combustible.test` |
| conductor_cisterna | `conductor_cisterna@combustible.test` |
| administrador | `administrador@combustible.test` |
| administrador_deposito | `administrador_deposito@combustible.test` |
| supervisor | `supervisor@combustible.test` |

## Flujo de trabajo con Git

Antes de empezar a trabajar, trae los cambios de los demás:

```bash
git pull
```

Para trabajar en tu tarea sin pisar a los demás, usa una rama propia:

```bash
git checkout -b nombre-de-tu-tarea
```

Al terminar:

```bash
git add .
git commit -m "Descripción corta de lo que hiciste"
git push -u origin nombre-de-tu-tarea
```

Luego crea un **Pull Request** en GitHub para unirlo a `main`.

Cuando alguien actualice dependencias, después de `git pull` ejecuta:

```bash
composer install
npm install
npm run build
php artisan optimize:clear
```

## Problemas frecuentes

| Problema | Solución |
|---|---|
| `SQLSTATE[HY000] [1049] Unknown database` | La base `combustible_lp` no existe; créala e impórtala (paso 5). |
| `SQLSTATE[HY000] [1045] Access denied` | Usuario o contraseña de MySQL incorrectos en `.env`. |
| Pantalla sin estilos o error `Vite manifest not found` | Ejecuta `npm install` y `npm run build`. |
| `No application encryption key has been specified` | Ejecuta `php artisan key:generate`. |
| Cambios en `.env` o rutas que no se reflejan | Ejecuta `php artisan optimize:clear`. |
| Error 403 al entrar a una pantalla | El usuario no tiene el rol permitido para esa pantalla; prueba con otro usuario de la tabla de arriba. |

## Equipo

Proyecto académico — Análisis y Diseño II e Investigación Operativa II, Universidad Privada Franz Tamayo, Ingeniería de Sistemas.
