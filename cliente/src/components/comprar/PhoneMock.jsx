import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'

// Conversación del asesor (texto genérico, sin precios inventados): la pantalla de WhatsApp de la cotización
const CHAT = [
  ['in', 'Hola, acabo de enviar la solicitud COT-0042 desde la tienda.'],
  ['out', 'Hola, ya la revisé. Te confirmo las referencias, el precio y el tiempo de entrega.'],
  ['out', '¿La recibes en tu obra o prefieres recogerla en la tienda?'],
  ['in', 'En la obra, por favor.'],
]

function Chat() {
  return (
    <div className="phone__chat" aria-hidden="true">
      <header><MessageCircle size={18} /> <b>{site.name}</b><small>Asesor · en línea</small></header>
      {CHAT.map(([who, text]) => <p key={text} className={who}>{text}</p>)}
    </div>
  )
}

/**
 * Celular con pantallas reales de la tienda. Todas las pantallas están apiladas y la activa se muestra con un fundido de
 * 300 ms («pantalla fija que cambia»). Es decoración: el sentido de cada paso lo da el texto; `label` describe la pantalla visible.
 * `screens`: [{ src } | { chat: true }, ...]; `active`: índice visible; `size`: 'lg' (fijo de las secciones) | 'md' (hero).
 */
export default function PhoneMock({ screens, active = 0, label, size = 'lg', className = '' }) {
  return (
    <div className={`phone phone--${size} ${className}`} role="img" aria-label={label}>
      <div className="phone__bezel">
        <span className="phone__notch" aria-hidden="true" />
        <div className="phone__screen">
          {screens.map((s, i) => (
            <div key={s.src ?? 'chat'} className={`phone__layer ${i === active ? 'is-on' : ''}`} aria-hidden="true">
              {s.chat ? <Chat /> : <img src={s.src} alt="" width="390" height="844" loading={i === 0 ? 'eager' : 'lazy'} decoding="async" />}
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
