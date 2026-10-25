<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @section('title', 'Iniciar sesión')
    @include('layouts.head')
</head>
<body>
    <div class="login-page row g-0">
        {{-- Izquierda: la marca. Solo en pantallas grandes --}}
        <aside class="login-hero col-lg-6 col-xl-7 d-none d-lg-flex" aria-label="Imagen Eléctrica">
            <img src="{{ asset('images/local.jpg') }}" alt="" class="login-hero-photo">
            <div class="login-hero-overlay"></div>
            <div class="login-hero-dots"></div>
            @include('partials.brand-arc', ['class' => 'login-hero-arc'])

            <div class="login-hero-content">
                <img src="{{ asset('images/logo-blanco.png') }}" alt="Logo de Imagen Eléctrica" class="login-hero-logo animate-in">

                <h1 class="login-hero-title animate-in" style="--delay: 80ms">
                    Soluciones eléctricas
                    <span>para la industria</span>
                </h1>
                <p class="login-hero-lead animate-in" style="--delay: 160ms">
                    Empresa colombiana especializada en soluciones eléctricas y de automatización. Proveemos equipos de alta eficiencia energética y acompañamiento técnico para llevar tu producción al siguiente nivel.
                </p>

                <div class="login-highlights animate-in" style="--delay: 240ms">
                    <div class="years-badge">
                        <strong>25+</strong>
                        <span>años</span>
                    </div>
                    <div>
                        <p class="login-highlights-title mb-1">Experiencia y calidad</p>
                        <p class="login-highlights-text mb-0"><strong>+1.000</strong> referencias en el catálogo</p>
                    </div>
                </div>
            </div>

            @if ($brands->isNotEmpty())
                <div class="brand-marquee" aria-label="Marcas que distribuimos">
                    <div class="brand-marquee-track">
                        {{-- La lista se repite para que el desplazamiento no tenga cortes --}}
                        @foreach ([false, true] as $duplicate)
                            <ul class="brand-marquee-list" @if ($duplicate) aria-hidden="true" @endif>
                                @foreach ($brands as $brand)
                                    <li>{{ $brand }}</li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>

        {{-- Derecha: formulario --}}
        <main class="login-form-side col-lg-6 col-xl-5">
            <div class="login-form-wrap">
                <div class="text-center d-lg-none mb-4 animate-in">
                    <img src="{{ asset('images/logo.png') }}" alt="Imagen Eléctrica" class="login-mobile-logo">
                </div>

                <div class="animate-in" style="--delay: 100ms">
                    <h2 class="login-heading mb-1">Ingresa al <span>panel</span></h2>
                    <p class="text-body-secondary mb-4">Usa tu correo y contraseña del equipo de Imagen Eléctrica.</p>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" novalidate data-login-form class="animate-in" style="--delay: 180ms">
                    @csrf

                    {{-- Correo --}}
                    <div class="mb-3">
                        <div @class(['input-group login-field', 'is-invalid' => $errors->has('email')])>
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                            <div class="form-floating">
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('email')])
                                    placeholder="nombre@imagenelectrica.com" autocomplete="username" required autofocus
                                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                <label for="email">Correo</label>
                            </div>
                        </div>
                        @error('email')
                            <div class="field-error" id="email-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-3">
                        <div @class(['input-group login-field', 'is-invalid' => $errors->has('password')])>
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                            <div class="form-floating">
                                <input type="password" id="password" name="password"
                                    @class(['form-control', 'is-invalid' => $errors->has('password')])
                                    placeholder="Contraseña" autocomplete="current-password" required data-password-input
                                    aria-describedby="caps-lock-warning @error('password') password-error @enderror"
                                    @error('password') aria-invalid="true" @enderror>
                                <label for="password">Contraseña</label>
                            </div>
                            <button type="button" class="btn password-toggle" data-password-toggle aria-controls="password" aria-label="Mostrar contraseña" aria-pressed="false">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="caps-lock-warning" id="caps-lock-warning" data-caps-warning hidden>
                            <i class="bi bi-capslock-fill" aria-hidden="true"></i> Bloqueo de mayúsculas activado
                        </div>
                        @error('password')
                            <div class="field-error" id="password-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input" @checked(old('remember'))>
                        <label for="remember" class="form-check-label">Mantener la sesión iniciada</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-login" data-submit-button>
                        <span class="spinner-border spinner-border-sm me-2" aria-hidden="true" hidden data-loading-spinner></span>
                        <span data-button-text>Ingresar</span>
                    </button>
                </form>

                <div class="login-help mt-4 animate-in" style="--delay: 260ms">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span>¿Olvidaste tu contraseña? Contacta al administrador.</span>
                </div>

                <p class="text-center small text-body-secondary mt-5 mb-0">© {{ now()->year }} Imagen Eléctrica · Bucaramanga</p>
            </div>
        </main>
    </div>

    @include('partials.toasts')
</body>
</html>
