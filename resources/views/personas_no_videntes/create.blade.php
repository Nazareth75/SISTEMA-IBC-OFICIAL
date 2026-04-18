<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrar Persona No Vidente
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Complete los datos requeridos para registrar una nueva persona.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <p class="font-semibold mb-2">Se encontraron errores en el formulario:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('personas-no-videntes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @include('personas_no_videntes.partials.form')
            </form>
        </div>
    </div>
</x-app-layout>