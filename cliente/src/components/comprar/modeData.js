import { FileText, ShoppingCart } from 'lucide-react'
import { site } from '../../config/site'

// Los dos caminos de compra. Todo el contenido de la página cambia según el modo elegido.
export const MODES = {
  direct: {
    id: 'direct',
    label: 'Compra en línea',
    short: 'Lo que está en bodega, con precio en COP e IVA incluido.',
    icon: ShoppingCart,
    lead: 'Productos con precio y unidades en bodega. Los compras con tu cuenta y un asesor confirma el pago y la entrega.',
    needs: ['Cuenta gratis (persona natural o empresa)', 'Referencias con precio y bodega', 'Pago y entrega confirmados por un asesor'],
    cta: { label: 'Ver el catálogo', to: '/productos?modo=en-linea' },
  },
  quote: {
    id: 'quote',
    label: 'Cotización',
    short: 'Para lo que no tiene precio publicado, está bajo pedido o es por volumen.',
    icon: FileText,
    lead: 'Para productos industriales, bajo pedido o por volumen. Envías tu lista sin crear cuenta y un asesor te responde con precio.',
    needs: ['No necesitas cuenta', 'Nombre, WhatsApp, correo y ciudad', 'Un asesor responde con referencia y precio'],
    cta: { label: 'Armar mi cotización', to: '/carrito#cotizacion' },
  },
}

// Pasos de cada camino. Cada paso lleva una foto que muestra exactamente lo que dice o, si no hay una foto exacta, una captura
// real de la tienda dentro de un celular (`mock`: 'ficha' | 'carrito' | 'cotizacion' en public/img/mockups/) o la conversación
// de WhatsApp con el asesor (`chat`). Las capturas se generan con cliente/scripts/capturar-mockups.mjs.
export const STEPS = {
  direct: [
    { title: 'Busca y filtra', text: 'Escribe el nombre, la referencia o el SKU, o recorre las familias y categorías. Filtra por forma de compra y por marca.', photo: '/img/fotos/busca-portatil.webp', alt: 'Persona buscando una referencia en un portátil junto a una máquina industrial', path: '/productos' },
    { title: 'Elige la referencia y agrega', text: 'Escoge potencia, velocidad y referencia. El precio y las unidades en bodega se actualizan al instante; agrégala al carrito.', mock: 'ficha', alt: 'Ficha de un motor en el celular, con el selector de referencia y el botón Agregar al carrito', path: '/productos/w22-iec-carcasa-hierro' },
    { title: 'Envía tu pedido; un asesor confirma pago y entrega', text: 'Revisa el carrito, escoge cómo lo recibes y envía tu pedido. Un asesor te contacta para confirmar el pago y la entrega; el pago en línea llegará más adelante.', mock: 'carrito', alt: 'Carrito de compras en el celular con un producto y las opciones de entrega', path: '/carrito' },
    { title: 'Recibe y sigue tu pedido', text: 'Recógelo en la tienda o recíbelo en tu obra o planta. En Mi cuenta ves cada pedido con su línea de estados: pago pendiente, pagado, enviado y entregado.', photo: '/img/fotos/despacho-montacargas.webp', alt: 'Bodega con estanterías y un montacargas listo para despachar cajas', path: '/mi-cuenta' },
  ],
  quote: [
    { title: 'Arma tu lista', text: 'Usa «Solicitar cotización» en cada ficha, también en las líneas que se manejan bajo pedido, y ve sumando referencias.', mock: 'ficha', alt: 'Ficha de un motor en el celular con el botón Solicitar cotización', path: '/productos/w22-iec-carcasa-hierro' },
    { title: 'Envía la solicitud sin cuenta', text: 'Déjanos tu nombre, WhatsApp, correo y ciudad. Empresa, NIT y mensaje son opcionales.', mock: 'cotizacion', alt: 'Formulario de cotización en el celular con la lista de referencias', path: '/carrito#cotizacion' },
    { title: 'El asesor responde', text: 'Te contacta por WhatsApp o correo con la referencia, el precio y el tiempo de entrega.', chat: true, alt: 'Conversación de WhatsApp en la que un asesor de Imagen Eléctrica responde una cotización' },
    { title: 'Aprueba y coordina la entrega', text: 'Cuando apruebes, el asesor confirma el pago y coordina la entrega. El pago en línea llegará más adelante.', photo: '/img/fotos/despacho-montacargas.webp', alt: 'Bodega con estanterías y un montacargas listo para despachar cajas' },
  ],
}

