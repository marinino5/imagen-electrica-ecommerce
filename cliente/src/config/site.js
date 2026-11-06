// Datos reales de Imagen Eléctrica. Si falta un dato (horario, hitos, cifras, testimonios),
// esa parte no se muestra: no se inventa nada.
const dialable = (phone) => phone.replace(/[^\d+]/g, '')

// Contacto principal y secundarios (fuente única de teléfonos y WhatsApp de toda la tienda)
const contacts = [
  // Sin nombres propios: el primero es la línea principal; los otros dos se muestran solo con su número
  { role: 'Línea principal', label: '+57 316 821 9758', whatsapp: '573168219758', main: true },
  { label: '+57 301 742 3427', whatsapp: '573017423427' },
  { label: '+57 304 521 0355', whatsapp: '573045210355' },
].map((c) => ({ ...c, href: `tel:${dialable(c.label)}` }))

const whatsapp = import.meta.env.VITE_WHATSAPP_NUMBER ?? contacts[0].whatsapp

const whatsappLink = (text) => `https://wa.me/${whatsapp}?text=${encodeURIComponent(text)}`

export const site = {
  name: 'Imagen Eléctrica',
  tagline: 'Soluciones Industriales',
  years: 25,
  address: 'Cra 18 No 24-32',
  city: 'Bucaramanga, Santander, Colombia',
  contacts,
  mainContact: contacts[0],
  // Compatibilidad: lista de teléfonos con el principal primero
  phones: contacts,
  email: import.meta.env.VITE_EMAIL ?? 'imagenelectrica@gmail.com',
  whatsappUrl: whatsappLink('Hola, quiero información sobre sus productos.'),
  whatsappLink,
  // Mapa de Google embebido con la dirección (iframe, sin clave de API)
  // Enlace para abrir la ruta en Google Maps
  directionsUrl: `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent('Cra 18 No 24-32, Bucaramanga, Santander, Colombia')}`,
  mapUrl: `https://www.google.com/maps?q=${encodeURIComponent('Cra 18 No 24-32, Bucaramanga, Santander, Colombia')}&output=embed`,
  social: {
    facebook: 'https://facebook.com/imagenelectrica',
    instagram: 'https://instagram.com/imagenelectrica1824',
  },
  // Datos legales: se muestran en el pie solo si tienen valor (pendientes de confirmar con la empresa)
  legalName: import.meta.env.VITE_LEGAL_NAME ?? '',
  nit: import.meta.env.VITE_NIT ?? '',
  sicUrl: 'https://www.sic.gov.co',
  // Horario: pendiente de confirmar; no se muestra hasta tenerlo
  hours: null,
}

export const mainMenu = [
  { to: '/', label: 'Inicio', end: true },
  { to: '/productos', label: 'Productos' },
  { to: '/como-comprar', label: 'Cómo comprar' },
  { to: '/nosotros', label: 'Nosotros' },
  { to: '/contacto', label: 'Contacto' },
]
