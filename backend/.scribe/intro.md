# Introduction

API REST del e-commerce B2B de Imagen Eléctrica: catálogo público, cuenta del cliente, pedidos y cotizaciones, y gestión de productos para administradores.

<aside>
    <strong>Base URL</strong>: <code>https://imagen-electrica-ecommerce.onrender.com</code>
</aside>

    Todas las rutas están bajo `/api/v1` y responden JSON en español.

    **Autenticación.** El catálogo (categorías, marcas y productos) es público. Para lo demás, inicia sesión en `POST /api/v1/login` (o regístrate en `POST /api/v1/register`) y envía el token recibido en el encabezado `Authorization: Bearer {token}`. Los clientes usan sus propias rutas; las de `/api/v1/admin/*` exigen el token de un administrador.

    **Códigos de respuesta.** `200` correcto, `201` creado, `204` sin contenido, `401` sin token o token inválido, `403` sin permiso para esa ruta, `404` no existe, `422` datos inválidos o regla de negocio incumplida (el cuerpo trae `message` y, si aplica, `errors`) y `429` demasiados intentos (login y registro permiten 5 por minuto).

    **Precios.** Los precios incluyen IVA. Un producto con `price` en `null` solo se vende por cotización.

