<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if(session('success'))
            <meta name="flash-success" content="{{ session('success') }}">
        @endif

        @if(session('error'))
            <meta name="flash-error" content="{{ session('error') }}">
        @endif

        <title>Sistema Web IBC</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- SweetAlert2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
        
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <body class="font-sans antialiased bg-[#f5f7f6] text-gray-900">
    <div class="min-h-screen bg-[#f5f7f6]">
            @include('layouts.navigation')

            <!-- Page Heading -->
<div class="lg:ml-72 pt-16">
    @isset($header)
        <header class="bg-white shadow-sm border-b border-gray-100">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset



            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @stack('scripts')

        <!-- Flash Messages Handler -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const esNoVidente = {{ auth()->check() && auth()->user()->tieneRol('ADMINISTRADOR_NO_VIDENTE') ? 'true' : 'false' }};

                if (!esNoVidente) {
                    // Manejar mensajes de éxito
                    const successMeta = document.querySelector('meta[name="flash-success"]');
                    if (successMeta) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: successMeta.getAttribute('content'),
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#10b981',
                            timer: 3500,
                            timerProgressBar: true,
                        });
                    }

                    // Manejar mensajes de error
                    const errorMeta = document.querySelector('meta[name="flash-error"]');
                    if (errorMeta) {
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: errorMeta.getAttribute('content'),
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ef4444',
                            timer: 5000,
                            timerProgressBar: true,
                        });
                    }
                }

                // Manejar confirmaciones de eliminación
                document.querySelectorAll('[data-confirm-delete]').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        Swal.fire({
                            icon: 'warning',
                            title: '¿Estás seguro?',
                            text: this.getAttribute('data-confirm-message') || 'Esta acción no se puede deshacer.',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#009966',
                            cancelButtonColor: '#6b7280',
                            reverseButtons: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.submit();
                            }
                        });
                    });
                });
            });
        </script>
        
        <x-voice-assistant />
    </body>
</html>
