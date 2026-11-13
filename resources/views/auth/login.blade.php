<x-layouts.auth title="Iniciar sesión">
    <h1>Iniciar sesión</h1>
    <p class="sub">Ingresa con tu correo y contraseña.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit" class="boton">Entrar</button>
    </form>

    <a class="enlace" href="{{ route('registro') }}">¿No tienes cuenta? Regístrate</a>
    <a class="enlace" href="{{ route('home') }}">Volver al inicio</a>
</x-layouts.auth>