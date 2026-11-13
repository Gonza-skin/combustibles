<div class="tabla-wrap">
    <table {{ $attributes->merge(['class' => 'tabla']) }}>
        <thead><tr>{{ $head }}</tr></thead>
        <tbody>{{ $slot }}</tbody>
    </table>
</div>