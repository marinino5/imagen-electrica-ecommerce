@extends('layouts.minimal')

@section('title', 'Iniciar sesión')

@section('content')
    <h1 class="h3 text-uppercase mb-1">Panel <span class="text-accent">administrativo</span></h1>
    <p class="text-body-secondary mb-4">Ingresa con tu usuario de Imagen Eléctrica.</p>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                autocomplete="username" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="password"
                class="form-control @error('password') is-invalid @enderror"
                autocomplete="current-password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input" @checked(old('remember'))>
            <label for="remember" class="form-check-label">Mantener la sesión iniciada</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
@endsection
