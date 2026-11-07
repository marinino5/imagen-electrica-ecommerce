import { useMemo } from 'react'

// Silueta aproximada de Colombia como [longitud, latitud]. Es un dibujo estilizado hecho solo de puntos.
const OUTLINE = [
  [-77.35, 8.67], [-76.9, 8.2], [-76.45, 8.85], [-75.8, 9.55], [-75.5, 10.45], [-74.85, 11.0], [-74.2, 11.3],
  [-73.0, 11.4], [-72.3, 12.2], [-71.65, 12.45], [-71.3, 11.85], [-72.0, 11.0], [-72.6, 10.0], [-72.95, 9.0],
  [-72.8, 8.2], [-72.3, 7.4], [-72.0, 7.0], [-70.2, 7.0], [-69.5, 6.2], [-67.5, 6.2], [-67.6, 4.8], [-67.3, 3.0],
  [-67.1, 1.8], [-67.8, 1.2], [-69.9, 1.0], [-69.4, -1.0], [-69.95, -4.2], [-70.45, -3.85], [-70.0, -2.3],
  [-72.0, -2.4], [-73.5, -1.8], [-74.0, -1.0], [-75.3, -0.1], [-76.4, 0.4], [-77.2, 0.35], [-77.8, 0.8],
  [-78.8, 1.4], [-78.5, 2.0], [-77.6, 2.9], [-77.2, 3.9], [-77.4, 4.8], [-77.35, 5.8], [-77.6, 6.6], [-77.9, 7.2],
  [-77.55, 7.9],
]

const LON0 = -79.1
const LAT0 = 12.6
const SCALE = 20 // unidades del SVG por grado
const STEP = 0.36 // separación entre puntos (grados)
const WIDTH = Math.ceil(12.4 * SCALE)
const HEIGHT = Math.ceil(17.1 * SCALE)

const project = (lon, lat) => [(lon - LON0) * SCALE, (LAT0 - lat) * SCALE]

// Punto dentro del polígono (trazado de rayo)
function inside(lon, lat) {
  let hit = false
  for (let i = 0, j = OUTLINE.length - 1; i < OUTLINE.length; j = i++) {
    const [xi, yi] = OUTLINE[i]
    const [xj, yj] = OUTLINE[j]
    if (yi > lat !== yj > lat && lon < ((xj - xi) * (lat - yi)) / (yj - yi) + xi) hit = !hit
  }
  return hit
}

const DOTS = (() => {
  const dots = []
  let row = 0
  for (let lat = LAT0; lat > -4.4; lat -= STEP * 0.866, row++) {
    for (let lon = LON0 + (row % 2 ? STEP / 2 : 0); lon < -66.8; lon += STEP) {
      if (inside(lon, lat)) dots.push(project(lon, lat))
    }
  }
  return dots
})()

// Bucaramanga: 7.12° N, 73.12° O
const [BGA_X, BGA_Y] = project(-73.12, 7.12)

// Ciudades principales a las que llegan los envíos: [nombre, longitud, latitud]
const CITIES = [
  ['Bogotá', -74.07, 4.71],
  ['Medellín', -75.57, 6.25],
  ['Cali', -76.53, 3.45],
  ['Barranquilla', -74.78, 10.96],
  ['Cartagena', -75.51, 10.39],
  ['Cúcuta', -72.5, 7.89],
]

// Curva suave de Bucaramanga a cada ciudad (control a medio camino, levantado)
const route = ([lon, lat]) => {
  const [x, y] = project(lon, lat)
  const mx = (BGA_X + x) / 2
  const my = (BGA_Y + y) / 2 - 18
  return `M${BGA_X.toFixed(1)} ${BGA_Y.toFixed(1)} Q${mx.toFixed(1)} ${my.toFixed(1)} ${x.toFixed(1)} ${y.toFixed(1)}`
}

// Mapa de puntos lavanda con Bucaramanga marcada en naranja y rutas animadas hacia las ciudades principales
export default function ColombiaMap({ className = '', routes = false }) {
  const dots = useMemo(() => DOTS, [])
  return (
    <svg className={`colombia-map ${className}`} viewBox={`0 0 ${WIDTH} ${HEIGHT}`} role="img" aria-label="Mapa de Colombia con Bucaramanga marcada" preserveAspectRatio="xMidYMid meet">
      {dots.map(([x, y], i) => (
        <circle key={i} cx={x.toFixed(1)} cy={y.toFixed(1)} r="1.35" fill="var(--lavender-2)" opacity="0.7" />
      ))}
      {routes && CITIES.map(([name, lon, lat], i) => {
        const [x, y] = project(lon, lat)
        return (
          <g key={name} className="colombia-map__route" style={{ '--i': i }}>
            <path d={route([lon, lat])} pathLength="1" />
            <circle cx={x.toFixed(1)} cy={y.toFixed(1)} r="3" />
            <text x={(x + 6).toFixed(1)} y={(y + 3).toFixed(1)}>{name}</text>
          </g>
        )
      })}
      <g transform={`translate(${BGA_X.toFixed(1)} ${BGA_Y.toFixed(1)})`}>
        <circle className="colombia-map__pulse" r="7" fill="var(--accent)" />
        <circle className="colombia-map__pulse colombia-map__pulse--b" r="7" fill="var(--accent)" />
        <circle r="4.2" fill="var(--accent)" stroke="var(--white)" strokeWidth="1.5" />
      </g>
    </svg>
  )
}
