import { Boxes, FileText, Headset, ShoppingCart, Truck } from 'lucide-react'
import Eyebrow from '../ui/Eyebrow'
import Reveal from '../ui/Reveal'

// Qué hace la empresa por el cliente (reemplaza a la línea de tiempo hasta que la empresa entregue sus fechas)
const services = [
  { Icon: ShoppingCart, title: 'Venta en línea', text: 'Lo que está en bodega, con precio en pesos e IVA incluido, listo para pedir.' },
  { Icon: FileText, title: 'Cotización sin cuenta', text: 'Para equipos industriales y compras por volumen: envías tu lista y un asesor responde con precio.' },
  { Icon: Boxes, title: 'Pedidos bajo solicitud', text: 'Si la referencia no está en bodega, la conseguimos y te confirmamos tiempo de entrega.' },
  { Icon: Headset, title: 'Asesoría técnica', text: 'Te ayudamos a elegir potencia, tensión y protección para tu motor, tablero o instalación.' },
  { Icon: Truck, title: 'Entrega o recogida', text: 'Recoge en la tienda de Bucaramanga o recibe en tu ciudad con envío nacional.' },
]

export default function Services() {
  return (
    <section className="wrap ab-serv" aria-labelledby="h-servicios">
      <Reveal>
        <Eyebrow>Lo que hacemos</Eyebrow>
        <h2 id="h-servicios" className="h2-sys">Servicios para <em>tu proyecto</em></h2>
      </Reveal>
      <ul className="ab-serv__list">
        {services.map(({ Icon, title, text }, i) => (
          <Reveal as="li" key={title} delay={i * 0.04}>
            <span className="ab-serv__ic" aria-hidden="true"><Icon size={24} strokeWidth={1.6} /></span>
            <div>
              <h3>{title}</h3>
              <p>{text}</p>
            </div>
          </Reveal>
        ))}
      </ul>
    </section>
  )
}
