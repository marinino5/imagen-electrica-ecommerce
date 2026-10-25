// Datos de contacto de la tienda. Mientras no se confirmen los reales, se leen del .env.
// (PENDIENTE: reemplazar los valores de marcador de posición por los oficiales.)
const whatsapp = import.meta.env.VITE_WHATSAPP_NUMBER ?? '573000000000'

export const site = {
  name: 'Imagen Eléctrica',
  city: 'Bucaramanga, Santander',
  phone: import.meta.env.VITE_PHONE ?? '+57 300 000 0000',
  email: import.meta.env.VITE_EMAIL ?? 'ventas@imagenelectrica.com',
  whatsappUrl: `https://wa.me/${whatsapp}?text=${encodeURIComponent('Hola, quiero información sobre sus productos.')}`,
}

export const mainMenu = [
  { to: '/', label: 'Inicio', end: true },
  { to: '/productos', label: 'Nuestros productos' },
  { to: '/como-comprar', label: 'Cómo comprar' },
  { to: '/nosotros', label: 'Nosotros' },
  { to: '/contacto', label: 'Contacto' },
]
