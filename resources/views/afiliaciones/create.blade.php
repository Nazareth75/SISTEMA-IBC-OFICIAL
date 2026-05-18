<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrar Afiliación
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Complete los datos requeridos para registrar una nueva afiliación.
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

            <form action="{{ route('afiliaciones.store') }}" method="POST">
                @csrf
                @include('afiliaciones.partials.form')
            </form>
        </div>
    </div>

    <script>
        // Buscador interactivo de personas sin afiliación
        const inputBuscar = document.getElementById('buscar_persona');
        const resultadosContainer = document.getElementById('resultados_personas');
        const inputPersonaSeleccionada = document.getElementById('cod_personas_no_videntes_afi');
        const personaSeleccionadaDiv = document.getElementById('persona_seleccionada');
        const nombrePersonaSel = document.getElementById('nombre_persona_sel');

        let timeoutId = null;

        inputBuscar.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();

            // Limpiar si está vacío
            if (query.length === 0) {
                resultadosContainer.innerHTML = '';
                return;
            }

            // Debounce: esperar 300ms después de dejar de escribir
            timeoutId = setTimeout(() => {
                buscarPersonas(query);
            }, 300);
        });

        function buscarPersonas(query) {
            const url = `{{ route('afiliaciones.buscar-personas') }}?q=${encodeURIComponent(query)}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    mostrarResultados(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultadosContainer.innerHTML = '<p class="col-span-full text-center text-red-600 text-sm">Error al buscar personas</p>';
                });
        }

        function mostrarResultados(personas) {
            resultadosContainer.innerHTML = '';

            if (personas.length === 0) {
                resultadosContainer.innerHTML = '<p class="col-span-full text-center text-gray-500 text-sm py-4">No se encontraron personas disponibles</p>';
                return;
            }

            personas.forEach(persona => {
                const fichaHTML = `
                    <div class="cursor-pointer transform transition hover:scale-105 hover:shadow-md"
                         onclick="seleccionarPersona('${persona.id}', '${persona.nombre_completo}', '${persona.codigo}')">
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-indigo-200 rounded-lg p-4 hover:border-indigo-500 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 text-sm truncate">${persona.nombre_completo}</p>
                                    <p class="text-xs text-gray-600 mt-1">CI: <span class="font-mono">${persona.cii}</span></p>
                                </div>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${persona.genero === 'FEMENINO' ? 'bg-pink-100 text-pink-800' : 'bg-blue-100 text-blue-800'}">
                                    ${persona.genero === 'FEMENINO' ? '♀' : '♂'}
                                </span>
                            </div>
                            <div class="text-xs text-indigo-700 bg-indigo-100 rounded px-2 py-1 inline-block font-mono">
                                ${persona.codigo}
                            </div>
                        </div>
                    </div>
                `;
                resultadosContainer.insertAdjacentHTML('beforeend', fichaHTML);
            });
        }

        function seleccionarPersona(id, nombreCompleto, codigo) {
            // Guardar el ID en el input oculto
            inputPersonaSeleccionada.value = id;

            // Mostrar mensaje de selección
            nombrePersonaSel.textContent = `${nombreCompleto} (${codigo})`;
            personaSeleccionadaDiv.classList.remove('hidden');

            // Limpiar resultados y input de búsqueda
            inputBuscar.value = nombreCompleto;
            resultadosContainer.innerHTML = '';
        }

        // Cargar personas sin afiliación al iniciar (si hay búsqueda anterior)
        document.addEventListener('DOMContentLoaded', function() {
            const personaId = inputPersonaSeleccionada.value;
            if (personaId) {
                personaSeleccionadaDiv.classList.remove('hidden');
            }
        });
    </script>
</x-app-layout>