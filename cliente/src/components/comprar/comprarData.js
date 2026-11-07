import { site } from '../../config/site'

// Pasos de los dos caminos. `screen` = pantalla real de la tienda que se ve en el celular fijo (public/img/como-comprar/*.webp,
// se generan con cliente/scripts/capturar-mockups.mjs); `chat` = la conversación del asesor por WhatsApp (texto genérico).
// `tip` = el dato útil de la píldora.
export const ONLINE = [
  { title: 'Busca tu referencia', text: 'Escribe el nombre, la referencia o el SKU, o recorre las familias. Filtra por marca y por forma de compra.', tip: 'Precios en COP con IVA incluido', screen: 'catalogo', alt: 'El catálogo de la tienda en el celular, con el buscador y las líneas de producto' },
  { title: 'Elige y agrega', text: 'Escoge potencia, velocidad y referencia. El precio se actualiza al instante y la agregas al carrito.', tip: 'Fichas con datos del fabricante', screen: 'ficha', alt: 'Ficha de un producto en el celular, con el selector de referencia y el botón Agregar al carrito' },
  { title: 'Revisa tu carrito', text: 'Elige si lo recoges o te lo enviamos y envía el pedido con tu cuenta gratis.', tip: 'Recoger en tienda no cuesta', screen: 'carrito', alt: 'El carrito en el celular con el producto elegido y el total a pagar' },
  { title: 'Recibe tu confirmación', text: 'Te damos tu número de pedido y un asesor confirma el pago y la entrega por WhatsApp.', tip: 'Sigue el estado en Mi cuenta', screen: 'confirmacion', alt: 'Pantalla de pedido enviado en el celular, con el número de pedido' },
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
export const FAQ_TABS = [
  {
    id: 'compra',
    label: 'Compra',
    items: [
      { q: '¿Necesito una cuenta?', a: 'Para comprar en línea sí. Es gratis, de persona natural o empresa, y guarda tus datos.' },
      { q: '¿Los precios incluyen IVA?', a: 'Sí. Todo está en COP con IVA incluido y el carrito muestra la base y el IVA.' },
      { q: '¿Cómo sigo mi pedido?', a: 'En Mi cuenta ves cada pedido con su estado: pago pendiente, pagado, enviado o entregado.' },
      { q: '¿Puedo cancelar mi pedido?', a: 'Sí, mientras esté pendiente de pago. Pídeselo a tu asesor por WhatsApp.' },
      { q: '¿Qué pasa si mezclo productos?', a: 'Lo que tiene precio va como pedido y lo demás como cotización, cada uno con su número.' },
      { q: '¿Cómo pago?', a: 'Un asesor confirma el pago contigo. El pago en línea llegará más adelante.' },
    ],
  },
  {
    id: 'cotizacion',
    label: 'Cotización',
    items: [
      { q: '¿Necesito una cuenta para cotizar?', a: 'No. Dejas nombre, WhatsApp y ciudad y un asesor te responde.' },
      { q: '¿Cuánto tardan en responder?', a: `Un asesor responde ${site.quoteResponse} por WhatsApp o correo, con precio y entrega.` },
      { q: '¿Puedo cotizar algo que no está en el catálogo?', a: 'Sí. Descríbelo y adjunta una foto o la placa; el asesor te responde con precio.', link: { to: '/cotizar', label: 'Cotizar lo que necesito' } },
      { q: '¿Cotizan por volumen o para proyectos?', a: 'Sí. Envía tu lista completa y el asesor te responde con precio por cantidad.' },
      { q: '¿Qué pasa cuando apruebo?', a: 'El asesor confirma el pago, coordina la entrega y arma tu pedido con esas referencias.' },
      { q: '¿Puedo cotizar una referencia sin precio?', a: 'Sí. Va a tu cotización y te respondemos con el precio y el tiempo de entrega.' },
    ],
  },
  {
    id: 'envios',
    label: 'Envíos',
    items: [
      { q: '¿Cuánto cuesta el envío?', a: 'Recoger en la tienda es gratis. El domicilio y el envío nacional los confirma tu asesor.' },
      { q: '¿Dónde recojo mi pedido?', a: `En ${site.address}, Bucaramanga. Te avisamos cuando esté listo.` },
      { q: '¿Envían a todo el país?', a: 'Sí, coordinamos el envío a donde esté tu obra o planta.' },
      { q: '¿Cuánto tarda en llegar?', a: 'Depende de la ciudad y del producto. El asesor te lo confirma antes de que apruebes.' },
      { q: '¿Puedo cambiar la dirección de entrega?', a: 'Sí, escríbele a tu asesor antes del despacho y la actualiza.' },
      { q: '¿Qué reviso al recibir?', a: 'Que la caja venga cerrada y las referencias coincidan con tu pedido. Si algo falla, escríbenos.' },
    ],
  },
]
