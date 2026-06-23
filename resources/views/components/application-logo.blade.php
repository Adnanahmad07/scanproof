@props(['class' => 'w-32 h-10'])

<img src="{{ asset('images/logo.jpeg') }}" alt="ScanProof Logo" {{ $attributes->merge(['class' => $class . ' rounded-xl object-contain shadow-sm bg-white']) }}>
