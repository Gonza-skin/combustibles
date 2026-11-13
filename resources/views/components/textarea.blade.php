@props(['name', 'label', 'value' => null, 'hint' => null, 'rows' => 4])
<div class="campo">
    <label for="{{ $name }}">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              {{ $attributes->class(['tiene-error' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @if ($hint)<small class="hint">{{ $hint }}</small>@endif
    @error($name)<small class="error-texto">{{ $message }}</small>@enderror
</div>