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
curl -s -m 60 "$BASE/api/v1/families" | python3 -c "import sys,json; m=json.load(sys.stdin).get('meta',{}); print('Totales: %s líneas, %s referencias, %s familias' % (m.get('lines'), m.get('references'), m.get('families')))" 2>/dev/null
[ "$ok" = 1 ] && echo "TODO BIEN: la API tiene el catálogo por líneas." || { echo "FALTA ALGO: algún endpoint no respondió 200 (¿versión vieja del backend?)."; exit 1; }
