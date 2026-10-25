# Documentación

Documentación del proyecto:

- `arquitectura.pdf`: documento de arquitectura.
- `bruno/`: colección de Bruno para probar la API (flujo completo del cliente, administrador y casos de error 422, 401 y 403). Ábrela desde Bruno con *Open Collection* y elige el entorno `Local` (API en tu máquina) o `Produccion` (https://imagen-electrica-ecommerce.onrender.com).
- `collection.json` (Postman) y `openapi.yaml` (OpenAPI): documentación de la API generada con Scribe. Se regeneran con `composer docs:api` desde `backend/`; la versión en HTML queda en `backend/public/docs`.
