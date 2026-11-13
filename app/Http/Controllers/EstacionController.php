<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Services\UbicacionGoogleMaps;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstacionController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $estaciones = Estacion::query()
            ->when($q !== '', fn ($c) => $c->where(
                fn ($w) => $w->where('NOM_EST', 'like', "%{$q}%")
                    ->orWhere('ZON_EST', 'like', "%{$q}%")
                    ->orWhere('DIR_EST', 'like', "%{$q}%")
            ))
            ->orderBy('NOM_EST')
            ->paginate(9)
            ->withQueryString();

        return view('estaciones.index', compact('estaciones', 'q'));
    }

    public function create(): View
    {
        return view('estaciones.create', ['estacion' => new Estacion(['ACT_EST' => true, 'TPT_EST' => 60])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());
        $datos['ACT_EST'] = $request->boolean('ACT_EST');
        [$datos, $sinCoordenadas] = $this->resolverCoordenadas($datos, null);

        Estacion::create($datos);

        return $this->respuesta('Estación creada correctamente.', $sinCoordenadas);
    }

    public function edit(Estacion $estacion): View
    {
        return view('estaciones.edit', compact('estacion'));
    }

    public function update(Request $request, Estacion $estacion): RedirectResponse
    {
        $datos = $request->validate($this->reglas());
        $datos['ACT_EST'] = $request->boolean('ACT_EST');
        [$datos, $sinCoordenadas] = $this->resolverCoordenadas($datos, $estacion);

        $estacion->update($datos);

        return $this->respuesta('Estación actualizada correctamente.', $sinCoordenadas);
    }

    public function destroy(Estacion $estacion): RedirectResponse
    {
        try {
            $estacion->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar "'.$estacion->NOM_EST.'": ya tiene registros asociados (existencias, ventas, etc.). Desactívala en su lugar.');
        }

        return redirect()->route('estaciones.index')->with('exito', 'Estación eliminada.');
    }

    private function reglas(): array
    {
        return [
            'NOM_EST' => ['required', 'string', 'max:120'],
            // Aquí se pega el enlace de Google Maps: el botón «Cómo llegar» lo abre directamente.
            // (También se acepta una dirección escrita; si empieza con http, debe ser de Google Maps.)
            'DIR_EST' => ['required', 'string', 'max:500', function (string $atributo, mixed $valor, \Closure $fallar) {
                $texto = trim((string) $valor);

                if (preg_match('~^https?://~i', $texto) === 1 && ! app(UbicacionGoogleMaps::class)->esEnlace($texto)) {
                    $fallar('Pega un enlace de Google Maps (por ejemplo: https://maps.app.goo.gl/...).');
                }
            }],
            'ZON_EST' => ['nullable', 'string', 'max:80'],
            // Se llenan solas al guardar el enlace; se escriben a mano solo si Google no deja leerlas
            'LAT_EST' => ['nullable', 'numeric', 'between:-90,90', 'required_with:LON_EST'],
            'LON_EST' => ['nullable', 'numeric', 'between:-180,180', 'required_with:LAT_EST'],
            'MIN_EST' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'MAX_EST' => ['required', 'numeric', 'gte:MIN_EST', 'max:99999999.99'],
            'TPT_EST' => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }

    /**
     * Decide la latitud y longitud que se guardan:
     *  - Si la persona escribió coordenadas distintas a las guardadas, se respetan.
     *  - Si no hay coordenadas, o el enlace cambió, se leen del enlace de Google Maps.
     * Devuelve [datos, true si quedó sin coordenadas].
     */
    private function resolverCoordenadas(array $datos, ?Estacion $estacion): array
    {
        $lat = isset($datos['LAT_EST']) ? (float) $datos['LAT_EST'] : null;
        $lon = isset($datos['LON_EST']) ? (float) $datos['LON_EST'] : null;

        $enlaceCambio = ! $estacion || trim((string) $estacion->DIR_EST) !== trim((string) $datos['DIR_EST']);
        $escribioCoordenadas = $lat !== null && $lon !== null
            && (! $estacion || ! $estacion->tieneCoordenadas()
                || abs($lat - (float) $estacion->LAT_EST) > 1e-7 || abs($lon - (float) $estacion->LON_EST) > 1e-7);

        if ($lat === null || $lon === null || ($enlaceCambio && ! $escribioCoordenadas)) {
            $leidas = preg_match('~^https://~i', trim((string) $datos['DIR_EST'])) === 1
                ? app(UbicacionGoogleMaps::class)->coordenadas($datos['DIR_EST'])
                : null;

            [$lat, $lon] = $leidas ?? [null, null];
        }

        $datos['LAT_EST'] = $lat;
        $datos['LON_EST'] = $lon;

        return [$datos, $lat === null || $lon === null];
    }

    private function respuesta(string $mensaje, bool $sinCoordenadas): RedirectResponse
    {
        if ($sinCoordenadas) {
            return redirect()->route('estaciones.index')->with('error', $mensaje.' Pero no se pudo leer su ubicación del enlace: edítala y escribe la latitud y longitud.');
        }

        return redirect()->route('estaciones.index')->with('exito', $mensaje);
    }
}