export const DELIVERY = [
  { id: 'pickup', title: 'Recoger en tienda', tag: 'Gratis', text: 'Cra 18 No 24-32, Bucaramanga. Sin costo de envío.' },
  { id: 'local', title: 'Domicilio', tag: 'Área metropolitana', text: 'En Bucaramanga y su área metropolitana. El costo lo confirma tu asesor.' },
  { id: 'national', title: 'Envío nacional', tag: 'Todo el país', text: 'A donde estés. El costo lo confirma tu asesor.' },
]

export const FAQS = [
  { modes: ['direct', 'quote'], q: '¿Necesito una cuenta?', a: 'Para cotizar no: envías tus datos y un asesor te responde. Para comprar en línea sí; la cuenta es gratis, como persona natural o empresa, y guarda tus datos para la próxima vez.' },
  { modes: ['direct', 'quote'], q: '¿Qué pasa si mezclo compra en línea y cotización?', a: 'Van por separado: lo que se compra en línea se envía como pedido y la cotización tiene su propio bloque en el carrito.' },
  { modes: ['direct'], q: '¿Los precios incluyen IVA?', a: 'Sí. Los precios están en COP con el IVA incluido y el carrito te muestra la base y el IVA.' },
  { modes: ['direct'], q: '¿Puedo pedir más unidades de las que hay en bodega?', a: 'No en compra en línea. Si pides más que las unidades en bodega, esa referencia pasa a cotización y un asesor te responde.' },
  { modes: ['direct', 'quote'], q: '¿Puedo cancelar mi solicitud?', a: 'Pídeselo a tu asesor. Un pedido se puede cancelar mientras está pendiente de pago.' },
  { modes: ['quote'], q: '¿Qué pasa si una referencia está agotada?', a: 'Va a tu cotización: un asesor te responde con la disponibilidad y el precio.' },
  { modes: ['quote'], q: '¿Cuándo recibo el precio?', a: 'Cuando el asesor revisa tu lista y te escribe por WhatsApp o correo. Suele ser lo primero que hace.' },
  { modes: ['quote'], q: '¿Qué pasa cuando apruebo la cotización?', a: 'El asesor confirma el pago, coordina la entrega y arma tu pedido con esas referencias.' },
  { modes: ['direct', 'quote'], q: '¿Cuánto cuesta el envío?', a: 'Recoger en tienda no tiene costo. Para domicilio o envío nacional, el costo lo confirma tu asesor.' },
  { modes: ['direct'], q: '¿Cómo sigo el estado de mi pedido?', a: 'En Mi cuenta ves todos tus pedidos con su estado actual.' },
  { modes: ['quote'], q: '¿Dónde veo mi cotización?', a: 'Las cotizaciones sin cuenta las responde un asesor por WhatsApp o correo, con los datos que dejaste.' },
  { modes: ['direct', 'quote'], q: '¿Puedo cotizar algo que no está en el catálogo?', a: 'Sí. Descríbenos lo que necesitas, con foto, placa del equipo o lista de materiales si la tienes, y un asesor lo busca y te responde con precio.', link: { to: '/cotizar', label: 'Cotizar lo que necesito' } },
  { modes: ['direct', 'quote'], q: '¿Cuánto tardan en responder una cotización?', a: `Un asesor responde ${site.quoteResponse}, por WhatsApp o por correo, con la referencia, el precio y el tiempo de entrega.` },
  { modes: ['direct', 'quote'], q: '¿Cotizan por volumen o para proyectos?', a: 'Sí. Envía tu lista completa o descríbela en la cotización libre y el asesor te responde con precio por cantidad y tiempo de entrega.', link: { to: '/cotizar', label: 'Cotizar mi proyecto' } },
]
