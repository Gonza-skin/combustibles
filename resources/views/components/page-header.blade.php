@props(['titulo', 'subtitulo' => null])
<div class="page-header">
    <div>
        <h1>{{ $titulo }}</h1>
        @if ($subtitulo)<p>{{ $subtitulo }}</p>@endif
    </div>
    <div>{{ $slot }}</div>
</div>