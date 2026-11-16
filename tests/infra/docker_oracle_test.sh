#!/bin/sh
# Tests oráculo de infraestructura: se ejecutan contra los contenedores levantados con
#   docker compose -p clans up -d
# Uso (desde clans/):  sh tests/infra/docker_oracle_test.sh
# Sale con código 1 si alguno falla.

API=${API:-http://127.0.0.1:8080}
ROOT=$(cd "$(dirname "$0")/../.." && pwd)
VIEW="$ROOT/../clans-view"
fails=0
pass() { echo "PASS  $1"; }
fail() { echo "FAIL  $1  -> $2"; fails=$((fails+1)); }

# 1. No existe ningún script público que vacíe la caché de PHP
code=$(curl -s -o /dev/null -w "%{http_code}" "$API/opcache_reset.php")
[ "$code" = "404" ] && pass "opcache_reset.php no es accesible" || fail "opcache_reset.php no es accesible" "HTTP $code"

# 2. PostgreSQL solo publicado en loopback (no en 0.0.0.0 / ::)
binds=$(docker port clans_db 5432 2>/dev/null)
if [ -n "$binds" ] && ! echo "$binds" | grep -qvE '^127\.0\.0\.1:'; then
  pass "BD publicada solo en 127.0.0.1 ($binds)"
else
  fail "BD publicada solo en 127.0.0.1" "$(echo $binds)"
fi

# 3. php-fpm con valores de producción
fpm=$(docker exec clans_app php-fpm -i 2>/dev/null)
for kv in "expose_php => Off" "display_errors => Off" "register_argc_argv => Off"; do
  echo "$fpm" | grep -q "^$kv => " && pass "php-fpm $kv" || fail "php-fpm $kv" "$(echo "$fpm" | grep "^${kv%% =>*} =>")"
done

# 4. Imágenes con versión fija (nada de :latest, :alpine, :17 ni etiquetas sin X.Y)
bad=$(grep -hE '^\s*(FROM|COPY --from=)|^\s*image:' "$ROOT/Dockerfile" "$ROOT/docker-compose.yml" "$VIEW/Dockerfile" \
  | grep -oE '(FROM|from=|image:)\s*[^ ]+' | sed -E 's/^(FROM|from=|image:)\s*//' \
  | grep -vE '^clans-(app|view)$' | grep ':' | grep -vE ':[^:]*[0-9]+\.[0-9]+')
[ -z "$bad" ] && pass "imágenes con versión fija" || fail "imágenes con versión fija" "$(echo $bad)"

# 5. El build del frontend no publica source maps
maps=$(docker exec clans_frontend sh -c 'find /usr/share/nginx/html -name "*.map" | wc -l' 2>/dev/null)
[ "$maps" = "0" ] && pass "frontend sin archivos .map" || fail "frontend sin archivos .map" "$maps archivos"

# 6. (H13) nginx del backend: cabeceras de seguridad, sin versión ni X-Powered-By
hdr=$(curl -s -D - -o /dev/null "$API/api/auth/me" | tr -d '\r')
for h in "X-Content-Type-Options: nosniff" "X-Frame-Options: DENY" "Referrer-Policy:" "Content-Security-Policy: frame-ancestors 'none'"; do
  echo "$hdr" | grep -qi "^$h" && pass "backend cabecera $h" || fail "backend cabecera $h" "ausente"
done
echo "$hdr" | grep -qiE '^Server: nginx/[0-9]' && fail "backend sin versión de nginx" "$(echo "$hdr" | grep -i '^Server:')" || pass "backend sin versión de nginx"
echo "$hdr" | grep -qi '^X-Powered-By:' && fail "backend sin X-Powered-By" "$(echo "$hdr" | grep -i '^X-Powered-By:')" || pass "backend sin X-Powered-By"

# 7. (H13) archivos ocultos y de respaldo no se sirven
for path in /.env /.git/config /backup.sql /db.dump /composer.lock /README.md; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "$API$path")
  case "$code" in 403|404) pass "backend bloquea $path ($code)";; *) fail "backend bloquea $path" "HTTP $code";; esac
done

# 8. (H15) nginx del frontend: cabeceras y bloqueo de .map/dotfiles
WEB=${WEB:-http://127.0.0.1:3000}
fhdr=$(curl -s -D - -o /dev/null "$WEB/" | tr -d '\r')
for h in "X-Content-Type-Options: nosniff" "X-Frame-Options:" "Content-Security-Policy:"; do
  echo "$fhdr" | grep -qi "^$h" && pass "frontend cabecera $h" || fail "frontend cabecera $h" "ausente"
done
for path in /static/js/main.js.map /.env /.git/config; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "$WEB$path")
  case "$code" in 403|404) pass "frontend bloquea $path ($code)";; *) fail "frontend bloquea $path" "HTTP $code";; esac
done

# 9. El frontend sirve la API en su mismo origen (:3000/api -> backend), sin CORS
me_json=$(mktemp); mejarg=$me_json; command -v cygpath >/dev/null 2>&1 && mejarg=$(cygpath -m "$me_json")
code=$(curl -s -o "$mejarg" -w "%{http_code}" "$WEB/api/auth/me" -H "Accept: application/json")
if [ "$code" = "401" ] && grep -q '"code":401' "$me_json"; then pass "frontend /api/auth/me llega al backend (401 JSON)"; else fail "frontend /api/auth/me llega al backend" "HTTP $code $(head -c 80 "$me_json")"; fi
js=$(docker exec clans_frontend sh -c 'cat /usr/share/nginx/html/static/js/main.*.js' 2>/dev/null)
echo "$js" | grep -q 'localhost:8080' && fail "el bundle no apunta a otro origen" "contiene localhost:8080" || pass "el bundle no apunta a otro origen"

# 10. Subidas de hasta 2 MB (foto de perfil) no las corta nginx con 413, en :8080 ni en :3000.
#     Se manda contenido NO imagen de 1.5 MB: Laravel lo rechaza con 422 sin tocar datos.
TOKEN=$(curl -s -X POST "$API/api/auth/login" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"admin@admin.com","password":"admin"}' | sed -n 's/.*"access_token":"\([^"]*\)".*/\1/p')
big=$(mktemp)
# curl puede ser nativo de Windows: pasarle la ruta en formato del sistema
bigarg=$big; command -v cygpath >/dev/null 2>&1 && bigarg=$(cygpath -m "$big")
head -c 1572864 /dev/zero | tr '\0' 'a' > $big
for base in "$API" "$WEB"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$base/api/me/avatar" -H "Accept: application/json" \
    -H "Authorization: Bearer $TOKEN" -F "avatar=@$bigarg;type=image/jpeg")
  [ "$code" = "422" ] && pass "subida de 1.5 MB llega a Laravel por $base (422)" || fail "subida de 1.5 MB llega a Laravel por $base" "HTTP $code"
done
rm -f $big

# 11. GD con soporte WebP (fotos de perfil en .webp se convierten a JPEG)
docker exec clans_app php -r 'exit(!empty(gd_info()["WebP Support"]) ? 0 : 1);' && pass "GD soporta WebP" || fail "GD soporta WebP" "sin soporte"

echo "----"
[ $fails -eq 0 ] && echo "OK: todos los tests de infraestructura pasan" || echo "FALLAN: $fails"
exit $([ $fails -eq 0 ] && echo 0 || echo 1)
