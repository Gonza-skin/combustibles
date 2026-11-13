@props(['name', 'label', 'options' => [], 'selected' => null, 'placeholder' => null, 'hint' => null])
<div class="campo">
    <label for="{{ $name }}">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->class(['tiene-error' => $errors->has($name)]) }}>
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $valor => $texto)
            <option value="{{ $valor }}" @selected((string) old($name, $selected) === (string) $valor)>{{ $texto }}</option>
        @endforeach
    </select>
    @if ($hint)<small class="hint">{{ $hint }}</small>@endif
    @error($name)<small class="error-texto">{{ $message }}</small>@enderror
</div>