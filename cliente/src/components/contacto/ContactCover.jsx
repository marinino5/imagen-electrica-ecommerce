import { useEffect, useState } from 'react'
import { motion, useReducedMotion } from 'framer-motion'
import { Headset, Mail, MessageCircle, Phone, Zap } from 'lucide-react'
import WordReveal from '../ui/WordReveal'
import { site } from '../../config/site'

// Conversación de ejemplo (no es un chat real: está marcada como "Ejemplo")
const SCRIPT = [
  { from: 'client', text: 'Hola, necesito un variador para un motor de 5 HP.' },
  { from: 'advisor', text: '¡Hola! Con gusto. ¿A qué voltaje trabaja y para qué aplicación es?' },
  { from: 'client', text: '220 V, es para una bomba.' },
  { from: 'advisor', text: 'Perfecto. Te envío las opciones con su precio e IVA incluido.' },
]

function Typing() {
  return (
    <span className="bubble bubble--advisor bubble--typing" aria-hidden="true">
      <i /><i /><i />
    </span>
  )
}

// Tarjeta que simula una conversación: las burbujas aparecen una por una y el ciclo se repite
function ChatExample() {
  const reduced = useReducedMotion()
  const [shown, setShown] = useState(reduced ? SCRIPT.length : 0)
  const [typing, setTyping] = useState(false)

  useEffect(() => {
    if (reduced) return
    let timer
    if (shown >= SCRIPT.length) {
      timer = setTimeout(() => setShown(0), 5200) // pausa y vuelve a empezar
    } else if (SCRIPT[shown].from === 'advisor') {
      setTyping(true)
      timer = setTimeout(() => {
        setTyping(false)
        setShown((n) => n + 1)
      }, 1200)
    } else {
      timer = setTimeout(() => setShown((n) => n + 1), shown === 0 ? 900 : 1300)
    }
    return () => clearTimeout(timer)
  }, [shown, reduced])

  return (
    <motion.aside
      className="chat"
      aria-label="Ejemplo de conversación con un asesor"
      initial={{ opacity: 0, y: 30 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.7, delay: 0.3, ease: [0.22, 1, 0.36, 1] }}
    >
      <div className="chat__head">
        <span className="chat__avatar"><Headset size={20} aria-hidden="true" /></span>
        <div>
          <strong>Asesor de {site.name}</strong>
          <small>Así podría ser tu conversación</small>
        </div>
        <span className="chat__example">Ejemplo</span>
      </div>
      <ol className="chat__body" aria-live="off">
        {SCRIPT.slice(0, shown).map((m, i) => (
          <motion.li
            key={`${i}-${m.text}`}
            className={`chat__row chat__row--${m.from}`}
            initial={{ opacity: 0, y: 14, scale: 0.94 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            transition={{ duration: 0.35, ease: [0.22, 1, 0.36, 1] }}
          >
            <span className={`bubble bubble--${m.from}`}>{m.text}</span>
          </motion.li>
        ))}
        {typing && <li className="chat__row chat__row--advisor"><Typing /></li>}
      </ol>
    </motion.aside>
  )
}

// Portada dividida: violeta con el título y las acciones rápidas; a la derecha, la conversación de ejemplo
export default function ContactCover() {
  return (
    <header className="cover contact-cover on-violet">
      <span className="contact-cover__ring contact-cover__ring--a" aria-hidden="true" />
      <span className="contact-cover__ring contact-cover__ring--b" aria-hidden="true" />
      <Zap className="contact-cover__bolt" size={420} strokeWidth={1} aria-hidden="true" />
      <div className="container contact-cover__grid">
        <div className="contact-cover__copy">
          <p className="tag-pill"><Zap size={14} aria-hidden="true" /> Contacto</p>
          <WordReveal
            className="display contact-cover__title"
            delay={0.1}
            lines={[{ text: 'HABLEMOS DE TU' }, { text: 'PROYECTO', accent: true }]}
          />
          <p className="contact-cover__text">Escríbenos o llámanos y un asesor te responde con la referencia y el precio.</p>
          <div className="contact-cover__actions">
            <a className="btn btn--accent btn--lg" href={site.whatsappUrl} target="_blank" rel="noreferrer">
              <MessageCircle size={20} aria-hidden="true" /> WhatsApp
            </a>
            <a className="btn btn--ghost-light btn--lg" href={site.phones[0].href}>
              <Phone size={20} aria-hidden="true" /> Llamar
            </a>
            <a className="btn btn--ghost-light btn--lg" href={`mailto:${site.email}`}>
              <Mail size={20} aria-hidden="true" /> Correo
            </a>
          </div>
        </div>
        <ChatExample />
      </div>
    </header>
  )
}
