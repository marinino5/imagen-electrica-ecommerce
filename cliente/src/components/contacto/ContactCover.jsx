import { useEffect, useState } from 'react'
import { motion, useReducedMotion } from 'framer-motion'
import { Headset } from 'lucide-react'
import Bezel from '../ui/Bezel'
import Cover from '../ui/Cover'

// Conversación de ejemplo (no es un chat real: está marcada como "Ejemplo")
const SCRIPT = [
  { from: 'client', text: 'Hola, necesito un variador para un motor de 5 HP.' },
  { from: 'advisor', text: '¡Hola! Con gusto. ¿A qué voltaje trabaja y para qué aplicación es?' },
  { from: 'client', text: '220 V, es para una bomba.' },
  { from: 'advisor', text: 'Perfecto. Te envío las opciones con su precio e IVA incluido.' },
]

// Teléfono en bisel: las burbujas aparecen una por una y el ciclo se repite
function PhoneChat() {
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
    <Bezel className="phone" aria-label="Ejemplo de conversación con un asesor" role="group">
      <div className="phone__screen">
        <span className="phone__notch" aria-hidden="true" />
        <div className="phone__head">
          <span className="phone__avatar"><Headset size={18} aria-hidden="true" /></span>
          <div>
            <strong>Asesor de Imagen Eléctrica</strong>
            <small>Así podría ser tu conversación</small>
          </div>
          <span className="phone__example">Ejemplo</span>
        </div>
        <ol className="phone__body" aria-live="off">
          {SCRIPT.slice(0, shown).map((m, i) => (
            <motion.li
              key={`${i}-${m.text}`}
              className={`phone__row phone__row--${m.from}`}
              initial={{ opacity: 0, y: 14, scale: 0.94 }}
              animate={{ opacity: 1, y: 0, scale: 1 }}
              transition={{ duration: 0.5, ease: [0.32, 0.72, 0, 1] }}
            >
              <span className={`bubble bubble--${m.from}`}>{m.text}</span>
            </motion.li>
          ))}
          {typing && (
            <li className="phone__row phone__row--advisor"><span className="bubble bubble--advisor bubble--typing" aria-hidden="true"><i /><i /><i /></span></li>
          )}
        </ol>
      </div>
    </Bezel>
  )
}

// Portada editorial sobre lavanda con un aro naranja grande: título a la izquierda y el teléfono a la derecha
export default function ContactCover() {
  return (
    <Cover
      className="ct-cover"
      eyebrow="Contacto"
      lines={['Hablemos de', { text: 'tu proyecto', accent: true }]}
      lead="Escríbenos o llámanos y un asesor te responde con la referencia y el precio."
    >
      <PhoneChat />
    </Cover>
  )
}
