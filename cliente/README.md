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

### Con el backend local

Por defecto `npm run dev` usa la API de Render (`VITE_API_URL` de `.env`). Para trabajar contra tu Laravel local:

```bash
# Terminal 1: backend
cd backend
cp .env.example .env && php artisan key:generate   # solo la primera vez
php artisan migrate --seed                         # tablas, usuarios y catálogo por líneas
php artisan serve                                  # http://localhost:8000

# Terminal 2: tienda apuntando al backend local
cd cliente
npm run dev:local                                  # usa .env.api-local
```

`npm run dev:local` lee `cliente/.env.api-local` (`VITE_API_URL=http://localhost:8000/api/v1`) y no toca tu `.env`.
Si prefieres otro puerto, crea `cliente/.env.local` con `VITE_API_URL=...`: tiene prioridad sobre `.env` y no se sube a git.

El backend permite por defecto los orígenes `http://localhost:5173` y `http://127.0.0.1:5173`
(`CORS_ALLOWED_ORIGINS`). Si Vite usa otro puerto, agrégalo a esa variable en `backend/.env`.

Si la tienda muestra «El servidor no tiene este contenido» (HTTP 404) o «Sin conexión con el servidor»,
revisa que el backend al que apunta `VITE_API_URL` esté corriendo y tenga la versión actual:
`bash docs/verificar-api.sh http://localhost:8000` (o la URL de Render).

## Estructura

| Carpeta | Contenido |
|---|---|
| `src/api/` | Único lugar que llama a la API (token Bearer, errores sin conexión / 401 / 422). |
| `src/context/` | Sesión, carrito + cotización (localStorage) y avisos (toast). |
| `src/styles/variables.css` | Colores, tipografía, radios y sombras. Nada de colores sueltos en otros archivos. |
| `src/components/` | Layout global, secciones del inicio y piezas reutilizables. |
| `src/pages/` | Una página por ruta. |

Despliegue: `public/_redirects` envía todas las rutas a `index.html` en Cloudflare Pages.
