import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'

// Celular con la pantalla real de la tienda (captura) o, en el paso del asesor, su respuesta por WhatsApp (texto genérico,
// sin precios inventados). Es decoración: el sentido del paso lo da `alt` en el contenedor.
const CHATS = {
  quote: [
    ['in', 'Hola, quiero cotizar la lista que envié desde la tienda.'],
    ['out', 'Hola, ya la revisé. Te confirmo las referencias, el precio y el tiempo de entrega.'],
    ['out', '¿La recibes en tu obra o prefieres recogerla en la tienda?'],
    ['in', 'En la obra, por favor.'],
  ],
  order: [
    ['in', 'Hola, acabo de enviar mi pedido desde la tienda.'],
    ['out', 'Hola, ya lo recibí. Te confirmo el pago y la entrega.'],
    ['out', '¿Lo recoges en la tienda o te lo enviamos?'],
    ['in', 'Envíenlo a la obra, por favor.'],
  ],
}

export default function PhoneMock({ mock, chat, alt }) {
  return (
    <div className="phone" role="img" aria-label={alt}>
      <div className="phone__bezel">
        <span className="phone__notch" aria-hidden="true" />
        <div className="phone__screen">
          {chat ? (
            <div className="phone__chat" aria-hidden="true">
              <header><MessageCircle size={16} /> <b>{site.name}</b><small>Asesor · en línea</small></header>
              {CHATS[chat === true ? 'quote' : chat].map(([who, text]) => <p key={text} className={who}>{text}</p>)}
            </div>
          ) : (
            <img src={`/img/mockups/${mock}.webp`} alt="" width="390" height="844" loading="lazy" />
          )}
        </div>
      </div>
    </div>
  )
}
