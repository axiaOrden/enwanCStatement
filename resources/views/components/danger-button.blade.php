<button {{ $attributes->merge(['type' => 'submit', 'class' => 'expressive-button expressive-button-danger']) }}>
    {{ $slot }}
</button>
