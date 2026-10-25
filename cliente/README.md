# Cliente — tienda en React

Tienda B2B de Imagen Eléctrica hecha con React + Vite + React Router. Consume la API del backend (`/api/v1`) y se despliega por separado (Cloudflare Pages).

## Cómo correrla

```bash
cd cliente
cp .env.example .env     # VITE_API_URL apunta a la API de Render
npm install
npm run dev              # http://localhost:5173
npm run build            # genera dist/
```

Para usar el backend local, cambia `VITE_API_URL` en `.env` a `http://localhost:8000/api/v1`.
El backend debe permitir el origen de la tienda en `CORS_ALLOWED_ORIGINS`.

## Estructura

| Carpeta | Contenido |
|---|---|
| `src/api/` | Único lugar que llama a la API (token Bearer, errores sin conexión / 401 / 422). |
| `src/context/` | Sesión, carrito + cotización (localStorage) y avisos (toast). |
| `src/styles/variables.css` | Colores, tipografía, radios y sombras. Nada de colores sueltos en otros archivos. |
| `src/components/` | Layout global, secciones del inicio y piezas reutilizables. |
| `src/pages/` | Una página por ruta. |

Despliegue: `public/_redirects` envía todas las rutas a `index.html` en Cloudflare Pages.
