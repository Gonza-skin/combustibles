@props(['title' => 'SCACMLP'])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — SCACMLP</title>
    <style>
        :root {
            --petroleo: #123B5D;
            --petroleo-oscuro: #0d2c47;
            --verde: #27AE60;
            --verde-oscuro: #1f9150;
            --texto: #1f2d3a;
            --gris: #5b6b79;
            --error: #c0392b;
        }
        * { box-sizing: border-box; }
        a { text-decoration: none; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--texto);
            background: linear-gradient(135deg, var(--petroleo) 0%, var(--petroleo-oscuro) 100%);
        }
        .tarjeta {
            width: 100%;
            max-width: 430px;
            background: #fff;
            border-radius: 16px;
            padding: 28px 32px 32px;
            box-shadow: 0 12px 34px rgba(0,0,0,.3);
        }
        .logo { display: block; margin: 0 auto 8px; width: fit-content; }
        .logo img { height: 110px; width: auto; display: block; }
        h1 { margin: 0 0 4px; font-size: 1.5rem; color: var(--petroleo); text-align: center; }
        .sub { margin: 0 0 24px; font-size: .92rem; color: var(--gris); text-align: center; }
        label { display: block; margin-bottom: 6px; font-size: .85rem; font-weight: 600; }
        input {
            width: 100%;
            padding: 11px 12px;
            margin-bottom: 16px;
            border: 1px solid #cfd8df;
            border-radius: 8px;
            font-size: 1rem;
        }
        input:focus { outline: 2px solid var(--verde); border-color: var(--verde); }
        .boton {
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 8px;
            background: var(--verde);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }
        .boton:hover { background: var(--verde-oscuro); }
        .boton-borde { background: #fff; color: #111; border: 2px solid #111; }
        .boton-borde:hover { background: #111; color: #fff; }
        .error { margin: -10px 0 14px; font-size: .85rem; font-weight: 600; color: var(--error); }
        .enlace { display: block; margin-top: 16px; text-align: center; font-size: .9rem; color: var(--petroleo); font-weight: 600; }
        .enlace:hover { color: var(--verde); }
    </style>
</head>
<body>
    <main class="tarjeta">
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('img/logo.png') }}" alt="SCACMLP - Sistema de Control de Abastecimiento de Combustible">
        </a>

        {{ $slot }}
    </main>
</body>
</html>