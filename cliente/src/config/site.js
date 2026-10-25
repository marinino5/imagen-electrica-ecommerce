// Datos reales de Imagen Eléctrica. Si falta un dato (horario, hitos, cifras, testimonios),
// esa parte no se muestra: no se inventa nada.
const phones = ['+57 301 742 3427', '+57 315 111 1184']

// PENDIENTE: confirmar el número de WhatsApp oficial. Mientras tanto se usa el primer teléfono.
const whatsapp = import.meta.env.VITE_WHATSAPP_NUMBER ?? phones[0].replace(/\D/g, '')

const dialable = (phone) => phone.replace(/[^\d+]/g, '')

export const site = {
  name: 'Imagen Eléctrica',
  tagline: 'Soluciones Industriales',
  years: 25,
  address: 'Cra 18 No 24-32',
  city: 'Bucaramanga, Santander',
  phones: phones.map((label) => ({ label, href: `tel:${dialable(label)}` })),
  email: import.meta.env.VITE_EMAIL ?? 'info@imagenelectrica.com',
  whatsappUrl: `https://wa.me/${whatsapp}?text=${encodeURIComponent('Hola, quiero información sobre sus productos.')}`,
  social: {
    facebook: 'https://facebook.com/imagenelectrica',
    instagram: 'https://instagram.com/imagenelectrica1824',
  },
  // Horario: pendiente de confirmar; no se muestra hasta tenerlo
  hours: null,
}

export const mainMenu = [
  { to: '/', label: 'Inicio', end: true },
  { to: '/productos', label: 'Nuestros productos' },
  { to: '/como-comprar', label: 'Cómo comprar' },
  { to: '/nosotros', label: 'Nosotros' },
  { to: '/contacto', label: 'Contacto' },
]
