import { useEffect, useId, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Loader2, Search } from 'lucide-react'
import { catalogApi } from '../../api'
import { useCategories } from '../../hooks/useCategories'
import { formatCOP } from '../../lib/format'
import '../../styles/searchbox.css'

// Buscador con autocompletado: consulta GET /products?q= mientras se escribe
export default function SearchBox({ className = '' }) {
  const navigate = useNavigate()
  const listId = useId()
  const wrapRef = useRef(null)
  const { data: categories } = useCategories()

  const [value, setValue] = useState('')
  const [open, setOpen] = useState(false)
  const [results, setResults] = useState([])
  const [loading, setLoading] = useState(false)
  const [failed, setFailed] = useState(false)
  const [active, setActive] = useState(-1)

  const term = value.trim()
  const canSearch = term.length >= 2

  // Espera 250 ms después de la última tecla y cancela la consulta anterior
  useEffect(() => {
    if (!canSearch) {
      setResults([])
      setLoading(false)
      setFailed(false)
      return
    }
    setLoading(true)
    const controller = new AbortController()
    const timer = setTimeout(() => {
      catalogApi
        .products({ q: term, per_page: 5 }, controller.signal)
        .then((res) => {
          setResults(res.data)
          setFailed(false)
          setActive(-1)
        })
        .catch(() => {
          if (!controller.signal.aborted) setFailed(true)
        })
        .finally(() => !controller.signal.aborted && setLoading(false))
    }, 250)
    return () => {
      clearTimeout(timer)
      controller.abort()
    }
  }, [term, canSearch])

  useEffect(() => {
    const onClick = (e) => !wrapRef.current?.contains(e.target) && setOpen(false)
    document.addEventListener('pointerdown', onClick)
    return () => document.removeEventListener('pointerdown', onClick)
  }, [])

  const goSearch = (query) => {
    setOpen(false)
    navigate(`/productos?q=${encodeURIComponent(query)}`)
  }

  const quick = categories.roots.slice(0, 6)
  const items = canSearch ? results : []

  const onKeyDown = (e) => {
    if (e.key === 'Escape') return setOpen(false)
    if (e.key === 'ArrowDown') {
      e.preventDefault()
      setOpen(true)
      setActive((i) => Math.min(i + 1, items.length - 1))
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      setActive((i) => Math.max(i - 1, -1))
    }
  }

  const onSubmit = (e) => {
    e.preventDefault()
    if (active >= 0 && items[active]) return goSearch(items[active].sku)
    if (term) goSearch(term)
  }

  return (
    <div className={`searchbox ${className}`} ref={wrapRef}>
      <form role="search" onSubmit={onSubmit} className="searchbox__form">
        <Search size={22} aria-hidden="true" className="searchbox__icon" />
        <input
          type="search"
          role="combobox"
          aria-label="Buscar productos por nombre o referencia"
          aria-expanded={open}
          aria-controls={listId}
          aria-autocomplete="list"
          aria-activedescendant={active >= 0 ? `${listId}-${active}` : undefined}
          placeholder="Busca por nombre o referencia: motor 5 HP, variador, cable 10 AWG…"
          value={value}
          onChange={(e) => {
            setValue(e.target.value)
            setOpen(true)
          }}
          onFocus={() => setOpen(true)}
          onKeyDown={onKeyDown}
          autoComplete="off"
        />
        {loading && <Loader2 size={20} className="searchbox__spin" aria-hidden="true" />}
        <button type="submit" className="btn btn--accent">Buscar</button>
      </form>

      {open && (
        <div className="searchbox__panel" id={listId} role="listbox" aria-label="Sugerencias">
          {!canSearch && (
            <div className="searchbox__quick">
              <span>Búsquedas rápidas:</span>
              {quick.map((c) => (
                <button key={c.id} type="button" onClick={() => navigate(`/productos?category=${c.slug}`)}>
                  {c.name}
                </button>
              ))}
            </div>
          )}
          {canSearch && failed && <p className="searchbox__msg">No pudimos buscar ahora. Presiona Buscar para intentarlo de nuevo.</p>}
          {canSearch && !failed && !loading && items.length === 0 && (
            <p className="searchbox__msg">Sin coincidencias para «{term}». Pídenos la referencia y te la cotizamos.</p>
          )}
          {items.map((p, i) => (
            <button
              key={p.id}
              id={`${listId}-${i}`}
              type="button"
              role="option"
              aria-selected={i === active}
              className={`searchbox__item ${i === active ? 'is-active' : ''}`}
              onMouseEnter={() => setActive(i)}
              onClick={() => goSearch(p.sku)}
            >
              <span className="searchbox__name">{p.name}</span>
              <span className="searchbox__sub">
                {p.brand?.name} · Ref. {p.sku}
              </span>
              <span className="searchbox__price">{p.price === null ? 'Cotizar' : formatCOP(p.price)}</span>
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
