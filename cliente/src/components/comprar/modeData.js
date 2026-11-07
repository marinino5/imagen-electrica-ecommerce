import { FileText, ShoppingCart } from 'lucide-react'
import { site } from '../../config/site'

// Los dos caminos de compra. Todo el contenido de la página cambia según el modo elegido.
export const MODES = {
  direct: {
    id: 'direct',
    label: 'Compra en línea',
    short: 'Productos con precio publicado, listos para comprar en línea. IVA incluido.',
    icon: ShoppingCart,
    lead: 'Productos con precio publicado, listos para comprar en línea. Los compras con tu cuenta y un asesor confirma el pago y la entrega. Si necesitas más cantidad o algo especial, cotízalo.',
    needs: ['Cuenta gratis (persona natural o empresa)', 'Referencias con precio publicado', 'Pago y entrega confirmados por un asesor'],
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

// Pasos de cada camino. Cada paso lleva una imagen pequeña dentro de su tarjeta del circuito:
//  - `mock: 'laptop'` | 'ficha' | 'carrito' | 'cotizacion': captura real de la tienda dentro de un portátil o un celular
//    (public/img/mockups/*.webp, se generan con cliente/scripts/capturar-mockups.mjs);
//  - `chat`: el asesor respondiendo por WhatsApp ('order' o 'quote');
//  - `photo`: foto real.
export const STEPS = {
  direct: [
    { title: 'Busca y filtra', text: 'Escribe el nombre, la referencia o el SKU, o recorre las familias. Filtra por marca y forma de compra.', mock: 'laptop', alt: 'La tienda en un portátil con el buscador y los resultados de motores' },
    { title: 'Elige la referencia', text: 'Escoge potencia, velocidad y referencia; el precio se actualiza al instante y la agregas al carrito.', mock: 'ficha', alt: 'Ficha de un motor en el celular, con el selector de referencia y el botón Agregar al carrito' },
    { title: 'Envía y un asesor confirma', text: 'Revisa el carrito, elige cómo lo recibes y envía. Un asesor confirma el pago y la entrega por WhatsApp.', chat: 'order', alt: 'Conversación de WhatsApp en la que un asesor de Imagen Eléctrica confirma un pedido' },
    { title: 'Recibe y sigue tu pedido', text: 'Recógelo en la tienda o recíbelo en tu obra. En Mi cuenta ves cada pedido con su estado.', photo: '/img/fotos/entrega-almacen.webp', alt: 'Operario escaneando una caja de material eléctrico para despacharla' },
  ],
  quote: [
    { title: 'Arma tu lista', text: 'Usa «Solicitar cotización» en cada ficha y suma las referencias que necesitas.', mock: 'ficha', alt: 'Ficha de un motor en el celular con el botón Solicitar cotización' },
    { title: 'Envía sin cuenta', text: 'Déjanos nombre, WhatsApp, correo y ciudad. Empresa, NIT y mensaje son opcionales.', mock: 'cotizacion', alt: 'Formulario de cotización en el celular con la lista de referencias' },
    { title: 'El asesor responde', text: 'Te escribe por WhatsApp o correo con la referencia, el precio y el tiempo de entrega.', chat: 'quote', alt: 'Conversación de WhatsApp en la que un asesor de Imagen Eléctrica responde una cotización' },
    { title: 'Aprueba y recibe', text: 'Cuando apruebes, el asesor confirma el pago y coordina la entrega.', photo: '/img/fotos/entrega-almacen.webp', alt: 'Operario escaneando una caja de material eléctrico para despacharla' },
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
  { modes: ['direct', 'quote'], q: '¿Puedo cancelar mi solicitud?', a: 'Pídeselo a tu asesor. Un pedido se puede cancelar mientras está pendiente de pago.' },
  { modes: ['quote'], q: '¿Puedo cotizar una referencia que no tiene precio?', a: 'Sí. Va a tu cotización y un asesor te responde con el precio y el tiempo de entrega.' },
  { modes: ['quote'], q: '¿Cuándo recibo el precio?', a: 'Cuando el asesor revisa tu lista y te escribe por WhatsApp o correo. Suele ser lo primero que hace.' },
  { modes: ['quote'], q: '¿Qué pasa cuando apruebo la cotización?', a: 'El asesor confirma el pago, coordina la entrega y arma tu pedido con esas referencias.' },
  { modes: ['direct', 'quote'], q: '¿Cuánto cuesta el envío?', a: 'Recoger en tienda no tiene costo. Para domicilio o envío nacional, el costo lo confirma tu asesor.' },
  { modes: ['direct'], q: '¿Cómo sigo el estado de mi pedido?', a: 'En Mi cuenta ves todos tus pedidos con su estado actual.' },
  { modes: ['quote'], q: '¿Dónde veo mi cotización?', a: 'Las cotizaciones sin cuenta las responde un asesor por WhatsApp o correo, con los datos que dejaste.' },
  { modes: ['direct', 'quote'], q: '¿Puedo cotizar algo que no está en el catálogo?', a: 'Sí. Descríbenos lo que necesitas, con foto, placa del equipo o lista de materiales si la tienes, y un asesor lo busca y te responde con precio.', link: { to: '/cotizar', label: 'Cotizar lo que necesito' } },
  { modes: ['direct', 'quote'], q: '¿Cuánto tardan en responder una cotización?', a: `Un asesor responde ${site.quoteResponse}, por WhatsApp o por correo, con la referencia, el precio y el tiempo de entrega.` },
  { modes: ['direct', 'quote'], q: '¿Cotizan por volumen o para proyectos?', a: 'Sí. Envía tu lista completa o descríbela en la cotización libre y el asesor te responde con precio por cantidad y tiempo de entrega.', link: { to: '/cotizar', label: 'Cotizar mi proyecto' } },
]
