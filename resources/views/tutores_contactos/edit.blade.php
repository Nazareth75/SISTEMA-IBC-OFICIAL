<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Tutor / Contacto
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Persona: {{ $persona->nombre_completo }} ({{ $persona->ide_pnv }})
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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

            <form action="{{ route('personas-no-videntes.tutores.update', [$persona, $tutor]) }}" method="POST">
                @csrf
                @method('PUT')
                @include('tutores_contactos._form')
            </form>
        </div>
    </div>
</x-app-layout>