// Captura las pantallas reales de la tienda para «Cómo comprar» → public/img/como-comprar/*.png (luego se pasan a WebP con cwebp).
// Uso (tienda corriendo contra la API que tenga fotos de producto; necesita playwright-core y el Chrome del sistema, que no son
// dependencias del proyecto):
//   BASE=http://localhost:5191 node capturar-mockups.mjs <carpeta-de-salida>
// Los envíos (POST /orders y /quote-requests/free) se SIMULAN con route(): las pantallas de «confirmación» son las reales de la
// tienda, pero no se crea ningún pedido ni cotización en la base de datos.
import { chromium } from 'playwright-core'

const BASE = process.env.BASE ?? 'http://localhost:5191'
const out = process.argv[2] ?? '.'
const chrome = process.env.CHROME ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
const SLUG = process.env.SLUG ?? 'w22-iec-carcasa-hierro'

// La API limita las peticiones por minuto por IP: ESPERA=65 (segundos) antes de empezar si se acaba de correr otra captura
if (process.env.ESPERA) await new Promise((r) => setTimeout(r, Number(process.env.ESPERA) * 1000))

// La API de producción solo acepta el origen de la tienda publicada y de localhost:5173 (CORS). Para capturar desde otro puerto,
// las peticiones GET a la API se piden desde Node y se devuelven con CORS abierto; además se guardan en memoria para no
// gastar el límite de peticiones por minuto de la API.
const cache = new Map()
const proxy = async (route) => {
  const req = route.request()
  if (req.method() !== 'GET') return route.fallback()
  let hit = cache.get(req.url())
  if (!hit) {
    const res = await route.fetch({ headers: { ...req.headers(), origin: 'http://localhost:5173' } })
    hit = { status: res.status(), body: await res.body(), headers: { ...res.headers(), 'access-control-allow-origin': '*' } }
    if (res.ok()) cache.set(req.url(), hit)
  }
  return route.fulfill(hit)
}

const b = await chromium.launch({ executablePath: chrome, headless: true })

// ---------- Celular (390 × 844 @2x) ----------
const phone = await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true })
const p = await phone.newPage()
await p.route('**/api/v1/**', proxy)
const wait = (ms = 1500) => p.waitForTimeout(ms)
const shot = (name) => p.screenshot({ path: `${out}/${name}.png` })
// El botón flotante de WhatsApp no va en la pantalla del celular de la maqueta
await p.addInitScript(() => document.addEventListener('DOMContentLoaded', () => { const st = document.createElement('style'); st.textContent = '.wa, .wa-float, [class*="whatsapp"] { display: none !important }'; document.head.appendChild(st) }))

// Catálogo
await p.goto(`${BASE}/productos`, { waitUntil: 'networkidle' })
await wait(2500)
await p.evaluate(() => document.querySelector('.cat-wrap')?.scrollIntoView({ block: 'start' }))
await wait(900)
await shot('catalogo')

// Ficha
for (let i = 0; i < 4; i += 1) {
  await p.goto(`${BASE}/productos/${SLUG}`, { waitUntil: 'networkidle' })
  await wait(2500)
  if (!(await p.getByText('Sin conexión').count())) break
  await wait(4000) // la API limita las peticiones por minuto: se espera y se reintenta
}
await shot('ficha')

// Carrito con un producto real: se toma una referencia de compra directa de la API y se guarda en el carrito (localStorage)
const API = process.env.API ?? 'https://imagen-electrica-ecommerce.onrender.com/api/v1'
const line = (await (await fetch(`${API}/lines/${SLUG}`, { headers: { Accept: 'application/json' } })).json()).data
const v = line.variants.find((x) => x.sale_mode === 'direct' && x.stock > 0 && x.price) ?? line.variants[0]
const item = { id: v.id, sku: v.sku, name: v.name, slug: v.slug, line_slug: line.slug, line_name: line.name, label: v.label, image_url: line.image_url, unit: v.unit, unit_label: v.unit_label, price: v.price, tax_rate: v.tax_rate, stock: v.stock, sale_mode: v.sale_mode, brand: v.brand?.name ?? null, category: line.category?.name ?? null }
await p.evaluate((cart) => localStorage.setItem('ie.cart', JSON.stringify(cart)), [{ product: item, quantity: 2 }])
await p.goto(`${BASE}/carrito`, { waitUntil: 'networkidle' })
await wait(2500)
await p.evaluate(() => document.querySelector('.cart-item')?.scrollIntoView({ block: 'center' }))
await wait(500)
await shot('carrito')

