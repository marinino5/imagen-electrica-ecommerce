#!/bin/bash
# Prepara un clip del hero con la misma dirección audiovisual para las 3 diapositivas.
# Uso: scripts/procesar-hero.sh <entrada.mp4> <nombre> [inicio_seg] [x_centro_0a1]
#   nombre: hero-d1, hero-d2 o hero-d3
#   inicio_seg: segundo desde el que se corta (por defecto 0)
#   x_centro_0a1: dónde está el sujeto en horizontal para el recorte vertical (0 = izquierda, 1 = derecha; por defecto 0.5)
# Salidas en public/media/: <nombre>.mp4/.webm (16:9, 1920 px) y <nombre>-m.mp4/.webm (4:5, 720 px), más <nombre>-poster.webp
set -euo pipefail
IN="$1"; NAME="$2"; SS="${3:-0}"; CX="${4:-0.5}"
OUT="$(dirname "$0")/../public/media"; mkdir -p "$OUT"
DUR=9
# Gradación común: contraste y saturación suaves, sin virar el color; fundido de entrada y salida para el loop
GRADE="eq=contrast=1.04:saturation=1.05:brightness=0.0,fade=t=in:st=0:d=0.5,fade=t=out:st=$(echo "$DUR - 0.5" | bc):d=0.5"
COMMON=(-ss "$SS" -t "$DUR" -i "$IN" -an -r 25 -movflags +faststart)

# Escritorio 16:9 a 1920 px (3 MB o menos)
V_D="scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,$GRADE"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_D" -c:v libx264 -preset slow -crf 27 -maxrate 2200k -bufsize 4400k -pix_fmt yuv420p "$OUT/$NAME.mp4"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_D" -c:v libvpx-vp9 -crf 36 -b:v 2000k -row-mt 1 "$OUT/$NAME.webm"

# Celular 4:5 a 720 px de ancho (1,5 MB o menos), recortando alrededor del sujeto
V_M="scale=-2:900:force_original_aspect_ratio=increase,crop=720:900:'(in_w-720)*$CX':0,$GRADE"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_M" -c:v libx264 -preset slow -crf 29 -maxrate 1000k -bufsize 2000k -pix_fmt yuv420p "$OUT/$NAME-m.mp4"
ffmpeg -y -loglevel error "${COMMON[@]}" -vf "$V_M" -c:v libvpx-vp9 -crf 38 -b:v 1000k -row-mt 1 "$OUT/$NAME-m.webm"

# Poster WebP (fotograma a 1 s, sin fundido). ffmpeg local no trae libwebp: se pasa por PNG y cwebp
TMP="$(mktemp -t hero-poster).png"
ffmpeg -y -loglevel error -ss "$(echo "$SS + 1" | bc)" -i "$IN" -frames:v 1 -vf "scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,eq=contrast=1.04:saturation=1.05" "$TMP"
cwebp -quiet -q 80 "$TMP" -o "$OUT/$NAME-poster.webp"
rm -f "$TMP"

ls -l "$OUT/$NAME"* | awk '{printf "%8.2f MB  %s\n", $5/1048576, $9}'
