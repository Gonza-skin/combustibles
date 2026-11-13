{{--
    Layout principal del sistema (pantallas con sesión iniciada).
    Uso:  <x-layouts.app title="Nombre de la página"> ...contenido... </x-layouts.app>
    El menú lateral sale de config/menu.php (filtrado por el rol del usuario).
--}}
@props(['title' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' — ' : '' }}SCACMLP</title>
    <link rel="stylesheet" href="{{ asset('css/sistema.css') }}">
    {{-- FontAwesome + SweetAlert2 (se compilan con "npm run build"). Si aún no se compilaron, el sistema sigue funcionando sin ellos. --}}
    @if (file_exists(public_path('hot')) || (is_file(public_path('build/manifest.json')) && str_contains(file_get_contents(public_path('build/manifest.json')), 'resources/js/sistema.js')))
        @vite('resources/js/sistema.js')
    @endif
</head>
<body>
<div class="app">
    <header class="app__barra">
        <a class="app__marca" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
            <img src="{{ asset('img/logo.png') }}" alt="SCACMLP - Sistema de Control de Abastecimiento de Combustible">
        </a>
        @auth
            <div class="app__usuario">
                <span>{{ auth()->user()->NOM_USU }}</span>
                @if (auth()->user()->rol)
                    <span class="rol-chip">{{ auth()->user()->rol->nombre }}</span>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button variant="outline" class="btn--sm">Cerrar sesión</x-button>
                </form>
            </div>
        @endauth
    </header>

    <nav class="menu" aria-label="Menú principal">
        @auth
            @php($grupoActual = null)
            @foreach (config('menu', []) as $item)
                @continue(! \Illuminate\Support\Facades\Route::has($item['ruta']))
                @continue(! auth()->user()->tieneRol(...($item['roles'] ?? [])))
                @continue(isset($item['permiso']) && ! auth()->user()->can($item['permiso']))
                @if (($item['grupo'] ?? null) !== $grupoActual)
                    @php($grupoActual = $item['grupo'] ?? null)
                    @if ($grupoActual)
                        <div class="menu__titulo">{{ $grupoActual }}</div>
                    @endif
                @endif
                <a href="{{ route($item['ruta']) }}"
                   @class(['activo' => request()->routeIs(\Illuminate\Support\Str::beforeLast($item['ruta'], '.').'.*') || request()->routeIs($item['ruta'])])>
                    <i class="fa-solid {{ $item['icono'] ?? 'fa-circle' }} menu__icono"></i>
                    {{ $item['titulo'] }}
                </a>
            @endforeach
        @endauth
    </nav>

    <main class="contenido">
        <x-alert />
        {{ $slot }}
    </main>
</div>
</body>
</html>