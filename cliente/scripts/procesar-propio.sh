#!/bin/sh
# Videos propios de la tienda (celular, vertical con metadatos de rotación): corrige la rotación, recorta el encuadre para sacar
# letreros, estabiliza (deshake), corrige color, quita el audio y exporta MP4 (H.264) y WebM (VP9) en 1080p (escritorio) y 720p (celular),
# más el póster JPG. Fondo: el mismo clip desenfocado; el clip nítido va a la derecha en escritorio y centrado en celular.
# Uso: procesar-propio.sh <origen.MOV> <nombre> <inicio_s> <duracion_s> <crop w:h:x:y como fracciones, ej. 0.88:0.65:0.12:0.30> [cámara_lenta]
# cámara_lenta (ej. 2.4): si solo un tramo corto está limpio (sin letreros ni precios a mano), se alarga con fundido entre cuadros.
set -e
SRC="$1"; NAME="$2"; SS="$3"; T="$4"; SLOW="${6:-1}"; IFS=: read CW CH CX CY <<EOF2
$5
EOF2
OUT="$(dirname "$0")/../public/media"
CROP="crop=trunc(iw*$CW/2)*2:trunc(ih*$CH/2)*2:trunc(iw*$CX/2)*2:trunc(ih*$CY/2)*2,deshake,eq=contrast=1.06:saturation=0.95:gamma=1.04,hqdn3d=2:2:3:3"
if [ "$SLOW" != "1" ]; then CROP="$CROP,setpts=$SLOW*PTS,minterpolate=fps=30:mi_mode=blend"; fi
for MODE in d m; do
  if [ "$MODE" = d ]; then W=1920; H=1080; FGX="W*0.58"; SUF=""; else W=720; H=1280; FGX="(W-w)/2"; SUF="-m"; fi
  GRAPH="[0:v]$CROP,split=2[a][b];[a]scale=$W:$H:force_original_aspect_ratio=increase,crop=$W:$H,boxblur=28:6,eq=brightness=-0.06[bg];[b]scale=-2:$H*0.94[fg];[bg][fg]overlay=x=$FGX:y=($H-h)/2,format=yuv420p[v]"
  case "$MODE" in d) GRAPH=$(echo "$GRAPH" | sed "s/W\*0.58/$W*0.58/;s/\$H\*0.94/$H*0.94/;s/(\$H-h)/($H-h)/");; m) GRAPH=$(echo "$GRAPH" | sed "s/\$H\*0.94/$H*0.84/;s/(W-w)/($W-w)/;s/(\$H-h)/($H-h)/");; esac
  ffmpeg -v error -y -ss "$SS" -t "$T" -i "$SRC" -an -filter_complex "$GRAPH" -map "[v]" -c:v libx264 -preset veryfast -crf 29 -movflags +faststart -r 30 "$OUT/$NAME$SUF.mp4"
  ffmpeg -v error -y -i "$OUT/$NAME$SUF.mp4" -an -c:v libvpx-vp9 -b:v 0 -crf 40 -row-mt 1 -cpu-used 4 "$OUT/$NAME$SUF.webm"
done
ffmpeg -v error -y -ss 1.2 -i "$OUT/$NAME.mp4" -frames:v 1 -q:v 4 "$OUT/$NAME-poster.jpg"
ls -la "$OUT/$NAME"*.mp4 "$OUT/$NAME"*.webm "$OUT/$NAME-poster.jpg" | awk '{print $5, $9}'
