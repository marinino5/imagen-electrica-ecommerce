# Mapeo de la compra con factura electrónica → Siigo Nube

Aún **no hay integración con Siigo** (fase 2). Esta tabla deja claro qué columna de la base corresponde a cada campo que pide Siigo para crear al tercero (cliente) y la factura, para que la integración sea un mapeo y no una migración de datos.

**Fuente verificada:** documentación oficial de la API de Siigo Nube, «Crear cliente» (`POST /v1/customers`), <https://developers.siigo.com/docs/siigoapi/customer/1-create-customer/> (consultada el 11-nov-2026). Los nombres de campo y los códigos de abajo salen de esa página. Ella misma tiene dos incoherencias que hay que tener presentes al integrar: `person_type` figura como `person`/`company` en la tabla y como `'Person'` en el ejemplo (guardamos `Person` / `Company`, como el ejemplo); y `check_digit` figura como opcional y «calculado automáticamente» (nosotros lo calculamos y validamos, y lo guardamos aparte).

## Quién es el tercero → `POST /v1/customers`

La factura es **opcional**: solo se piden estos datos si la persona activa «Necesito factura a nombre de una persona o empresa» (`orders.needs_invoice`). Sin factura no se guarda ningún documento.

| Campo de Siigo | Obligatorio en Siigo | Columna en `orders` (copia por pedido) | Columna en `customers` (perfil) | Valor / regla |
|---|---|---|---|---|
| `type` | no (por defecto `Customer`) | — | — | siempre `Customer` |
| `person_type` | sí | `billing_person_type` | `type` (`natural` / `company`) | `Person` o `Company` |
| `id_type` | sí | `billing_id_type` | `id_type` | `13` cédula de ciudadanía · `31` NIT · `22` cédula de extranjería · `41` pasaporte (enum `App\Enums\IdType`) |
| `identification` | sí | `billing_identification` | `nit` (NIT con `-DV`, o el documento) | CC y NIT: solo dígitos, 3 a 13; CE y pasaporte: letras y números, 1 a 20 |
| `check_digit` | no | `billing_check_digit` | `check_digit` | solo NIT; se calcula (módulo 11, DIAN) y se valida: si no coincide, 422 con el dígito correcto |
| `name` | sí | persona: `billing_first_name` + `billing_last_name` · empresa: `company_name` | `first_name` + `last_name` · `company_name` | persona: lista de 2 (nombres, apellidos); empresa: un solo valor (razón social); máx. 100 |
| `commercial_name` | no | `billing_commercial_name` | `commercial_name` | máx. 100 |
| `branch_office` | no | — | — | `0` |
| `active` | no | — | — | `true` |
| `vat_responsible` | no | `billing_vat_responsible` | `vat_responsible` | booleano («Responsable de IVA») |
| `fiscal_responsibilities[].code` | sí | `billing_fiscal_responsibilities` (JSON, lista de códigos) | `fiscal_responsibilities` | `R-99-PN` no aplica/otros (por defecto) · `O-13` gran contribuyente · `O-15` autorretenedor · `O-23` agente de retención de IVA · `O-47` régimen simple (enum `App\Enums\FiscalCode`) |
| `address.address` | sí | `billing_address` | `address` | máx. 256 |
| `address.city.country_code` | sí | `billing_country_code` | — | `CO` |
| `address.city.state_code` | sí | `billing_state_code` (+ `billing_department`, el nombre) | `state_code` (+ `department`) | código DANE del departamento, 2 dígitos |
| `address.city.city_code` | sí | `billing_city_code` (el nombre va en `orders.city`) | `city_code` (+ `city`) | código DANE del municipio, 5 dígitos |
| `address.postal_code` | no | `billing_postal_code` | `postal_code` | alfanumérico sin espacios, máx. 10 |
| `phones[].indicative` / `number` | no | indicativo `57`; número en `orders.phone` | `phone` | celular colombiano de 10 dígitos que empieza por 3 |
| `contacts[].first_name` | sí | `billing_contact_first_name` | `first_name` / `contact_name` | persona: ella misma; empresa: el contacto (por defecto, quien compra) |
| `contacts[].last_name` | no | `billing_contact_last_name` | `last_name` | |
| `contacts[].email` | no | `billing_email` (si no se da, el correo de contacto) | `billing_email` | |
| `comments` | no | `orders.notes` | — | |

Los códigos de departamento y municipio son los **DANE (DIVIPOLA)**: la lista oficial está en `backend/database/data/divipola.json` (33 departamentos, 1.122 municipios; fuente: datos.gov.co) y en `cliente/src/data/divipola.json` (copia idéntica, verificada por una prueba). Siigo los pide en este mismo formato (por ejemplo `CO` / `11` / `11001` para Bogotá).

## Pedido → factura de venta

| Dato | Columna | Campo de Siigo |
|---|---|---|
| Número del pedido | `orders.code` (`PED-0001`) | `observations` (referencia externa) |
| Líneas | `order_items.sku` (= código del producto en Siigo), `quantity`, `unit_price` (con IVA), `tax_rate` | `items[].code`, `quantity`, `price` (Siigo espera el valor sin IVA: base = precio / (1 + tasa)), `taxes` |
| Totales | `subtotal` (base), `tax`, `shipping`, `total` | `payments[].value` |

## Contacto y entrega (no van en la factura)

| Dato | Columna |
|---|---|
| Nombre, celular y correo de quien compra | `contact_name`, `phone`, `email` |
| Entrega | `delivery_method` (`pickup`, `local`, `national`) |
| Departamento y municipio de entrega | `delivery_state_code`, `delivery_department`, `delivery_city_code`, `delivery_city` |
| Barrio, vía, número, complemento | `delivery_neighborhood`, `delivery_road`, `delivery_number`, `delivery_complement` |
| Código postal e indicaciones | `delivery_postal_code`, `delivery_notes` |
| Línea de texto lista para etiquetas | `address` (se compone al comprar) |

El envío es **«local»** si el municipio es Bucaramanga, Floridablanca, Girón o Piedecuesta y **«nacional»** en el resto; el valor sale de una tabla de zonas configurable (`config/shop.php` → `shipping.zones`): local $15.000, resto de Santander $25.000, resto del país $35.000, gratis desde la compra mínima. `GET /api/v1/shipping/quote?city_code=…` lo calcula.

## Consentimiento y origen

| Dato | Columna |
|---|---|
| Autorización de tratamiento de datos | `privacy_accepted_at` (fecha y hora) y `privacy_version` (versión del texto) |
| Compra sin cuenta | `is_guest` (y `customer_id` nulo) |
