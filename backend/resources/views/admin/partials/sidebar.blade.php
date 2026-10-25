@php($isAdmin = auth()->user()->hasRole(\App\Enums\UserRole::Admin))

<nav class="admin-nav nav flex-column" aria-label="Menú del panel">
    <a href="{{ route('admin.dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('admin.dashboard')])
        @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
        Dashboard
    </a>

    {{-- El catálogo solo lo administra el rol admin --}}
    @if ($isAdmin)
        <div class="nav-section">Catálogo</div>
        @foreach (['categories' => 'Categorías', 'brands' => 'Marcas', 'products' => 'Productos'] as $resource => $label)
            <a href="{{ route("admin.$resource.index") }}" @class(['nav-link', 'active' => request()->routeIs("admin.$resource.*")])
                @if (request()->routeIs("admin.$resource.*")) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach
    @endif
</nav>
