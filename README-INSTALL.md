# Guía de Instalación — Instituto Clans (Ubuntu / Red Local)

## Arquitectura

```
PC cliente (navegador)
    │
    │  http://clansinstituto.local        → Frontend (React build estático)
    │  http://api.clansinstituto.local    → Backend  (Laravel API)
    │
    ▼
Servidor Ubuntu
    │
    ├── Nginx nativo (host, puerto 80)
    │     ├── clansinstituto.local         → /var/www/html/clans-view
    │     └── api.clansinstituto.local     → reverse proxy a localhost:8080
    │
    ├── Docker
    │     ├── clans_web  (nginx:alpine :8080)  → fastcgi a clans_app:9000
    │     └── clans_app  (php:7.4-fpm + OPcache)
    │
    └── PostgreSQL (servicio nativo del Ubuntu)
```

---

## Requisitos previos

```bash
# Docker y Docker Compose
sudo apt update
sudo apt install -y docker.io docker-compose-plugin
sudo usermod -aG docker $USER
# Cerrar sesión y volver a entrar para que el grupo docker tome efecto

# Nginx
sudo apt install -y nginx

# Node.js (solo para compilar el frontend, después no se necesita)
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs

# PostgreSQL (si no está instalado)
sudo apt install -y postgresql postgresql-contrib
```

---

## Paso 1 — Configurar PostgreSQL

### 1.1 Crear base de datos y usuario

```bash
sudo -u postgres psql
```

```sql
CREATE DATABASE instituto_clans;
CREATE USER clans_user WITH ENCRYPTED PASSWORD 'tu_password_segura';
GRANT ALL PRIVILEGES ON DATABASE instituto_clans TO clans_user;
\q
```

### 1.2 Permitir conexiones desde Docker

Editar el archivo de autenticación:

```bash
sudo nano /etc/postgresql/*/main/pg_hba.conf
```

Agregar al final:

```
# Permitir conexiones desde contenedores Docker
host    instituto_clans    clans_user    172.17.0.0/16    md5
```

Editar la configuración para escuchar en la interfaz Docker:

```bash
sudo nano /etc/postgresql/*/main/postgresql.conf
```

Buscar y cambiar:

```
listen_addresses = 'localhost,172.17.0.1'
```

Reiniciar PostgreSQL:

```bash
sudo systemctl restart postgresql
```

---

## Paso 2 — Desplegar el Backend (Docker)

### 2.1 Copiar el proyecto al servidor

Copiar la carpeta `clans/` al servidor, por ejemplo en `/home/usuario/clans/`.

### 2.2 Configurar archivos

```bash
cd /home/usuario/clans/

# Copiar archivos de ejemplo
cp .env.clans.example .env
cp Dockerfile.clans.example Dockerfile
cp docker-compose.clans.example docker-compose.yml
```

### 2.3 Editar el .env

```bash
nano .env
```

Configurar los valores de la base de datos y la URL de la app:

```env
APP_URL=http://api.clansinstituto.local

DB_HOST=host.docker.internal
DB_PORT=5432
DB_DATABASE=instituto_clans
DB_USERNAME=clans_user
DB_PASSWORD=tu_password_segura
```

### 2.4 Levantar los contenedores

```bash
docker compose up -d --build
```

### 2.5 Ejecutar migraciones (primera vez)

```bash
docker exec -it clans_app php artisan migrate --seed
```

### 2.6 Verificar que funciona

```bash
curl http://localhost:8080
```

Debe responder con la página de Laravel.

---

## Paso 3 — Compilar y desplegar el Frontend

### 3.1 Compilar el build de React

En la carpeta `clans-view/`:

```bash
cd /ruta/a/clans-view/
```

Crear/editar el archivo `.env.production`:

```env
REACT_APP_BASE_URL=http://api.clansinstituto.local
PUBLIC_URL=
```

Compilar:

```bash
npm install
npm run build
```

### 3.2 Copiar el build al servidor

```bash
# Si compilaste en el servidor
sudo mkdir -p /var/www/html/clans-view
sudo cp -r build/* /var/www/html/clans-view/
sudo chown -R www-data:www-data /var/www/html/clans-view

# Si compilaste en otra PC, usar scp:
# scp -r build/* usuario@192.168.x.x:/tmp/clans-view/
# Luego en el servidor:
# sudo mkdir -p /var/www/html/clans-view
# sudo cp -r /tmp/clans-view/* /var/www/html/clans-view/
# sudo chown -R www-data:www-data /var/www/html/clans-view
```

---

## Paso 4 — Configurar Nginx del host

### 4.1 Frontend — `clansinstituto.local`

```bash
sudo nano /etc/nginx/sites-available/clansinstituto-frontend
```

Pegar:

```nginx
server {
    listen 80;
    server_name clansinstituto.local;

    root /var/www/html/clans-view;
    index index.html;

    # SPA: todas las rutas caen en index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache de assets estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }
}
```

