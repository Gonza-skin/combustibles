@props(['valor', 'texto', 'alerta' => false])
<div {{ $attributes->class(['stat', 'stat--alerta' => $alerta]) }}>
    <div class="stat__valor">{{ $valor }}</div>
    <div class="stat__texto">{{ $texto }}</div>
</div>