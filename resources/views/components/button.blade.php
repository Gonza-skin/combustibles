@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn--'.$variant]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn--'.$variant]) }}>{{ $slot }}</button>
@endif