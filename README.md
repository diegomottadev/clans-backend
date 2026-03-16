# Instituto Clans - Backend API

REST API para el sistema de gestión del Instituto de Idiomas Clans, construido con **Laravel 8** y **PostgreSQL**.

> **Frontend:** [clans-frontend](https://github.com/diegomottadev/clans-frontend)

---

## Descripción

Sistema integral de gestión para un instituto de idiomas que permite administrar alumnos, cursos, docentes, evaluaciones, asistencias y facturación. Expone una API RESTful consumida por el frontend en React.

## Tecnologías

| Componente | Tecnología |
|---|---|
| Framework | Laravel 8 (PHP 7.4+) |
| Base de datos | PostgreSQL |
| Autenticación | JWT (`tymon/jwt-auth`) |
| Roles y permisos | Entrust |
| Serialización API | Spatie Fractal |
| Generación de PDFs | DomPDF |
| Contenedores | Docker + Nginx |

## Módulos principales

- **Gestión Académica:** Ciclos lectivos, idiomas, niveles, cursos, aulas y horarios
- **Alumnos:** CRUD, inscripción a cursos, historial académico
- **Docentes:** CRUD, asignación a cursos, gestión de pagos
- **Evaluaciones:** Creación de exámenes, carga de notas por criterio (listening, vocabulary, reading, writing, oral, etc.)
- **Asistencias:** Registro de asistencia por fecha de clase con tipos configurables
- **Facturación:** Generación de facturas por alumno/curso/mes, cálculo de mora automática, gestión de deudores
- **Gastos fijos:** Registro de costos operativos
- **Reportes y PDFs:** Reportes analíticos por docente, alumno, curso, idioma/nivel, facturación y pagos, con descarga en PDF
- **Calendario:** Visualización de clases programadas

## Requisitos previos

- PHP >= 7.4
- Composer
- PostgreSQL 12+
- Node.js y npm (para assets)
- Docker y Docker Compose (opcional)

## Instalación

### Con Docker (recomendado)

```bash
# Clonar el repositorio
git clone https://github.com/diegomottadev/clans-backend.git
cd clans-backend

# Copiar archivo de entorno
cp .env.example .env

# Configurar las variables de entorno en .env
# DB_CONNECTION=pgsql
# DB_HOST=host.docker.internal
# DB_PORT=5432
# DB_DATABASE=clans
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_password
# JWT_SECRET=tu_jwt_secret

# Levantar los contenedores
docker-compose up -d

# Instalar dependencias dentro del contenedor
docker exec clans_app composer install

# Generar key de la aplicación
docker exec clans_app php artisan key:generate

# Generar secret JWT
docker exec clans_app php artisan jwt:secret

# Ejecutar migraciones y seeders
docker exec clans_app php artisan migrate --seed
```

La API estará disponible en `http://localhost:8080`.

### Sin Docker

```bash
# Clonar el repositorio
git clone https://github.com/diegomottadev/clans-backend.git
cd clans-backend

# Instalar dependencias
composer install

# Copiar y configurar entorno
cp .env.example .env
# Editar .env con los datos de tu PostgreSQL

# Generar keys
php artisan key:generate
php artisan jwt:secret

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Iniciar servidor de desarrollo
php artisan serve
```

La API estará disponible en `http://localhost:8000`.

## Autenticación

La API utiliza **JWT (JSON Web Tokens)**. Todos los endpoints (excepto login) requieren el header:

```
Authorization: Bearer <token>
```

### Roles

| Rol | Acceso |
|---|---|
| `admin` | Acceso completo a todos los módulos |
| `user` | Acceso a gestión académica, alumnos, evaluaciones y asistencias |

## Endpoints principales

### Auth
| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/auth/login` | Iniciar sesión |
| POST | `/api/auth/logout` | Cerrar sesión |
| GET | `/api/auth/me` | Usuario autenticado |
| POST | `/api/auth/refresh` | Refrescar token |

### Ciclos Lectivos
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/schoolYears` | Listar ciclos lectivos |
| GET | `/api/schoolYears/current` | Ciclo lectivo actual |
| POST | `/api/schoolYears` | Crear ciclo lectivo |
| PUT | `/api/schoolYears/{id}` | Actualizar ciclo lectivo |

### Alumnos
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/students` | Listar alumnos |
| POST | `/api/students` | Crear alumno |
| PUT | `/api/students/{id}` | Actualizar alumno |
| DELETE | `/api/students/{id}` | Eliminar alumno |
| POST | `/api/students/{id}/courses` | Inscribir a curso |

### Cursos
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/courses` | Listar cursos |
| POST | `/api/courses` | Crear curso |
| PUT | `/api/courses/{id}` | Actualizar curso |
| DELETE | `/api/courses/{id}` | Eliminar curso |

### Evaluaciones
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/evaluations` | Listar evaluaciones |
| POST | `/api/evaluations` | Crear evaluación |
| POST | `/api/evaluations/{id}/students` | Cargar nota de alumno |

### Asistencias
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/assistances` | Listar asistencias |
| POST | `/api/assistances` | Crear registro de asistencia |
| POST | `/api/assistances/{id}/students` | Registrar asistencia de alumno |

### Facturación
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/invoices` | Listar facturas |
| POST | `/api/invoices` | Crear factura |
| PUT | `/api/invoices/{id}` | Actualizar factura |
| GET | `/api/debtors` | Listar deudores |

### Reportes
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/reports/teachers` | Reporte de docentes |
| GET | `/api/reports/assistencesByCourse` | Asistencias por curso |
| GET | `/api/reports/reportCompleteByStudent` | Reporte completo por alumno |
| GET | `/api/reports/reportAnalytical` | Reporte analítico |
| GET | `/api/reports/downloadReportByStudent` | Descargar PDF por alumno |

> Consultar `routes/api.php` para la lista completa de endpoints.

## Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/     # Controladores por dominio (25+)
│   ├── Middleware/       # isAdmin, isUser
│   └── Requests/        # Form requests de validación
├── Models/              # Modelos Eloquent (27)
├── Traits/              # ApiResponse trait
└── Transformers/        # Fractal transformers para API
database/
├── migrations/          # 70+ migraciones
└── seeders/             # Datos iniciales de desarrollo
docker/
└── nginx/               # Configuración Nginx
routes/
└── api.php              # Definición de rutas API (~127 rutas)
```

## Seeders disponibles

```bash
php artisan db:seed
```

Incluye datos de ejemplo para: usuarios, idiomas, niveles, tipos de evaluación, tipos de asistencia, tipos de cursado, tipos de egreso y configuración inicial.

## Variables de entorno clave

| Variable | Descripción |
|---|---|
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Host de PostgreSQL |
| `DB_DATABASE` | Nombre de la base de datos |
| `JWT_SECRET` | Secret para firmar tokens JWT |
| `JWT_TTL` | Tiempo de vida del token en minutos (default: 480) |

## Licencia

Este proyecto está bajo la licencia [MIT](https://opensource.org/licenses/MIT).
