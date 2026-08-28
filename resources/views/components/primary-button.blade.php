<button {{ $attributes->merge(['type' => 'submit', 'class' => 'expressive-button expressive-button-primary']) }}>
    {{ $slot }}
</button>
