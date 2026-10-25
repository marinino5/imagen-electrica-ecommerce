# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtén el token con <code>POST /api/v1/login</code> y envíalo como <code>Bearer {token}</code>. Las rutas marcadas con «requires authentication» lo necesitan.
