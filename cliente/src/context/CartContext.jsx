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
  image_url: p.image_url,
  unit: p.unit,
  unit_label: p.unit_label,
  price: p.price,
  tax_rate: p.tax_rate,
  stock: p.stock,
  sale_mode: p.sale_mode,
  brand: p.brand?.name ?? null,
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

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(lines))
    } catch {
      /* sin almacenamiento: el carrito vive solo en memoria */
    }
  }, [lines])

  const add = useCallback(
    // origin: { rect, image } del botón pulsado, para animar el producto hacia el carrito
    (product, quantity = 1, origin) => {
      const qty = Number(quantity) > 0 ? Number(quantity) : 1
      setLines((current) => {
        const found = current.find((l) => l.product.id === product.id)
        if (found) {
          return current.map((l) =>
            l.product.id === product.id ? { ...l, product: snapshot(product), quantity: l.quantity + qty } : l,
          )
        }
        return [...current, { product: snapshot(product), quantity: qty }]
      })
      const direct = isDirectPurchase(product, qty)
      if (origin) fly(origin.rect, direct ? 'cart' : 'quote', origin.image)
      notify(
        direct ? `«${product.name}» se agregó al carrito` : `«${product.name}» se agregó a tu cotización`,
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
    const purchaseLines = lines.filter((l) => isDirectPurchase(l.product, l.quantity))
    const quoteLines = lines.filter((l) => !isDirectPurchase(l.product, l.quantity))
    const purchaseTotal = purchaseLines.reduce((sum, l) => sum + l.product.price * l.quantity, 0)
    return {
      lines,
      purchaseLines,
      quoteLines,
      purchaseCount: purchaseLines.length,
      quoteCount: quoteLines.length,
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
