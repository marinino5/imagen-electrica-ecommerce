#!/bin/bash
# Comprueba que un servidor tenga la API actual del catálogo por líneas.
# Uso: bash docs/verificar-api.sh [url_base]   (por defecto, Render)
BASE="${1:-https://imagen-electrica-ecommerce.onrender.com}"
BASE="${BASE%/}"
echo "Servidor: $BASE (Render gratis puede tardar ~1 minuto en despertar)"
ok=1
check() {
  local path="$1" expect="$2" code body
  body=$(curl -s -m 90 -w '\n%{http_code}' "$BASE/api/v1/$path")
  code=$(echo "$body" | tail -1)
  body=$(echo "$body" | sed '$d')
  count=$(echo "$body" | python3 -c "import sys,json; d=json.load(sys.stdin); print(len(d.get('data',[])) if isinstance(d.get('data'),list) else 'ok')" 2>/dev/null || echo "?")
  printf "%-22s HTTP %s  datos: %s\n" "/api/v1/$path" "$code" "$count"
  [ "$code" = "$expect" ] || ok=0
}
check "families" 200
check "lines" 200
check "lines/w22-iec-carcasa-hierro" 200
check "products" 200
check "brands" 200
# /newsletter solo acepta POST: sin datos debe responder 422 (si no existe, 404/405). No guarda nada.
code=$(curl -s -m 90 -o /dev/null -w '%{http_code}' -X POST -H 'Accept: application/json' "$BASE/api/v1/newsletter")
printf "%-22s HTTP %s  (esperado 422)\n" "POST /newsletter" "$code"
[ "$code" = "422" ] || ok=0
# /lines debe traer imagen en las líneas (las migraciones de imágenes corrieron)
imgs=$(curl -s -m 90 "$BASE/api/v1/lines" | python3 -c "import sys,json; d=json.load(sys.stdin).get('data',[]); print('%d de %d' % (sum(1 for x in d if x.get('image_url')), len(d)), end='')" 2>/dev/null || echo "?")
printf "%-22s líneas con imagen: %s\n" "/api/v1/lines" "$imgs"
case "$imgs" in "0 de"*|"?") ok=0 ;; esac
curl -s -m 60 "$BASE/api/v1/families" | python3 -c "import sys,json; m=json.load(sys.stdin).get('meta',{}); print('Totales: %s líneas, %s referencias, %s familias' % (m.get('lines'), m.get('references'), m.get('families')))" 2>/dev/null
[ "$ok" = 1 ] && echo "TODO BIEN: la API tiene el catálogo por líneas." || { echo "FALTA ALGO: algún endpoint no respondió 200 (¿versión vieja del backend?)."; exit 1; }
