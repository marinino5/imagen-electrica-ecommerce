import { useEffect, useRef, useState } from 'react'
import { ChevronDown, Phone } from 'lucide-react'
import { site } from '../../config/site'
import { useMediaQuery } from '../../hooks/useMediaQuery'

// Botón «Llamar». En celular llama de una vez a la línea principal; en escritorio (donde no se llama con un clic) se
// despliega una lista con los tres números. Cierra con Escape, con un clic afuera o al elegir un número.
export default function CallPill() {
  const desktop = useMediaQuery('(min-width: 768px)')
  const [open, setOpen] = useState(false)
  const box = useRef(null)

  useEffect(() => {
    if (!open) return undefined
    const onDown = (e) => !box.current?.contains(e.target) && setOpen(false)
    const onKey = (e) => e.key === 'Escape' && (setOpen(false), box.current?.querySelector('button')?.focus())
    document.addEventListener('pointerdown', onDown)
    document.addEventListener('keydown', onKey)
    return () => {
      document.removeEventListener('pointerdown', onDown)
      document.removeEventListener('keydown', onKey)
    }
  }, [open])

  if (!desktop) {
    return (
      <a className="ct-pill ct-pill--call" href={site.mainContact.href}>
        <Phone size={18} aria-hidden="true" /> Llamar
      </a>
    )
  }
  return (
    <div className="ct-call" ref={box}>
      <button type="button" className="ct-pill ct-pill--call" aria-expanded={open} aria-controls="ct-call-list" onClick={() => setOpen((o) => !o)}>
        <Phone size={18} aria-hidden="true" /> Llamar <ChevronDown size={16} aria-hidden="true" className="ct-call__chev" />
      </button>
      <ul id="ct-call-list" className={`ct-call__list ${open ? 'is-open' : ''}`} inert={!open}>
        {site.contacts.map((c) => (
          <li key={c.href}>
            <a href={c.href} onClick={() => setOpen(false)}>{c.label}{c.main && <small>Línea principal</small>}</a>
          </li>
        ))}
      </ul>
    </div>
  )
}
