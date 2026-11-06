import { FileText, ShoppingCart } from 'lucide-react'

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

// Pasos de cada camino. `image` es una captura real de la tienda; sin imagen se muestra un icono.
export const STEPS = {
  direct: [
    { title: 'Busca y filtra', text: 'Escribe la referencia o recorre las líneas, y filtra por marca, potencia y disponibilidad.', image: 'paso-catalogo.webp', w: 900, h: 675, alt: 'Tarjeta de una línea del catálogo con su precio desde', path: '/productos' },
    { title: 'Elige la variante', text: 'Escoge potencia, velocidad y referencia. El precio y las unidades en bodega se actualizan al instante.', image: 'paso-ficha.webp', w: 900, h: 778, alt: 'Selector de potencia, velocidad y referencia en la ficha', path: '/productos/w22-iec-carcasa-hierro' },
    { title: 'Paga y elige el envío', text: 'Agrega al carrito, escoge cómo lo recibes y envía tu pedido. El pago en línea llegará más adelante: hoy un asesor confirma el pago y la entrega contigo.', image: 'paso-carrito.webp', w: 900, h: 1529, alt: 'Resumen del carrito con base, IVA y opciones de entrega', path: '/carrito' },
    { title: 'Sigue tu pedido', text: 'En Mi cuenta ves cada pedido con su línea de estados: pago pendiente, pagado, enviado y entregado.', image: 'paso-pedido.webp', w: 900, h: 169, alt: 'Línea de estados de un pedido en Mi cuenta', path: '/mi-cuenta' },
  ],
  quote: [
    { title: 'Arma tu lista', text: 'Usa «Cotizar volumen» o «Solicitar cotización» en cada ficha. También puedes pedir líneas que se manejan bajo pedido.', image: 'paso-ficha.webp', w: 900, h: 778, alt: 'Ficha de una línea con los botones para cotizar', path: '/productos/w22-iec-carcasa-hierro' },
    { title: 'Envía la solicitud sin cuenta', text: 'Déjanos tu nombre, WhatsApp, correo y ciudad. Empresa, NIT y mensaje son opcionales.', image: 'paso-cotizacion.webp', w: 900, h: 623, alt: 'Formulario de cotización sin cuenta', path: '/carrito#cotizacion' },
    { title: 'El asesor responde', text: 'Te contacta por WhatsApp o correo con la referencia, el precio y el tiempo de entrega.', icon: 'headset' },
    { title: 'Aprueba y paga', text: 'Cuando apruebes, el asesor confirma el pago y coordina la entrega. El pago en línea llegará más adelante.', icon: 'card' },
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
  { modes: ['direct'], q: '¿Cuándo se descuenta el inventario?', a: 'Cuando el asesor confirma tu pago: en ese momento se vuelve a validar la disponibilidad y se descuenta el stock.' },
  { modes: ['direct', 'quote'], q: '¿Puedo cancelar mi solicitud?', a: 'Pídeselo a tu asesor. Un pedido se puede cancelar mientras está pendiente de pago.' },
  { modes: ['quote'], q: '¿Qué pasa si una referencia está agotada?', a: 'Va a tu cotización: un asesor te responde con la disponibilidad y el precio.' },
  { modes: ['quote'], q: '¿Cuándo recibo el precio?', a: 'Cuando el asesor revisa tu lista y te escribe por WhatsApp o correo. Suele ser lo primero que hace.' },
  { modes: ['quote'], q: '¿Qué pasa cuando apruebo la cotización?', a: 'El asesor confirma el pago, coordina la entrega y arma tu pedido con esas referencias.' },
  { modes: ['direct', 'quote'], q: '¿Cuánto cuesta el envío?', a: 'Recoger en tienda no tiene costo. Para domicilio o envío nacional, el costo lo confirma tu asesor.' },
  { modes: ['direct'], q: '¿Cómo sigo el estado de mi pedido?', a: 'En Mi cuenta ves todos tus pedidos con su estado actual.' },
  { modes: ['quote'], q: '¿Dónde veo mi cotización?', a: 'Las cotizaciones sin cuenta las responde un asesor por WhatsApp o correo, con los datos que dejaste.' },
]
