{{-- Etiquetas comunes del <head>: se incluye en todos los layouts --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title') · {{ config('app.name') }}</title>
<link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
@fonts
@vite(['resources/scss/app.scss', 'resources/js/app.js'])
