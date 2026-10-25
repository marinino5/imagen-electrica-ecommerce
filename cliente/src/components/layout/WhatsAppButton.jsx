import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import '../../styles/whatsapp.css'

export default function WhatsAppButton() {
  return (
    <a className="whatsapp" href={site.whatsappUrl} target="_blank" rel="noreferrer" aria-label="Escríbenos por WhatsApp">
      <MessageCircle size={26} aria-hidden="true" />
      <span className="whatsapp__label">¿Necesitas ayuda?</span>
    </a>
  )
}
