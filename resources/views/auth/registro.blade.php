<x-layouts.auth title="Crear cuenta">
    <h1>Crear cuenta</h1>
    <p class="sub">Regístrate para consultar estaciones y disponibilidad.</p>

    <form method="POST" action="{{ route('registro') }}">
        @csrf

        <label for="nombre">Nombre completo</label>
        <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" required autofocus>
        @error('nombre')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">Contraseña (mínimo 8 caracteres)</label>
        <input id="password" type="password" name="password" required>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">Repite la contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit" class="boton">Crear cuenta</button>
    </form>

    <a class="enlace" href="{{ route('login') }}">¿Ya tienes cuenta? Inicia sesión</a>
    <a class="enlace" href="{{ route('home') }}">Volver al inicio</a>
</x-layouts.auth>