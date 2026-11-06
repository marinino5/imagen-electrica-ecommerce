#!/bin/bash
# Prepara un clip del hero con la misma dirección audiovisual para las 3 diapositivas.
# Uso: scripts/procesar-hero.sh <entrada.mp4> <nombre> [inicio_seg] [x_centro_0a1]
#   nombre: hero-d1, hero-d2 o hero-d3
#   inicio_seg: segundo desde el que se corta (por defecto 0)
#   x_centro_0a1: dónde está el sujeto en horizontal para el recorte vertical (0 = izquierda, 1 = derecha; por defecto 0.5)
# Salidas en public/media/: <nombre>.mp4/.webm (16:9, 1920 px, CRF 21) y <nombre>-m.mp4/.webm (4:5, 720 px, CRF 23), más <nombre>-poster.webp
set -euo pipefail
IN="$1"; NAME="$2"; SS="${3:-0}"; CX="${4:-0.5}"
OUT="$(dirname "$0")/../public/media"; mkdir -p "$OUT"
DUR=9
# Gradación común: contraste y saturación suaves, sin virar el color; fundido de entrada y salida para el loop
GRADE="eq=contrast=1.04:saturation=1.05:brightness=0.0,fade=t=in:st=0:d=0.5,fade=t=out:st=$(echo "$DUR - 0.5" | bc):d=0.5"
COMMON=(-ss "$SS" -t "$DUR" -i "$IN" -an -r 25 -movflags +faststart)

# Escritorio 16:9 a 1920 px, desde la fuente de mayor resolución (si la fuente es más chica no se agranda).
# CRF 29 con preset slow y tope de 2,2 Mbps: menos de 3 MB para 9 s.
V_D="scale='min(1920,iw)':-2:flags=lanczos,crop='min(iw,ih*16/9)':'min(ih,iw*9/16)',$GRADE"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_D" -c:v libx264 -preset slow -crf 29 -maxrate 2200k -bufsize 4400k -pix_fmt yuv420p "$OUT/$NAME.mp4"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_D" -c:v libvpx-vp9 -crf 38 -b:v 1800k -row-mt 1 -cpu-used 1 "$OUT/$NAME.webm"

# Celular 4:5 a 720 px de ancho: CRF 23, recortando alrededor del sujeto
V_M="scale=-2:900:flags=lanczos:force_original_aspect_ratio=increase,crop=720:900:'(in_w-720)*$CX':0,$GRADE"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_M" -c:v libx264 -preset slow -crf 23 -maxrate 2200k -bufsize 4400k -pix_fmt yuv420p "$OUT/$NAME-m.mp4"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_M" -c:v libvpx-vp9 -crf 38 -b:v 1200k -row-mt 1 -cpu-used 1 "$OUT/$NAME-m.webm"

# Poster WebP (fotograma a 1 s, sin fundido). ffmpeg local no trae libwebp: se pasa por PNG y cwebp
TMP="$(mktemp -t hero-poster).png"
ffmpeg -y -loglevel error -ss "$(echo "$SS + 1" | bc)" -i "$IN" -frames:v 1 -vf "scale='min(1920,iw)':-2:flags=lanczos,crop='min(iw,ih*16/9)':'min(ih,iw*9/16)',eq=contrast=1.04:saturation=1.05" "$TMP"
cwebp -quiet -q 80 "$TMP" -o "$OUT/$NAME-poster.webp"
rm -f "$TMP"

ls -l "$OUT/$NAME"* | awk '{printf "%8.2f MB  %s\n", $5/1048576, $9}'
