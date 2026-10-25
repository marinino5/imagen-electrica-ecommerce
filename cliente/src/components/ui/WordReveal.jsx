import { motion } from 'framer-motion'

// Título que entra palabra por palabra: cada palabra sube desde una máscara.
// lines: [{ text, accent? }]; cada línea es un bloque (la de acento va en naranja).
// Con prefers-reduced-motion el texto aparece completo, sin desplazamiento.
export default function WordReveal({ as = 'h1', lines, className = '', delay = 0, stagger = 0.07 }) {
  const Tag = motion[as]
  let n = 0
  return (
    <Tag className={`word-reveal ${className}`} initial="hidden" animate="shown" transition={{ delayChildren: delay, staggerChildren: stagger }}>
      {lines.map((line) => (
        <span key={line.text} className={`word-reveal__line ${line.accent ? 'is-accent' : ''}`}>
          {line.text.split(' ').map((word) => (
            <span key={`${word}-${n++}`} className="word-reveal__mask">
              <motion.span
                className="word-reveal__word"
                variants={{ hidden: { y: '110%' }, shown: { y: 0 } }}
                transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
              >
                {word}
              </motion.span>
            </span>
          ))}
        </span>
      ))}
    </Tag>
  )
}
