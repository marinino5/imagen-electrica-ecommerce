import { FileText, ShoppingCart } from 'lucide-react'

// Los dos caminos de compra. Todo el contenido de la página cambia según el modo elegido.
export const MODES = {
  direct: {
    id: 'direct',
    label: 'Compra directa',
    icon: ShoppingCart,
    lead: 'Productos disponibles, con precio en COP e IVA incluido. Tu asesor confirma el pago y la entrega.',
    cta: { label: 'Ver productos', to: '/productos' },
  },
  quote: {
    id: 'quote',
    label: 'Cotización',
    icon: FileText,
    lead: 'Para productos industriales, bajo pedido o sin stock. Un asesor pone el precio y te responde.',
    cta: { label: 'Solicitar cotización', to: '/productos?sale_mode=quote' },
  },
}

// Seis pasos del recorrido. `screen` elige la mini pantalla que se muestra en la ventana del navegador.
export const STEPS = {
  direct: [
    { screen: 'search', path: '/productos', title: 'Busca o explora por línea', text: 'Escribe el nombre o la referencia y elige entre las sugerencias, o recorre el catálogo por línea.' },
    { screen: 'product', path: '/productos', title: 'Agrega al carrito', text: 'Los productos con la insignia Disponible tienen precio en COP con IVA incluido y van directo a tu carrito.' },
    { screen: 'auth', path: '/crear-cuenta', title: 'Ingresa o crea tu cuenta', text: 'Como persona natural o empresa. Tu carrito se conserva mientras te registras.' },
    { screen: 'delivery', path: '/carrito', title: 'Elige cómo recibirlo y envía tu solicitud', text: 'Recoge en tienda, envío local o envío nacional. Enviar la solicitud no genera ningún cobro automático.' },
    { screen: 'status', path: '/mi-cuenta', title: 'Sigue el estado en Mi cuenta', text: 'Tu pedido avanza de Pendiente de pago a Pagado, Enviado y Entregado, y lo ves en tiempo real.' },
    { screen: 'advisor', path: '/mi-cuenta', title: 'El asesor confirma el pago y la entrega', text: 'Te indica los medios de pago disponibles, confirma tu pago y coordina la entrega.' },
  ],
  quote: [
    { screen: 'search', path: '/productos', title: 'Busca o explora por línea', text: 'Escribe el nombre o la referencia, o recorre el catálogo. Si no ves tu referencia, también la puedes pedir.' },
    { screen: 'product', path: '/productos', title: 'Agrega a tu cotización', text: 'Los productos Solo cotización, Bajo pedido o sin stock van a tu cotización, sin precio hasta que el asesor lo ponga.' },
    { screen: 'auth', path: '/crear-cuenta', title: 'Ingresa o crea tu cuenta', text: 'Como persona natural o empresa. Tu cotización se conserva mientras te registras.' },
    { screen: 'delivery', path: '/carrito', title: 'Elige cómo recibirlo y envía tu solicitud', text: 'Indica si recoges en tienda o prefieres envío local o nacional, y envía la solicitud.' },
    { screen: 'status', path: '/mi-cuenta', title: 'Sigue el estado en Mi cuenta', text: 'Tu cotización pasa de Nueva a En revisión, Enviada al cliente y Aceptada.' },
    { screen: 'advisor', path: '/mi-cuenta', title: 'El asesor te responde y coordina', text: 'Valora tu cotización con precios. Al aceptarla se crea tu pedido y el asesor confirma el pago y la entrega.' },
  ],
}

export const FAQS = [
  { modes: ['direct', 'quote'], q: '¿Necesito una cuenta para comprar o cotizar?', a: 'Sí. Para enviar tu solicitud crea una cuenta como persona natural o como empresa. Es gratis y tus datos quedan guardados para la próxima vez.' },
  { modes: ['direct', 'quote'], q: '¿Qué pasa si mi carrito mezcla compra directa y cotización?', a: 'Creamos dos solicitudes separadas: un pedido con lo que está disponible y una cotización con el resto.' },
  { modes: ['direct'], q: '¿Los precios incluyen IVA?', a: 'Sí. Los precios están en COP con el IVA incluido.' },
  { modes: ['direct'], q: '¿Puedo pedir más unidades de las que hay disponibles?', a: 'No en compra directa. Si pides más que el stock, esa referencia pasa a tu cotización y un asesor te responde.' },
  { modes: ['direct'], q: '¿Cuándo se descuenta el inventario?', a: 'Cuando el asesor confirma tu pago. En ese momento se vuelve a validar la disponibilidad y se descuenta el stock.' },
  { modes: ['direct', 'quote'], q: '¿Puedo cancelar mi solicitud?', a: 'Pídeselo a tu asesor. Un pedido se puede cancelar mientras está pendiente de pago y una cotización mientras está nueva.' },
  { modes: ['quote'], q: '¿Qué pasa si un producto está agotado?', a: 'Va a tu cotización: un asesor te responde con la disponibilidad y el precio.' },
  { modes: ['quote'], q: '¿Cuándo recibo el precio?', a: 'Cuando el asesor valora tu cotización y te la envía. No se envía hasta tener el precio de todos los productos.' },
  { modes: ['quote'], q: '¿Qué pasa cuando acepto la cotización?', a: 'Se genera un pedido nuevo con esos productos y tu cotización se conserva en Mi cuenta.' },
  { modes: ['direct', 'quote'], q: '¿Cuánto cuesta el envío?', a: 'El costo se confirma con tu asesor según cómo lo recibas. Recoger en tienda no tiene costo de envío.' },
  { modes: ['direct', 'quote'], q: '¿Cómo sigo el estado de mi solicitud?', a: 'En Mi cuenta ves todos tus pedidos y cotizaciones con su estado actual.' },
]
