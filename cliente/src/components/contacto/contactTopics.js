import { FileText, HelpCircle, PackageSearch, Settings2, Truck } from 'lucide-react'

const lines = (...rows) => rows.filter(Boolean).join('\n')

// Cada motivo cambia los campos y la plantilla del mensaje. `required` marca lo mínimo para poder armar el mensaje.
export const topics = [
  {
    id: 'quote',
    label: 'Cotizar un proyecto',
    icon: FileText,
    fields: [
      {
        name: 'application',
        label: 'Aplicación',
        type: 'select',
        options: ['Bombeo', 'Ventilación y extracción', 'Bandas transportadoras', 'Tableros y automatización', 'Iluminación industrial', 'Otra'],
      },
      { name: 'items', label: 'Productos o referencias de interés', placeholder: 'Ej.: motor trifásico, variador de frecuencia…', required: true },
      { name: 'when', label: '¿Para cuándo lo necesitas? (opcional)', placeholder: 'Ej.: esta semana, en un mes…' },
    ],
    template: (f) =>
      lines(
        `Hola, quiero cotizar un proyecto${f.application ? ` de ${f.application.toLowerCase()}` : ''}.`,
        f.items && `Necesito: ${f.items}.`,
        f.when && `Lo necesito para: ${f.when}.`,
      ),
  },
  {
    id: 'stock',
    label: 'Consultar disponibilidad',
    icon: PackageSearch,
    fields: [
      { name: 'product', label: 'Referencia o nombre del producto', placeholder: 'Ej.: referencia o nombre', required: true },
      { name: 'quantity', label: 'Cantidad (opcional)', placeholder: 'Ej.: 2 unidades, 100 metros…' },
    ],
    template: (f) =>
      lines(
        `Hola, quiero saber si tienen disponible ${f.product || 'un producto'}.`,
        f.quantity && `Cantidad: ${f.quantity}.`,
      ),
  },
  {
    id: 'advice',
    label: 'Asesoría técnica',
    icon: Settings2,
    fields: [
      { name: 'subject', label: '¿Sobre qué equipo o aplicación?', placeholder: 'Ej.: motor de una bomba, tablero de control…', required: true },
      { name: 'specs', label: 'Datos técnicos que conozcas (opcional)', type: 'textarea', placeholder: 'Potencia, voltaje, corriente, marca…' },
    ],
    template: (f) =>
      lines(
        `Hola, necesito asesoría técnica sobre ${f.subject || 'un equipo'}.`,
        f.specs && `Datos técnicos: ${f.specs}`,
      ),
  },
  {
    id: 'order',
    label: 'Estado de mi pedido',
    icon: Truck,
    fields: [{ name: 'code', label: 'Código de tu pedido o cotización', placeholder: 'Ej.: PED-0001 o COT-0001', required: true }],
    template: (f) => `Hola, quiero consultar el estado de mi solicitud ${f.code || ''}.`.replace(' .', '.'),
  },
  {
    id: 'other',
    label: 'Otro',
    icon: HelpCircle,
    fields: [],
    template: () => 'Hola, ',
  },
]
