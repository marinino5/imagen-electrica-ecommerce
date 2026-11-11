<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Restablece tu contraseña</title>
</head>
<body style="margin:0;padding:0;background:#f3f1fa;font-family:Arial,Helvetica,sans-serif;color:#1C1630;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f1fa;padding:32px 12px;">
  <tr><td align="center">
    <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
      <tr><td style="background:#4A3580;padding:24px 32px;">
        <span style="font-size:24px;font-weight:700;color:#ffffff;letter-spacing:.5px;">Imagen <span style="color:#ffb27a;">Eléctrica</span></span><br>
        <span style="font-size:13px;color:#d6d0ea;">Soluciones Industriales</span>
      </td></tr>
      <tr><td style="height:4px;background:#f26a00;font-size:0;line-height:0;">&nbsp;</td></tr>
      <tr><td style="padding:32px;">
        <h1 style="margin:0 0 12px;font-size:22px;line-height:1.2;color:#1C1630;">Restablece tu contraseña</h1>
        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#1C1630;">Hola {{ $name }}, recibimos una solicitud para cambiar la contraseña de tu cuenta en la tienda de Imagen Eléctrica.</p>
        <p style="margin:0 0 24px;"><a href="{{ $url }}" style="display:inline-block;background:#f26a00;color:#1C1630;font-weight:700;font-size:15px;text-decoration:none;padding:14px 28px;border-radius:999px;">Crear una contraseña nueva</a></p>
        <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#5d5672;">El enlace funciona una sola vez y vence en {{ $minutes }} minutos. Si no fuiste tú, ignora este correo: tu contraseña no cambia.</p>
        <p style="margin:0;font-size:12px;line-height:1.6;color:#5d5672;word-break:break-all;">Si el botón no abre, copia este enlace en tu navegador:<br>{{ $url }}</p>
      </td></tr>
      <tr><td style="background:#faf9fd;padding:16px 32px;font-size:12px;color:#5d5672;">Imagen Eléctrica · Cra 18 No 24-32, Bucaramanga, Santander</td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