// Confirmación del pedido: sesión simulada y POST /orders simulado
await p.route('**/api/v1/me', (r) => r.fulfill({ json: { data: { id: 1, name: 'Cliente de ejemplo', email: 'cliente@ejemplo.co', role: 'cliente', customer: { company_name: 'Taller Ejemplo', nit: '900123456', contact_name: 'Cliente de ejemplo', phone: '3001234567', city: 'Bucaramanga', address: 'Calle 1 # 2-3' } } } }))
await p.route('**/api/v1/orders', (r) => (r.request().method() === 'POST' ? r.fulfill({ status: 201, json: { data: [{ code: 'PED-0001', type: 'purchase', type_label: 'Pedido', status: 'pending_payment', total: 1215000 }] } }) : r.fulfill({ json: { data: [], meta: {} } })))
await p.addInitScript(() => localStorage.setItem('ie.token', 'captura'))
await p.goto(`${BASE}/carrito`, { waitUntil: 'networkidle' })
await wait(2000)
const send = p.getByRole('button', { name: /Enviar pedido/ }).last()
if (await send.count()) { await send.click(); await wait(1500) }
await p.evaluate(() => scrollTo(0, 0))
await wait(400)
await shot('confirmacion')

// ---------- Cotización libre ----------
// Contexto nuevo: sin la sesión simulada
const q = await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true })
const pq = await q.newPage()
await pq.route('**/api/v1/**', proxy)
await pq.addInitScript(() => document.addEventListener('DOMContentLoaded', () => { const st = document.createElement('style'); st.textContent = '.whatsapp { display: none !important }'; document.head.appendChild(st) }))
await pq.route('**/api/v1/quote-requests/free', (r) => r.fulfill({ status: 201, json: { data: { reference: 'COT-0042', response_time: 'en menos de 4 horas' } } }))
await pq.goto(`${BASE}/cotizar`, { waitUntil: 'networkidle' })
await wait(1500)
await pq.screenshot({ path: `${out}/cotizar.png` })
await pq.getByRole('button', { name: /Llenar formulario/ }).click()
await wait(900)
const fill = async (label, value) => pq.getByLabel(label, { exact: false }).first().fill(value)
await fill('Nombre', 'Taller Ejemplo')
await fill('Teléfono', '3001234567')
await fill('Dirección', 'Calle 1 # 2-3')
await fill('Ciudad', 'Bucaramanga')
await pq.getByRole('button', { name: /Continuar/ }).click()
await wait(700)
await pq.getByLabel('¿Qué productos necesitas?').fill('20 metros de cable 4x10 THHN y 2 guardamotores de 10 a 16 A para una bomba de 5 HP.')
await pq.setInputFiles('input[type=file]', { name: 'placa-motor.jpg', mimeType: 'image/jpeg', buffer: Buffer.from('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAAAP/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q==', 'base64') })
await pq.getByLabel('¿Qué productos necesitas?').press('Tab')
await wait(500)
await pq.evaluate(() => document.querySelector('.cz-file')?.scrollIntoView({ block: 'center' }))
await wait(500)
await pq.screenshot({ path: `${out}/adjuntar.png` })
await pq.locator('.cz-check input').check()
await pq.getByRole('button', { name: /Enviar cotización/ }).click()
await wait(1500)
await pq.screenshot({ path: `${out}/cotizacion-enviada.png` })

// ---------- Portátil (1440 × 900 @2x): el catálogo ----------
const desk = await b.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 })
const d = await desk.newPage()
await d.route('**/api/v1/**', proxy)
await d.goto(`${BASE}/productos`, { waitUntil: 'networkidle' })
await d.waitForTimeout(2500)
await d.screenshot({ path: `${out}/portatil.png` })

await b.close()
console.log('listo →', out)
