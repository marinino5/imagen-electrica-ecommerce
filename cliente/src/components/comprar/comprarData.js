import { site } from '../../config/site'

// Pasos de los dos caminos. `screen` = pantalla real de la tienda que se ve en el celular fijo (public/img/como-comprar/*.webp,
// se generan con cliente/scripts/capturar-mockups.mjs); `chat` = la conversación del asesor por WhatsApp (texto genérico).
// `tip` = el dato útil de la píldora.
export const ONLINE = [
  { title: 'Busca tu referencia', text: 'Escribe el nombre, la referencia o el SKU, o recorre las familias. Filtra por marca y por forma de compra.', tip: 'Precios en COP con IVA incluido', screen: 'catalogo', alt: 'El catálogo de la tienda en el celular, con el buscador y las líneas de producto' },
  { title: 'Elige y agrega', text: 'Escoge potencia, velocidad y referencia. El precio se actualiza al instante y la agregas al carrito.', tip: 'Fichas con datos del fabricante', screen: 'ficha', alt: 'Ficha de un producto en el celular, con el selector de referencia y el botón Agregar al carrito' },
  { title: 'Revisa tu carrito', text: 'Elige si lo recoges o te lo enviamos, escribe tus datos de facturación y confirma. No necesitas crear una cuenta.', tip: 'Recoger en tienda no cuesta', screen: 'carrito', alt: 'El carrito en el celular con el producto elegido y el total a pagar' },
  { title: 'Recibe tu confirmación', text: 'Te damos tu número de pedido y te enviamos un correo con un enlace para ver su estado. Un asesor confirma el pago y la entrega.', tip: 'El enlace del correo vale 60 días', screen: 'confirmacion', alt: 'Pantalla de pedido enviado en el celular, con el número de pedido' },
]

export const QUOTE = [
  { title: 'Cuéntanos qué necesitas', text: 'Abre «Cotizar» y deja tus datos. Sirve para productos sin precio publicado o que no están en el catálogo.', tip: 'No necesitas cuenta', screen: 'cotizar', alt: 'La página Cotizar en el celular, con las opciones de formulario o WhatsApp' },
  { title: 'Adjunta lo que tengas', text: 'Una foto, la placa del equipo o tu lista de materiales ayudan a acertar con la referencia.', tip: 'PDF, JPG o PNG, hasta 5 MB', screen: 'adjuntar', alt: 'Formulario de cotización en el celular con un archivo adjunto' },
  { title: 'Recibe tu número', text: 'Al enviar te damos un número de solicitud para que el asesor encuentre tu lista de inmediato.', tip: `Respuesta ${site.quoteResponse}`, screen: 'cotizacion-enviada', alt: 'Pantalla de solicitud enviada en el celular, con el número de la cotización' },
  { title: 'Sigue por WhatsApp', text: 'El asesor te escribe con la referencia, el precio y el tiempo de entrega. Tú apruebas y coordinan el pago.', tip: site.mainContact.label, chat: 'quote', alt: 'Conversación de WhatsApp en la que un asesor de Imagen Eléctrica responde una cotización' },
]

// Lo que recibes: tres tarjetas con foto real
export const RECEIVE = [
  { title: 'Producto original, en su caja', text: 'Referencias de fabricantes reconocidos, selladas y con su ficha técnica.', photo: '/img/fotos/comprar-caja.webp', alt: 'Caja de cartón abierta con el producto protegido por plástico de burbujas' },
  { title: 'Asesoría de un técnico', text: 'Quien te atiende conoce el tablero y la máquina: te recomienda la referencia exacta.', photo: '/img/fotos/comprar-tecnico.webp', alt: 'Manos de un técnico midiendo con un multímetro en un equipo eléctrico' },
  { title: 'Despacho cuidadoso', text: 'Armamos y revisamos tu pedido antes de entregarlo o enviarlo.', photo: '/img/fotos/comprar-despacho.webp', alt: 'Dos operarios llevando una caja por el pasillo de una bodega con estanterías' },
]

// Envíos: tres datos junto al mapa
export const SHIPPING = [
  { big: 'Gratis', title: 'Recoger en la tienda', text: `${site.address}, Bucaramanga.` },
  { big: 'Local', title: 'Domicilio en el área metropolitana', text: 'El costo lo confirma tu asesor.' },
  { big: 'Nacional', title: 'Envío a todo el país', text: 'Llega a donde esté tu obra o planta.' },
]

// Preguntas por pestaña: respuestas de máximo tres líneas
// Preguntas frecuentes: máximo cinco, con respuestas de máximo tres líneas
export const FAQ = [
  { q: '¿Necesito una cuenta?', a: 'No. Puedes comprar como invitado con tus datos de facturación y de entrega, y cotizar con nombre, WhatsApp y ciudad. Crear una cuenta es opcional: guarda tus datos y tus pedidos.' },
  { q: '¿Los precios incluyen IVA?', a: 'Sí. Todo está en COP con IVA incluido y el carrito muestra la base y el IVA.' },
  { q: '¿Cuánto tardan en responder una cotización?', a: `Un asesor responde ${site.quoteResponse} por WhatsApp o correo, con precio y entrega.` },
  { q: '¿Cómo pago?', a: 'Un asesor confirma el pago contigo. El pago en línea llegará más adelante.' },
  { q: '¿Cuánto cuesta el envío?', a: 'Recoger en la tienda es gratis. El domicilio y el envío nacional los confirma tu asesor.' },
]
