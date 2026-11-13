@props(['name', 'label', 'checked' => false, 'hint' => null])
<div class="campo campo--check">
    @unless ($attributes->has('disabled'))
        <input type="hidden" name="{{ $name }}" value="0">
    @endunless
    <label for="{{ $name }}">
        <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="1" @checked((bool) old($name, $checked)) {{ $attributes }}>
        {{ $label }}
    </label>
    @if ($hint)<small class="hint">{{ $hint }}</small>@endif
</div>