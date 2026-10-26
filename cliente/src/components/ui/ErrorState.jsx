import { RefreshCw, WifiOff, AlertTriangle } from 'lucide-react'

// Error visible y accionable: sin conexión, tiempo agotado u otro fallo
export default function ErrorState({ error, onRetry, title }) {
  const offline = error?.kind === 'network' || error?.kind === 'timeout'
  const Icon = offline ? WifiOff : AlertTriangle
  return (
    <div className="state" role="alert">
      <Icon size={36} aria-hidden="true" />
      <h3>{title ?? (offline ? 'No pudimos conectarnos' : 'Algo salió mal')}</h3>
      <p>{error?.message ?? 'Inténtalo de nuevo en unos segundos.'}</p>
      {onRetry && (
        <button type="button" className="btn btn--primary" onClick={onRetry}>
          <RefreshCw size={16} aria-hidden="true" /> Reintentar
        </button>
      )}
    </div>
  )
}
