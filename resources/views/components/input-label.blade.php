@props(['value'])

<label {{ $attributes->merge(['class' => 'expressive-label']) }}>
    {{ $value ?? $slot }}
</label>
