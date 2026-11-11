<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @section('title', 'Iniciar sesión')
    @include('layouts.head')
</head>
<body>
    <div class="login-page">
        {{-- Izquierda (escritorio): la marca con un collage de fotos propias de la tienda --}}
        <aside class="login-brand" aria-label="Imagen Eléctrica">
            <span class="login-brand-arc" aria-hidden="true"></span>
            <img src="{{ asset('images/logo-blanco.png') }}" alt="Imagen Eléctrica" class="login-brand-logo">

            <div class="login-collage" aria-hidden="true">
                <figure class="lc-a"><img src="{{ asset('images/login/mostrador.webp') }}" alt="" loading="eager"></figure>
                <figure class="lc-b"><img src="{{ asset('images/login/fachada.webp') }}" alt="" loading="eager"></figure>
                <figure class="lc-c"><img src="{{ asset('images/login/vitrina.webp') }}" alt="" loading="lazy"></figure>
                <figure class="lc-d"><img src="{{ asset('images/login/compresor.webp') }}" alt="" loading="lazy"></figure>
            </div>

            <div class="login-brand-foot">
                <p class="login-brand-title">Soluciones eléctricas <span>para la industria</span></p>
                <p class="login-brand-years"><strong>25+</strong> años en Bucaramanga</p>
            </div>
        </aside>

        {{-- Derecha: el formulario --}}
        <main class="login-main">
            <div class="login-mobilebar" aria-hidden="true">
                <img src="{{ asset('images/logo-blanco.png') }}" alt="" class="login-mobilebar-logo">
            </div>

            <div class="login-card animate-in">
                <h1 class="login-heading mb-1">Ingresa al <span>panel</span></h1>
                <p class="text-body-secondary mb-4">Usa tu correo y contraseña del equipo de Imagen Eléctrica.</p>

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

                <div class="login-help mt-4">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span>¿Olvidaste tu contraseña? Contacta al administrador.</span>
                </div>
            </div>

            <p class="text-center small text-body-secondary mt-4 mb-0">© {{ now()->year }} Imagen Eléctrica · Bucaramanga</p>
        </main>
    </div>

    @include('partials.toasts')
</body>
</html>