### 4.2 API — `api.clansinstituto.local`

```bash
sudo nano /etc/nginx/sites-available/clansinstituto-api
```

Pegar:

```nginx
server {
    listen 80;
    server_name api.clansinstituto.local;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Timeouts generosos para operaciones largas
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

### 4.3 Activar los sitios

```bash
# Crear enlaces simbólicos
sudo ln -s /etc/nginx/sites-available/clansinstituto-frontend /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/clansinstituto-api /etc/nginx/sites-enabled/

# Desactivar el sitio por defecto (opcional)
sudo rm -f /etc/nginx/sites-enabled/default

# Verificar la configuración
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

---

## Paso 5 — Configurar DNS local (archivo hosts)

### 5.1 En el servidor Ubuntu

```bash
sudo nano /etc/hosts
```

Agregar:

```
127.0.0.1   clansinstituto.local
127.0.0.1   api.clansinstituto.local
```

### 5.2 En cada PC cliente (Linux/Ubuntu)

```bash
sudo nano /etc/hosts
```

Agregar (reemplazar `192.168.x.x` con la IP real del servidor):

```
192.168.x.x   clansinstituto.local
192.168.x.x   api.clansinstituto.local
```

### 5.3 En cada PC cliente (Windows)

Abrir como Administrador el archivo:

```
C:\Windows\System32\drivers\etc\hosts
```

Agregar:

```
192.168.x.x   clansinstituto.local
192.168.x.x   api.clansinstituto.local
```

---

## Paso 6 — Verificar todo

Desde el navegador de una PC cliente:

| URL | Resultado esperado |
|---|---|
| `http://clansinstituto.local` | Carga la interfaz del sistema (React) |
| `http://api.clansinstituto.local/api/...` | Responde la API de Laravel |

---

## Comandos útiles

### Docker

```bash
# Ver estado de los contenedores
docker compose ps

# Ver logs en tiempo real
docker compose logs -f

# Reiniciar contenedores
docker compose restart

# Reconstruir (si cambia el código)
docker compose up -d --build

# Entrar al contenedor PHP
docker exec -it clans_app bash

# Limpiar caché de Laravel
docker exec -it clans_app php artisan optimize:clear
docker exec -it clans_app php artisan optimize
```

### Actualizar el frontend

```bash
cd /ruta/a/clans-view
npm run build
sudo rm -rf /var/www/html/clans-view/*
sudo cp -r build/* /var/www/html/clans-view/
sudo chown -R www-data:www-data /var/www/html/clans-view
```

### Actualizar el backend

```bash
cd /home/usuario/clans
git pull   # o copiar archivos nuevos
docker compose up -d --build
docker exec -it clans_app php artisan migrate
```

---

## Solución de problemas

### El frontend carga pero la API no responde

```bash
# Verificar que Docker está corriendo
docker compose ps

# Verificar que el puerto 8080 está escuchando
ss -tlnp | grep 8080

# Probar el reverse proxy
curl -H "Host: api.clansinstituto.local" http://127.0.0.1
```

### Error de conexión a PostgreSQL desde Docker

```bash
# Entrar al contenedor
docker exec -it clans_app bash

# Dentro del contenedor, probar conexión:
apt-get update && apt-get install -y postgresql-client
psql -h host.docker.internal -U clans_user -d instituto_clans

# Si falla, revisar pg_hba.conf y que PostgreSQL escuche en 172.17.0.1
```

### Saber la IP de la red Docker

```bash
docker network inspect clans_clans | grep Subnet
# Normalmente es 172.17.0.0/16 o 172.18.0.0/16
```

### Las PCs cliente no pueden acceder

```bash
# Verificar que Nginx escucha en el puerto 80
ss -tlnp | grep :80

# Verificar firewall
sudo ufw status
sudo ufw allow 80/tcp    # si está activo
```

---

## Resumen de archivos

| Archivo | Ubicación en Ubuntu | Función |
|---|---|---|
| `.env` | `/home/usuario/clans/.env` | Config de Laravel (DB, APP_URL) |
| `Dockerfile` | `/home/usuario/clans/Dockerfile` | Imagen PHP 7.4-fpm + OPcache |
| `docker-compose.yml` | `/home/usuario/clans/docker-compose.yml` | Servicios: app + web |
| `default.conf` | Dentro del contenedor Nginx | Nginx interno que sirve Laravel |
| Frontend build | `/var/www/html/clans-view/` | Archivos estáticos de React |
| Frontend Nginx | `/etc/nginx/sites-available/clansinstituto-frontend` | Virtual host del frontend |
| API Nginx | `/etc/nginx/sites-available/clansinstituto-api` | Reverse proxy hacia Docker |
| `pg_hba.conf` | `/etc/postgresql/*/main/` | Acceso de Docker a PostgreSQL |
| `/etc/hosts` | Servidor + cada PC cliente | Resolución de dominios locales |
