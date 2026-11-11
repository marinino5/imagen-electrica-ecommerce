# Investigación para la fase 2 (pagos, Siigo, hosting, dominio y envíos)

Fecha de la consulta: **11 de noviembre de 2026**. Es una investigación de escritorio: se leyeron resultados de búsqueda y páginas públicas, no se habló con ningún proveedor. Cada dato lleva su fuente y, cuando las fuentes se contradicen o son blogs de terceros, se dice **«por confirmar»**. Antes de firmar con alguien, hay que pedir la cotización oficial por escrito. Nada de esto está construido: sigue siendo fase 2.

## 1. Pasarelas de pago

La tienda necesita cobrar con **PSE** (el medio más usado por empresas colombianas) y tarjeta. Cobrar con PSE tiene además un costo técnico que depende de la pasarela, no del sistema interbancario ([Siigo, guía de PSE](https://www.siigo.com/blog/contabilidad-finanzas/pse-pagos-seguros-en-linea-colombia/)); algunos proveedores cobran un cargo mensual o de implementación (la guía de Siigo menciona montos de hasta ~4 millones de COP por integrar el botón PSE, **por confirmar** con cada pasarela).

| Pasarela | Tarifa que encontré | Confiabilidad del dato | Nota |
|---|---|---|---|
| **Wompi** (Bancolombia) | Tarjeta presencial 1,98 % + IVA ([Cambio Colombia, sep. 2026](https://cambiocolombia.com/peso-a-peso-paso-a-paso/articulo/2026/9/wompi-amplia-los-pagos-sin-contacto-y-baja-al-uno-por-ciento-la-tarifa-de-los-pagos-con-qr)); en línea 2,65 % + $700 + impuestos ([soporte Wompi](https://soporte.wompi.co/hc/es-419/article_attachments/360102376573)); QR 1 % | Contradictoria entre fuentes y fechas. PSE: **no hallé tarifa oficial** | Tiene API y widget; atada al ecosistema Bancolombia. |
| **PayU** | 3,29 % + $300, mínimo $450 para PSE, Nequi y Botón Bancolombia ([PayU, tarifas](https://corporate.payu.com/?p=36826)) | Oficial, pero la misma página mezcla países: **por confirmar** | Antes de IVA. Larga trayectoria en Colombia. |
| **ePayco** | 2,79 %–3,50 % + IVA + $700 según volumen ([Guía de Software](https://www.guiadesoftware.com/blog/mejor-pasarela-pago-colombia)) | Blog de terceros: **por confirmar** | Fácil de integrar; PSE sin cifra confirmada. |
| **Mercado Pago** | 3,29 % + $800 + IVA ([Portafolio, contenido patrocinado](https://www.portafolio.co/contenido-patrocinado/mercado-pago-transforma-la-manera-de-vender-con-sencillez-y-seguridad-582700)); baja con volumen y según los días de liberación | Patrocinado y posiblemente desactualizado: **por confirmar** en su simulador | Buena marca ante el comprador; SDK y checkout propio. |
| **Bold** | ≈ 2,7 % efectivo para un comercio típico ([Guía de Software](https://www.guiadesoftware.com/blog/mejor-pasarela-pago-colombia)) | Estimado de blog: **por confirmar** | Nació en datáfonos; botón de pagos en línea. |

**Mercado Libre** no es una pasarela, es un *marketplace*. Cobra solo por venta, sin cuota por abrir cuenta; la comisión depende de la categoría y del tipo de publicación ([Shopify](https://www.shopify.com/es/blog/como-vender-en-mercado-libre), [documentación para desarrolladores](https://developers.mercadolibre.com.ar/comision-por-vender)). Sirve como **canal adicional** para la línea de venta directa (no para cotizaciones industriales), y su API permite publicar y sincronizar. La tabla oficial de comisiones para Colombia no la encontré: **por confirmar**.

**Qué haría:** empezar con **Wompi o PayU** (ambas con PSE y API documentada), pedir cotización escrita a las dos y comparar el costo total (porcentaje + fijo + mensualidad de integración). Importa más el costo del PSE y los días de desembolso que la tarjeta, porque el cliente industrial paga sobre todo por PSE.

## 2. Siigo Nube (inventario y facturación)

- La API de Siigo Nube permite leer y crear clientes, productos, inventario y facturas de venta, y notas crédito ([Siigo, artículo sobre su API](https://www.siigo.com/blog/tecnologia-innovacion/siigo-api/)).
- Para habilitarla hace falta un plan **Profesional, Emprendedor o Premium** (según ese artículo); las credenciales se generan en la configuración de la cuenta. La documentación está en Apiary.
- Precios: la página de Colombia muestra Nube desde $119.900 al año con descuento a cliente nuevo ([Siigo, precios](https://siigo.com/precios-siigo)); una guía de terceros cita desde $80.000 al mes. **Por confirmar**: el precio regular, si la API tiene costo adicional y los límites de llamadas (no los hallé).
- Pregunta clave para la empresa: **¿qué versión de Siigo usa hoy?** El proyecto dice que el SKU es el código de Siigo; si usan Siigo Nube la sincronización es viable; si usan Siigo Pyme/Clásico (instalado), la API podría no estar disponible.

**Qué haría:** una sincronización de **solo lectura** de stock y precios (Siigo → tienda) cada cierto tiempo con una tarea programada de Laravel, antes de pensar en crear facturas desde la tienda. Eso ya está previsto: por eso el stock del panel es de solo lectura.

## 3. Hosting

Hoy: tienda en Cloudflare Pages, Laravel en Render (Docker), MySQL en Aiven.

| Opción | Costo aproximado | Notas |
|---|---|---|
| **Render** | Web service Starter ~US$7/mes (512 MB); Standard ~US$25/mes (2 GB) ([blogs 2026](https://devtoolpicks.com/blog/railway-vs-render-vs-fly-io-solo-developers-2026), [ServerCompass](https://servercompass.app/blog/render-pricing-is-it-worth-it)) | Las fuentes **se contradicen** sobre si aún existe plan gratuito (con «spin-down»): ver render.com/pricing. Un servicio gratuito que se duerme no sirve para una tienda real. |
| **Railway** | Por uso, ≈ US$20/mes en una carga comparable | La factura varía con el tráfico. |
| **DigitalOcean** | Droplet de US$24 (4 GB); App Platform ≈ US$25/mes | Más control; hay que administrar el servidor. |
| **Aiven MySQL** | Plan gratuito: 1 GB de RAM y 1 GB de disco ([Aiven, plan gratuito](https://aiven.io/docs/products/mysql/concepts/mysql-free-tier)) | Suficiente para la fase académica; para producción real conviene un plan de pago con copias de seguridad garantizadas (**precio por confirmar** en aiven.io/pricing/mysql). |

**Qué haría:** subir Render a un plan **de pago que no se duerma** (~US$7–25/mes) y pasar MySQL a un plan de pago con copias automáticas. El costo total razonable para empezar está en el orden de **US$20–50/mes** (**estimado**, depende de la base de datos elegida).

## 4. Migración al dominio imagenelectrica.com

Pasos (sin tocar el sitio que hoy está publicado en ese dominio hasta tener todo probado):

1. Confirmar **quién es el titular** del dominio y dónde está el DNS (registrador). Sin acceso al panel de DNS no se puede avanzar.
2. Hacer inventario de lo que hoy sirve ese dominio: correos corporativos (registros MX), sitio actual y subdominios. **Nunca** cambiar los MX sin plan: se cortarían los correos.
3. En **Cloudflare Pages**, agregar el dominio personalizado a la tienda y apuntar el DNS (CNAME) según las instrucciones de Cloudflare; el certificado HTTPS es automático.
4. Poner la API en un subdominio (por ejemplo `api.imagenelectrica.com`) y actualizar en Render las variables `FRONTEND_URL` y `CORS_ALLOWED_ORIGINS` y en la tienda `VITE_API_URL`.
5. Si el sitio actual tiene páginas con posicionamiento, mapear cada URL vieja a la nueva y hacer **redirecciones 301** ([guía de migración, Ahrefs](https://ahrefs.com/es/blog/migracion-web/); [checklist, Semrush](https://es.semrush.com/blog/cambio-de-nombre-de-dominio-seo/)).
6. Bajar el TTL del DNS un día antes, hacer el cambio en horario de poco tráfico y revisar la consola de búsqueda después.

**Lo que necesita la empresa:** acceso al registrador, saber quién administra el correo y confirmar si hay un sitio vigente que deba redirigirse.

## 5. Transportadoras

No encontré API pública documentada de **Servientrega, Envía ni Interrapidísimo**; ambos se manejan por cuenta corporativa con su área comercial. La ruta práctica son **agregadores** que integran varias transportadoras (Coordinadora, TCC, Deprisa, 4-72, Interrapidísimo, Servientrega, Envía): [Envíame](https://enviame.io/), [mipaquete](https://www.mipaquete.com/) y [DrEnvío (documentación de cotización)](https://docs.drenvio.com/en/guides/cotizar-envio/). Los precios por envío dependen de peso, volumen y ciudad: **por cotizar**.

Para material eléctrico pesado (motores, reductores) hay que pedir tarifas de **carga**, no de paquetería, y definir quién asume el seguro.

**Qué haría:** al principio, mantener el envío como hoy (valor configurable y coordinación manual) y evaluar un agregador cuando haya volumen.

## 6. Tabla final recomendada

| Tema | Recomendación | Costo estimado | Qué necesita la empresa |
|---|---|---|---|
| Pagos | Wompi o PayU (PSE + tarjeta); pedir cotización a ambas | Variable por venta (≈ 2–3,5 % + fijo, **por confirmar**) más posible cargo de integración | NIT, RUT, cuenta bancaria, cámara de comercio |
| Marketplace | Opcional después: Mercado Libre para venta directa | Comisión por venta, **por confirmar** | Cuenta de vendedor empresa |
| Siigo | Sincronización de solo lectura de stock y precios | Plan con API (**por confirmar**) | Saber qué versión de Siigo usan y pedir credenciales de API |
| Hosting | Render de pago + MySQL de pago con copias | ≈ US$20–50/mes (estimado) | Tarjeta para el pago de la nube |
| Dominio | Cloudflare Pages + subdominio `api.` | Dominio ya pagado; Cloudflare Pages tiene plan gratuito | Acceso al DNS y al correo |
| Envíos | Agregador (Envíame, mipaquete o DrEnvío) cuando haya volumen | Por envío, **por cotizar** | Datos de despacho y política de seguro |

## Limitaciones de esta investigación

- No pude abrir las páginas oficiales de tarifas de Wompi, Bold, ePayco ni Mercado Pago Colombia; las cifras vienen de resultados de búsqueda y blogs, varios contradictorios.
- No hay precios de la API de Siigo, ni de Aiven de pago, ni de transportadoras.
- Todo lo marcado **por confirmar** debe validarse con el proveedor antes de decidir o de cotizarle algo a la empresa.
