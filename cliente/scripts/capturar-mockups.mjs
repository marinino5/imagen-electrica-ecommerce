// Captura las pantallas reales de la tienda para los celulares de «Cómo comprar» (public/img/mockups/*.png).
// Uso (con la tienda y la API corriendo; necesita playwright-core y el Chrome del sistema, no son dependencias del proyecto):
//   BASE=http://localhost:5195 node capturar-mockups.mjs <carpeta-de-salida>
// Después se convierten a WebP con cwebp (ver docs/despliegue.md o el flujo de cliente/scripts).
import { chromium } from 'playwright-core'

const BASE = process.env.BASE ?? 'http://localhost:5195'
const out = process.argv[2] ?? '.'
const chrome = process.env.CHROME ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
const clip = { x: 0, y: 0, width: 390, height: 560 }

const b = await chromium.launch({ executablePath: chrome, headless: true })
const ctx = await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true })
const p = await ctx.newPage()
await p.goto(`${BASE}/productos/arrancadores-directos?ref=10046366`, { waitUntil: 'networkidle' })
await p.waitForTimeout(1200)
await p.screenshot({ path: `${out}/ficha.png`, clip })
// Agrega al carrito para mostrar el carrito y el formulario de cotización con un producto
await p.getByRole('button', { name: 'Agregar al carrito' }).first().click()
await p.waitForTimeout(600)
await p.goto(`${BASE}/carrito`, { waitUntil: 'networkidle' })
await p.waitForTimeout(1200)
await p.screenshot({ path: `${out}/carrito.png`, clip })
// Una referencia más, pero como cotización, para mostrar el formulario con su lista
await p.goto(`${BASE}/productos/contactores`, { waitUntil: 'networkidle' })
await p.getByRole('button', { name: 'Solicitar cotización' }).first().click()
await p.waitForTimeout(600)
await p.goto(`${BASE}/carrito#cotizacion`, { waitUntil: 'networkidle' })
await p.waitForTimeout(1200)
await p.evaluate(() => document.getElementById('cotizacion')?.scrollIntoView())
await p.waitForTimeout(600)
await p.screenshot({ path: `${out}/cotizacion.png`, clip })
await b.close()
