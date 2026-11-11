# Boletín: qué hay hoy y cómo automatizar los envíos

## Qué existe hoy

- **Suscripción desde la tienda:** `POST /api/v1/newsletter` guarda el correo con la casilla de consentimiento marcada (`NewsletterService::subscribe`). Sin consentimiento no se guarda.
- **Prueba del consentimiento:** cada fila lleva `consent`, `consented_at`, `consent_version` (versión del texto que aceptó, `config('shop.newsletter_consent_version')`) e `ip`.
- **Panel → Clientes → Suscriptores:** búsqueda, filtro por estado, contadores, **exportar CSV** y **dar de baja**. La baja guarda `unsubscribed_at` y no borra la fila, porque es la prueba de que dio y retiró el permiso. Si la persona se vuelve a suscribir, se reactiva.
- **No se envía ningún correo de campaña desde el sistema.** Hoy el envío es manual: se exporta el CSV y se sube a una herramienta externa.

## Reglas que deben cumplirse (Colombia)

La Ley 1581 de 2012 (protección de datos) exige autorización previa, expresa e informada, y poder retirarla. Por eso:

1. Solo escribir a quien tenga `consent` verdadero y `unsubscribed_at` nulo (el CSV ya marca el estado: usar solo los **activos**).
2. Cada correo debe llevar un **enlace de baja** visible.
3. Cuando alguien se dé de baja en la herramienta externa, reflejarlo en el panel (o importar la lista de bajas) para no volver a escribirle.
4. Si cambia el texto del consentimiento, subir `newsletter_consent_version`; los suscriptores antiguos quedan identificados por la versión que aceptaron.

(Esto es una guía técnica, no asesoría legal: `docs/legal-pendiente.md` recoge lo que falta validar con un abogado.)

## Opciones para automatizar

| Opción | Cómo funciona | Costo (según búsquedas del 11-nov-2026; **por confirmar**) | Cuándo conviene |
|---|---|---|---|
| **Brevo** | La tienda/Laravel crea el contacto en Brevo por API y las campañas se arman en su editor. Cobra por correos enviados, no por contactos. | Plan gratis: unos 300 correos al día (~9.000 al mes) con marca de Brevo; de pago desde ~US$7–9 al mes ([Hackceleration](https://hackceleration.com/es/labs/brevo-precio), [Brevo](https://www.brevo.com/es/blog/precios-email-marketing/)) | **Recomendada para empezar**: lista pequeña, pocos envíos, transaccional incluido. |
| **Mailchimp** | Igual, pero cobra por número de contactos. | Plan gratis muy limitado (las fuentes difieren: 500 contactos / 1.000 envíos); de pago desde ~US$13–20 al mes ([comparativa](https://toolquestor.com/vs/brevo-vs-mailchimp)) | Si el equipo ya lo conoce. |
| **Colas de Laravel + SMTP propio** | Un comando programado envía un correo a cada activo con `Mail` y la cola `database` (ya está configurada). | Solo el costo del servicio SMTP | Mucho trabajo: hay que construir editor, estadísticas, rebotes y baja. **No recomendado** ahora. |

## Plan propuesto (fase 2, no construido)

1. **Elegir Brevo** y crear la cuenta de la empresa (necesita dominio verificado: registros SPF/DKIM en el DNS de `imagenelectrica.com`, ver `docs/investigacion-fase3.md`, sección de dominio).
2. En `NewsletterService::subscribe`, después de guardar, **crear el contacto en Brevo** con una tarea en cola (`dispatch`), de modo que si Brevo falla no se pierda la suscripción.
3. Configurar en Brevo un *webhook* de baja hacia un endpoint de Laravel que llame a `NewsletterService::unsubscribe`, para que panel y herramienta queden sincronizados.
4. Mantener el CSV del panel como respaldo y como prueba del consentimiento.
5. Primer envío sugerido: bienvenida automática y una promoción mensual. Las promociones ya existen en el panel (Catálogo → Promociones); un envío podría tomar la vigente.

## Qué necesita la empresa

Elegir la herramienta, un correo remitente propio (por ejemplo `ventas@imagenelectrica.com`), acceso al DNS del dominio y la política de tratamiento de datos publicada en la tienda.
