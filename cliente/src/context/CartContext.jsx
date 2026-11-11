import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { isDirectPurchase } from '../lib/catalog'
import { useToast } from './ToastContext'

const CartContext = createContext(null)
const STORAGE_KEY = 'ie.cart'

// Se guarda solo lo necesario del producto para mostrar el carrito sin volver a pedirlo
const snapshot = (p) => ({
  id: p.id,
  sku: p.sku,
  name: p.name,
  slug: p.slug,
  line_slug: p.line_slug ?? null,
  line_name: p.line_name ?? null,
  label: p.label ?? null,
  image_url: p.image_url,
  unit: p.unit,
  unit_label: p.unit_label,
  price: p.price,
  tax_rate: p.tax_rate,
  in_stock: p.in_stock ?? true,
  stock_limit: p.stock_limit ?? null,
  sale_mode: p.sale_mode,
  brand: p.brand?.name ?? p.brand ?? null,
  category: p.category ?? null,
})

function load() {
  try {
    const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]')
    return Array.isArray(raw) ? raw.filter((l) => l?.product?.id && l.quantity > 0) : []
  } catch {
    return []
  }
}

export function CartProvider({ children }) {
  const { notify, fly } = useToast()
  const [lines, setLines] = useState(load)
  // Sube cada vez que se agrega algo: el contador del carrito lo usa para dar un pulso de 300 ms
  const [pulse, setPulse] = useState(0)

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(lines))
    } catch {
      /* sin almacenamiento: el carrito vive solo en memoria */
    }
  }, [lines])

  const add = useCallback(
    // origin: { rect, image } del botón pulsado, para animar el producto hacia el carrito.
    // options.asQuote: la persona pidió cotizar (por ejemplo "Cotizar volumen"), aunque se pueda comprar en línea.
    (product, quantity = 1, origin, options = {}) => {
      const asQuote = options.asQuote === true
      const qty = Number(quantity) > 0 ? Number(quantity) : 1
      setLines((current) => {
        const found = current.find((l) => l.product.id === product.id)
        if (found) {
          return current.map((l) =>
            l.product.id === product.id ? { ...l, product: snapshot(product), quantity: l.quantity + qty, asQuote } : l,
          )
        }
        return [...current, { product: snapshot(product), quantity: qty, asQuote }]
      })
      const direct = !asQuote && isDirectPurchase(product, qty)
      if (origin) fly(origin.rect, direct ? 'cart' : 'quote', origin.image)
      setPulse((n) => n + 1)
      // «Agregado: W22 5 HP · Ver carrito»: nombre de la línea y referencia, sin repetir el SKU
      const name = product.line_name && product.label ? `${product.line_name} ${product.label}` : product.name
      notify(
        direct ? `Agregado: ${name}` : `Agregado a tu cotización: ${name}`,
        { to: '/carrito', label: direct ? 'Ver carrito' : 'Ver cotización' },
      )
    },
    [notify, fly],
  )

  const setQuantity = useCallback((productId, quantity) => {
    setLines((current) =>
      current.map((l) => (l.product.id === productId ? { ...l, quantity: Math.max(Number(quantity) || 1, 1) } : l)),
    )
  }, [])

  const remove = useCallback((productId) => {
    setLines((current) => current.filter((l) => l.product.id !== productId))
  }, [])

  const clear = useCallback(() => setLines([]), [])

  const value = useMemo(() => {
    // Misma clasificación que hace el backend al recibir la solicitud
    const isPurchase = (l) => !l.asQuote && isDirectPurchase(l.product, l.quantity)
    const purchaseLines = lines.filter(isPurchase)
    const quoteLines = lines.filter((l) => !isPurchase(l))
    const purchaseTotal = purchaseLines.reduce((sum, l) => sum + l.product.price * l.quantity, 0)
    return {
      lines,
      purchaseLines,
      quoteLines,
      purchaseCount: purchaseLines.length,
      quoteCount: quoteLines.length,
      pulse,
      purchaseTotal, // precios con IVA incluido
      add,
      setQuantity,
      remove,
      clear,
    }
  }, [lines, add, setQuantity, remove, clear])

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>
}

export function useCart() {
  const ctx = useContext(CartContext)
  if (!ctx) throw new Error('useCart debe usarse dentro de CartProvider')
  return ctx
}
