<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCACMLP</title>
    <style>
        :root {
            --petroleo: #123B5D;
            --petroleo-oscuro: #0d2c47;
            --verde: #27AE60;
            --verde-oscuro: #1f9150;
            --fondo: #f4f6f8;
            --texto: #2c3e50;
            --gris: #5b6b79;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--fondo);
            color: var(--texto);
        }
        a { text-decoration: none; }

        /* BARRA SUPERIOR */
        header {
            background: #fff;
            padding: 4px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0,0,0,.12);
        }
        .logo { display: flex; align-items: center; }
        .logo img { height: 70px; width: auto; display: block; }
        nav { display: flex; gap: 26px; }
        nav a { color: #111; font-size: 15px; font-weight: 600; }
        nav a:hover { color: var(--verde); }
        .acciones { display: flex; gap: 10px; }
        .btn {
            display: inline-block;
            padding: 10px 22px;
            border: 2px solid transparent;
            border-radius: 6px;
            font-weight: 700;
            transition: .2s;
        }
        .btn-claro { color: #111; border-color: #111; }
        .btn-claro:hover { background: #111; color: #fff; }
        .btn-verde { background: var(--verde); color: #fff; }
        .btn-verde:hover { background: var(--verde-oscuro); }

        /* PORTADA: "Información confiable..." */
        .hero {
            background: linear-gradient(135deg, var(--petroleo) 0%, var(--petroleo-oscuro) 100%);
            color: #e9e0e0;
            text-align: center;
            padding: 60px 20px 80px;
        }
        .hero h1 {
            font-size: 36px;
            line-height: 1.25;
            margin: 0 auto 18px;
            max-width: 800px;
        }
        .hero p {
            max-width: 700px;
            margin: 0 auto 30px;
            font-size: 17px;
            line-height: 1.6;
            opacity: .95;
        }
        .hero .botones { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
        .hero-logo {
            width: fit-content;
            margin: 0 auto 28px;
            background: #fff;
            border-radius: 24px;
            padding: 14px 36px;
            box-shadow: 0 8px 24px rgba(0,0,0,.25);
        }
        .hero-logo img { height: 150px; width: auto; display: block; }
        .btn-grande { padding: 14px 30px; font-size: 16px; }
        .btn-borde { border-color: #fff; color: #fff; }
        .btn-borde:hover { background: #fff; color: var(--petroleo); }

        /* EJEMPLO DE LOS ESTADOS */
        .estados {
            max-width: 900px;
            margin: -36px auto 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,.12);
            padding: 22px 30px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 14px;
            position: relative;
        }
        .estado { display: flex; align-items: center; gap: 10px; font-size: 15px; }
        .estado i { width: 14px; height: 14px; border-radius: 50%; display: inline-block; }
        .estado .normal { background: var(--verde); }
        .estado .bajo { background: #f1c40f; }
        .estado .agotado { background: #c0392b; }

        /* LAS SECCIONES */
        section { padding: 64px 30px; }
        .contenedor { max-width: 1100px; margin: 0 auto; }
        h2.titulo {
            text-align: center;
            color: var(--petroleo);
            font-size: 28px;
            margin: 0 0 10px;
        }
        .subtitulo {
            text-align: center;
            max-width: 720px;
            margin: 0 auto 40px;
            color: var(--gris);
            line-height: 1.6;
        }

        /* QUE ES */
        .info { background: #fff; }
        .info p { max-width: 800px; margin: 0 auto 14px; line-height: 1.7; color: #555; text-align: center; }

        /* LAS TARJETAS */
        .grilla {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }
        /* Tarjetas de "¿Qué puedes hacer?": 4 por fila y la última fila centrada */
        .grilla-centrada {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 24px;
        }
        .grilla-centrada .tarjeta { flex: 0 0 calc((100% - 72px) / 4); }
        @media (max-width: 1000px) {
            .grilla-centrada .tarjeta { flex-basis: calc((100% - 24px) / 2); }
        }
        @media (max-width: 600px) {
            .grilla-centrada .tarjeta { flex-basis: 100%; }
        }

        .tarjeta {
            background: #fff;
            border-radius: 10px;
            padding: 26px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            border-top: 4px solid var(--verde);
        }
                .tarjeta .icono {
            display: block;
            width: 100%;
            height: 120px;
            object-fit: contain;
            margin-bottom: 18px;
        }
        .grilla-centrada .tarjeta { text-align: center; }
        .tarjeta h3 { margin: 0 0 10px; color: var(--petroleo); font-size: 18px; }
        .tarjeta p { margin: 0; font-size: 14px; color: #555; line-height: 1.55; }

        /* COMO FUNCIONA */
        .pasos { background: #fff; }
        .paso { text-align: center; padding: 10px; }
        .paso .num {
            width: 48px; height: 48px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: var(--petroleo);
            color: #fff;
            font-size: 20px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .paso h3 { margin: 0 0 8px; color: var(--petroleo); }
        .paso p { margin: 0; font-size: 14px; color: #555; line-height: 1.55; }

        /* LLAMADA FINAL */
        .cta { background: var(--petroleo); color: #fff; text-align: center; }
        .cta h2 { margin: 0 0 12px; font-size: 28px; }
        .cta p { max-width: 600px; margin: 0 auto 26px; opacity: .95; line-height: 1.6; }

        footer {
            text-align: center;
            padding: 24px;
            background: var(--petroleo-oscuro);
            color: #cfd8dc;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            header { padding: 4px 18px; justify-content: center; }
            nav { display: none; }
            .logo img { height: 50px; }
            .hero { padding: 40px 18px 70px; }
            .hero h1 { font-size: 27px; }
            .hero-logo img { height: 110px; }
        }
    </style>
</head>
<body>

    {{-- ===== BARRA SUPERIOR ===== --}}
    <header>
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('img/logo.png') }}" alt="SCACMLP - Sistema de Control de Abastecimiento de Combustible">
        </a>
        <nav>
            <a href="#que-es">¿Qué es?</a>
            <a href="#funciones">Funciones</a>
            <a href="#como-funciona">Cómo funciona</a>
        </nav>
        <div class="acciones">
            {{-- Más adelante: cambiar "#" por {{ route('login') }} y {{ route('registro') }} --}}
            <a href="{{ route('login') }}" class="btn btn-claro">Iniciar sesión</a>
            <a href="{{ route('registro') }}" class="btn btn-verde">Registrarse</a>
        </div>
    </header>

    {{-- ===== PORTADA ===== --}}
    <div class="hero">
        <div class="hero-logo">
            <img src="{{ asset('img/logo.png') }}" alt="SCACMLP - Sistema de Control de Abastecimiento de Combustible">
        </div>
        <h1>Información confiable sobre combustible en tu estación más cercana</h1>
        <p>
            Consulta la disponibilidad de gasolina y diésel, el estado de las filas
            y la ubicación de las estaciones de servicio del Municipio de La Paz,
            en tiempo real.
        </p>
        <div class="botones">
            <a href="{{ route('registro') }}" class="btn btn-verde btn-grande">Crear mi cuenta</a>
            <a href="#que-es" class="btn btn-borde btn-grande">Conocer el sistema</a>
        </div>
    </div>

    {{-- Ejemplo visual de los estados de una estación --}}
    <div class="estados">
        <div class="estado"><i class="normal"></i> <strong>Normal:</strong> hay combustible</div>
        <div class="estado"><i class="bajo"></i> <strong>Nivel bajo:</strong> queda poco</div>
        <div class="estado"><i class="agotado"></i> <strong>Sin combustible:</strong> agotado</div>
    </div>

    {{-- ===== QUÉ ES ===== --}}
    <section class="info" id="que-es">
        <div class="contenedor">
            <h2 class="titulo">¿Qué es este sistema?</h2>
            <p>
                El Sistema de Control de Abastecimiento de Combustible nace para enfrentar
                la crisis de desabastecimiento en el Municipio de La Paz. Brinda información digital,
                confiable y en tiempo real sobre la disponibilidad de combustible en las estaciones
                de servicio.
            </p>
            <p>
                Así se reduce la incertidumbre de miles de conductores que dependen de este recurso
                a diario, y el municipio puede controlar mejor cómo llega el combustible a cada estación.
            </p>
        </div>
    </section>

    {{-- ===== FUNCIONES ===== --}}
    <section id="funciones">
        <div class="contenedor">
            <h2 class="titulo">¿Qué puedes hacer?</h2>
            <p class="subtitulo">Herramientas para ciudadanos, estaciones y el personal del municipio.</p>

                        <div class="grilla grilla-centrada">
                <div class="tarjeta">
                    <img class="icono" src="{{ asset('img/funciones/mapa.png') }}" alt="Mapa con estaciones de servicio">
                    <h3>Mapa interactivo</h3>
                    <p>Ubica las estaciones de servicio más cercanas y mira su estado actual de abastecimiento.</p>
                </div>
                <div class="tarjeta">
                    <img class="icono" src="{{ asset('img/funciones/disponibilidad.png') }}" alt="Surtidor con estado de disponibilidad">
                    <h3>Disponibilidad en tiempo real</h3>
                    <p>Consulta si una estación tiene combustible disponible, nivel bajo o está agotada.</p>
                </div>
                <div class="tarjeta">
                    <img class="icono" src="{{ asset('img/funciones/reportes.png') }}" alt="Ciudadanos compartiendo reportes">
                    <h3>Reportes ciudadanos</h3>
                    <p>Comparte y consulta el estado de las filas reportado por otros usuarios.</p>
                </div>
                <div class="tarjeta">
                    <img class="icono" src="{{ asset('img/funciones/seguimiento.png') }}" alt="Camión cisterna y lista de entregas">
                    <h3>Seguimiento de abastecimiento</h3>
                    <p>El municipio controla la asignación de cisternas, horarios y entregas a cada estación.</p>
                </div>
                <div class="tarjeta">
                    <img class="icono" src="{{ asset('img/funciones/existencias.png') }}" alt="Surtidor, barriles y lista de control">
                    <h3>Control de existencias</h3>
                    <p>Cada estación registra sus existencias de gasolina y diésel, con historial de movimientos.</p>
                </div>
                <div class="tarjeta">
                    <img class="icono" src="{{ asset('img/funciones/alertas.png') }}" alt="Alerta e incidencia en una estación">
                    <h3>Alertas e incidencias</h3>
                    <p>Se detectan retrasos y diferencias de combustible para actuar a tiempo.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CÓMO FUNCIONA ===== --}}
    <section class="pasos" id="como-funciona">
        <div class="contenedor">
            <h2 class="titulo">¿Cómo funciona?</h2>
            <p class="subtitulo">Tres pasos para estar informado.</p>

            <div class="grilla">
                <div class="paso">
                    <div class="num">1</div>
                    <h3>Crea tu cuenta</h3>
                    <p>Regístrate con tu correo. Es gratis y toma menos de un minuto.</p>
                </div>
                <div class="paso">
                    <div class="num">2</div>
                    <h3>Busca tu estación</h3>
                    <p>Revisa el mapa y el estado de combustible de las estaciones cercanas.</p>
                </div>
                <div class="paso">
                    <div class="num">3</div>
                    <h3>Comparte y decide</h3>
                    <p>Reporta cómo está la fila y elige la estación con menos espera.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== LLAMADA FINAL ===== --}}
    <section class="cta">
        <h2>Empieza a consultar hoy</h2>
        <p>Ahorra tiempo y evita filas innecesarias con información del estado real de las estaciones.</p>
        <a href="{{ route('registro') }}" class="btn btn-verde btn-grande">Registrarme ahora</a>
    </section>

    <footer>
        © {{ date('Y') }} Sistema de Control de Abastecimiento de Combustible — Municipio de La Paz
    </footer>
</body>
</html>