# Documentación

Documentación del proyecto:

- `arquitectura.pdf`: documento de arquitectura.
- `bruno/`: colección de Bruno para probar la API (flujo completo del cliente, administrador y casos de error 422, 401 y 403). Ábrela desde Bruno con *Open Collection* y elige el entorno `Local` (API en tu máquina) o `Produccion` (https://imagen-electrica-ecommerce.onrender.com).
- `collection.json` (Postman) y `openapi.yaml` (OpenAPI): documentación de la API generada con Scribe. Se regeneran con `composer docs:api` desde `backend/`; la versión en HTML queda en `backend/public/docs`.

Entrega y revisión (Fase 2):

- `sustentacion.md`: guion de 20 minutos y reglas de negocio con archivo y método exactos.
- `arquitectura-cambios.md`: qué cambió en el modelo y en los diagramas C4 desde `arquitectura.pdf`.
- `revision-productos.md`: una fila por línea (imagen, especificaciones, ficha técnica, cambios) y `correcciones-especificaciones.md` con la fuente de cada corrección.
- `licencias-imagenes.md`: todas las imágenes y videos con archivo, fuente y tipo (`inventario/generar_licencias.py`).
- `inventario/precios-referencia.csv`: precios de referencia de las referencias nuevas con su fuente; `inventario/fotos-para-tomar.md`: referencias sin foto, por estante.
