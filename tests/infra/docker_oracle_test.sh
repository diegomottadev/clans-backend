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

echo "----"
[ $fails -eq 0 ] && echo "OK: todos los tests de infraestructura pasan" || echo "FALLAN: $fails"
exit $([ $fails -eq 0 ] && echo 0 || echo 1)
