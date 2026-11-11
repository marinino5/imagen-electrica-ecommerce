# Mapeo de la compra con facturación electrónica → Siigo

Aún **no hay integración con Siigo** (fase 2). Esta tabla deja claro qué columna de la base de datos corresponde a cada dato que Siigo pide para crear al tercero (cliente) y la factura de venta, para que la integración sea un mapeo y no una migración de datos.

> Los nombres de campos de Siigo salen de su documentación pública de la API v1 (Apiary) tal como se recuerdan al escribir esto; **hay que validarlos contra la documentación vigente y con una cuenta de pruebas antes de programar**. Lo que sí es firme es el origen de cada dato en nuestra base.

## Quién compra → tercero (cliente)

| Dato | Columna en `orders` (copia por pedido) | Columna en `customers` (perfil) | Campo de Siigo (a confirmar) |
|---|---|---|---|
| Tipo de persona | `customer_type` (`natural` / `company`) | `type` | `person_type` (`Person` / `Company`) |
| Tipo de documento | `billing_doc_type` (`CC`, `CE`, `PP`, `NIT`) | `doc_type` | `id_type` (código DIAN: 13 CC, 22 CE, 41 PP, 31 NIT; `DocumentType::dianCode()`) |
| Número de documento | `billing_doc_number` (solo dígitos; NIT sin DV) | `nit` (NIT con `-DV`, o la cédula) | `identification` |
| Dígito de verificación | `billing_doc_dv` | `doc_dv` | `check_digit` |
| Nombres / apellidos (natural) | `billing_first_name`, `billing_last_name` | `first_name`, `last_name` | `name` (lista: nombres, apellidos) |
| Razón social (empresa) | `company_name` | `company_name` | `name` (un elemento) |
| Responsabilidad fiscal | `fiscal_responsibility` (`vat_responsible`, `not_vat_responsible`, `large_taxpayer`, `self_withholder`, `simple_regime`) | `fiscal_responsibility` | `vat_responsible` (booleano) y `fiscal_responsibilities` (códigos; ver nota) |
| Correo de facturación electrónica | `billing_email` | `billing_email` | `contacts[].email` |
| Persona de contacto (empresa) | `contact_name` | `contact_name` | `contacts[]` (nombre) |
| Celular | `phone` | `phone` | `phones[].number` (indicativo 57) |
| Dirección | `billing_address` | `address` | `address.address` |
| Departamento | `billing_department_code` (DANE, 2 dígitos) + `billing_department` | `department_code`, `department` | `address.city.state_code` |
| Municipio | `billing_city_code` (DANE, 5 dígitos) + `city` | `city_code`, `city` | `address.city.city_code` |

Los códigos de departamento y municipio son los **DANE (DIVIPOLA)**: la lista oficial está en `backend/database/data/divipola.json` (33 departamentos, 1.122 municipios, fuente: datos.gov.co) y los expone la API en `GET /api/v1/locations/departments` y `/locations/departments/{code}/municipalities`.

**Nota sobre la responsabilidad fiscal.** El enum guarda el valor funcional y `FiscalResponsibility::dianCode()` devuelve el número de la casilla 53 del RUT (13 gran contribuyente, 15 autorretenedor, 47 régimen simple, 48 responsable de IVA, 49 no responsable de IVA). Siigo recibe sus propios códigos (por ejemplo `O-13`, `O-15`, `O-47`, `R-99-PN`) y el IVA como booleano: al integrar hay que armar esa conversión en un solo lugar.

## Pedido → factura de venta

| Dato | Columna | Campo de Siigo (a confirmar) |
|---|---|---|
| Número del pedido | `orders.code` (`PED-0001`) | `observations` / referencia externa |
| Líneas | `order_items.sku` (= código de producto en Siigo), `quantity`, `unit_price` (con IVA), `tax_rate` | `items[].code`, `quantity`, `price` (Siigo espera el precio sin IVA: base = precio / (1 + tasa)), `taxes` |
| Totales | `subtotal` (base), `tax`, `shipping`, `total` | `payments[].value` |
| Entrega | `delivery_method` | — (no va en la factura) |

## Dirección de entrega (no va en la factura; sirve para el envío)

| Dato | Columna |
|---|---|
| Departamento | `delivery_department_code`, `delivery_department` |
| Municipio | `delivery_city_code`, `delivery_city` |
| Barrio | `delivery_neighborhood` |
| Vía / número / complemento | `delivery_road`, `delivery_number`, `delivery_complement` |
| Código postal (opcional) | `delivery_postal_code` |
| Indicaciones | `delivery_notes` |
| «Igual a la de facturación» | `delivery_same_as_billing` |
| Línea de texto lista para etiquetas | `address` (se compone al comprar) |

Las columnas separadas permiten calcular el envío por municipio y entregar los datos a una transportadora sin volver a partir una dirección escrita en un solo campo (`docs/investigacion-fase3.md`, sección de transportadoras).

## Consentimiento y origen

| Dato | Columna |
|---|---|
| Autorización de tratamiento de datos | `privacy_accepted_at` (fecha y hora) y `privacy_version` (versión del texto) |
| Compra sin cuenta | `is_guest` (y `customer_id` nulo) |

## Reglas de validación que ya cumple el dato

- NIT: solo dígitos (5 a 12) y el **dígito de verificación se calcula y valida** en el servidor (`App\Support\Nit`, algoritmo de la DIAN, módulo 11 con los pesos 3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71); si no coincide, responde 422 con el dígito correcto.
- Celular colombiano de 10 dígitos que empieza por 3.
- El municipio debe pertenecer al departamento elegido.
- Correo con formato válido; se guarda en minúsculas.
