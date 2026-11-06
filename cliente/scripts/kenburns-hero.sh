#!/bin/bash
# Convierte una imagen fija en un clip del hero con movimiento lento tipo Ken Burns (8 s, loop sin salto).
# Pensado para la diapositiva D1 mientras no haya un clip real o material oficial de WEG.
# Uso: scripts/kenburns-hero.sh <imagen.jpg|png> <nombre> [x_centro_0a1]
#   La imagen debe tener al menos 2400 px de ancho (con menos, el zoom se ve borroso).
set -euo pipefail
IN="$1"; NAME="$2"; CX="${3:-0.5}"
OUT="$(dirname "$0")/../public/media"; mkdir -p "$OUT"
DUR=8; FPS=25; FRAMES=$((DUR * FPS))
# Zoom que sube y baja con un seno: el último fotograma coincide con el primero (loop continuo)
ZOOM="1+0.07*sin(PI*on/${FRAMES})"
PAN_X="(iw-iw/zoom)*${CX}"
PAN_Y="(ih-ih/zoom)*0.5"

# Se trabaja sobre una copia más grande para que zoompan no tiemble
BASE="scale=3840:-2:flags=lanczos,crop=3840:2160"
D="${BASE},zoompan=z='${ZOOM}':x='${PAN_X}':y='${PAN_Y}':d=${FRAMES}:s=1920x1080:fps=${FPS},eq=contrast=1.04:saturation=1.05"
M="${BASE},zoompan=z='${ZOOM}':x='(iw-iw/zoom)*${CX}':y='${PAN_Y}':d=${FRAMES}:s=1920x1080:fps=${FPS},crop=ih*4/5:ih:(iw-ih*4/5)*${CX}:0,scale=720:900:flags=lanczos,eq=contrast=1.04:saturation=1.05"

ffmpeg -y -loglevel error -loop 1 -i "$IN" -t "$DUR" -an -vf "$D" -c:v libx264 -preset slow -crf 21 -maxrate 4500k -bufsize 9000k -pix_fmt yuv420p -movflags +faststart "$OUT/$NAME.mp4"
ffmpeg -y -loglevel error -loop 1 -i "$IN" -t "$DUR" -an -vf "$D" -c:v libvpx-vp9 -crf 27 -b:v 4000k -row-mt 1 -cpu-used 1 "$OUT/$NAME.webm"
ffmpeg -y -loglevel error -loop 1 -i "$IN" -t "$DUR" -an -vf "$M" -c:v libx264 -preset slow -crf 23 -maxrate 2200k -bufsize 4400k -pix_fmt yuv420p -movflags +faststart "$OUT/$NAME-m.mp4"
ffmpeg -y -loglevel error -loop 1 -i "$IN" -t "$DUR" -an -vf "$M" -c:v libvpx-vp9 -crf 31 -b:v 2000k -row-mt 1 -cpu-used 1 "$OUT/$NAME-m.webm"

TMP="$(mktemp -t hero-poster).png"
ffmpeg -y -loglevel error -i "$IN" -frames:v 1 -vf "scale=1920:-2:flags=lanczos,crop=1920:1080,eq=contrast=1.04:saturation=1.05" "$TMP"
cwebp -quiet -q 80 "$TMP" -o "$OUT/$NAME-poster.webp"
rm -f "$TMP"
ls -l "$OUT/$NAME"* | awk '{printf "%8.2f MB  %s\n", $5/1048576, $9}'
