import { MessageCircle, RefreshCw, ServerCrash, WifiOff } from 'lucide-react'
import { site } from '../../config/site'
import '../../styles/product-card.css'

// Error visible y accionable. Distingue tres casos que antes se confundían con "sin resultados":
// - sin conexión con el servidor (red caída o tiempo agotado),
// - el servidor respondió pero no tiene ese contenido (404) o falló (5xx),
// - cualquier otro fallo.
// "No hay resultados" es otra cosa y la muestra EmptyState.
function describe(error) {
  if (error?.kind === 'network' || error?.kind === 'timeout') {
    return {
      Icon: WifiOff,
      title: 'Sin conexión con el servidor',
      text: 'No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.',
    }
  }
  if (error?.kind === 'not_found') {
    return {
      Icon: ServerCrash,
      title: 'El servidor no tiene este contenido',
      text: 'El catálogo no responde en este momento. Inténtalo en unos minutos o escríbenos por WhatsApp.',
    }
  }
  if (error?.kind === 'server') {
    return {
      Icon: ServerCrash,
      title: 'El servidor tuvo un problema',
      text: 'Ya estamos al tanto. Inténtalo de nuevo en unos segundos.',
    }
  }
  return { Icon: ServerCrash, title: 'Algo salió mal', text: error?.message ?? 'Inténtalo de nuevo en unos segundos.' }
}

export default function ErrorState({ error, onRetry, title }) {
  const { Icon, title: defaultTitle, text } = describe(error)
  return (
    <div className="state" role="alert">
      <Icon size={36} aria-hidden="true" />
      <h3>{title ?? defaultTitle}</h3>
      <p>{text}</p>
      {import.meta.env.DEV && error?.status ? <p><small>Desarrollo: el servidor respondió HTTP {error.status}.</small></p> : null}
      <div className="state__actions">
        {onRetry && (
          <button type="button" className="btn btn--primary" onClick={onRetry}>
            <RefreshCw size={16} aria-hidden="true" /> Reintentar
          </button>
        )}
        {error?.kind && error.kind !== 'validation' && (
          <a className="btn btn--ghost" href={site.whatsappUrl} target="_blank" rel="noreferrer">
            <MessageCircle size={16} aria-hidden="true" /> Escríbenos por WhatsApp
          </a>
        )}
      </div>
    </div>
  )
}
