@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])
<div class="campo">
    <label for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           {{ $attributes->class(['tiene-error' => $errors->has($name)]) }}>
    @if ($hint)<small class="hint">{{ $hint }}</small>@endif
    @error($name)<small class="error-texto">{{ $message }}</small>@enderror
</div>