<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-database mr-2" style="color:#006837"></i> COPIAS DE SEGURIDAD
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">GESTIÓN DE RESPALDOS Y RESTAURACIÓN DE LA BASE DE DATOS DEL SISTEMA</p>
            </div>
            <div>
                <button type="button" onclick="generarBackup()"
                    class="inline-flex items-center gap-2 bg-[#006837] hover:bg-[#004d27] text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-md uppercase transition-all">
                    <i class="fas fa-plus"></i> GENERAR NUEVA COPIA
                </button>
                <form id="form-crear-backup" action="{{ route('admin.backups.store') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alerta informativa --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-xl">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-500">info</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 uppercase font-semibold">
                            INFORMACIÓN DE SEGURIDAD
                        </p>
                        <p class="text-xs text-blue-600 mt-1 uppercase">
                            Las copias de seguridad contienen la estructura y los registros completos de la base de datos en formato SQL. 
                            Se recomienda descargar los respaldos generados y almacenarlos en un lugar seguro externo.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Listado de respaldos --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 uppercase text-sm">ARCHIVOS DE RESPALDO DISPONIBLES</h3>
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full font-bold text-xs uppercase">
                        TOTAL: {{ count($files) }} ARCHIVOS
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#006837]">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-white uppercase tracking-wider">NOMBRE DEL ARCHIVO</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-white uppercase tracking-wider w-48">FECHA DE CREACIÓN</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-white uppercase tracking-wider w-36">TAMAÑO</th>
                                <th class="px-6 py-3.5 text-center text-xs font-bold text-white uppercase tracking-wider w-72">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($files as $index => $file)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-gray-800 uppercase">
                                        <i class="fas fa-file-alt text-gray-400 mr-2"></i>
                                        {{ $file['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                        {{ $file['created_at']->format('d/m/Y H:i:s') }}
                                        <span class="text-xs text-gray-400 ml-1">({{ $file['created_at']->diffForHumans() }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-bold">
                                        {{ $file['size'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex justify-center gap-2">
                                        {{-- Descargar --}}
                                        <a href="{{ route('admin.backups.download', $file['name']) }}"
                                            class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold px-3 py-2 rounded-xl transition-all uppercase"
                                            title="DESCARGAR ARCHIVO">
                                            <i class="fas fa-download"></i> DESCARGAR
                                        </a>

                                        {{-- Restaurar --}}
                                        <button type="button" onclick="confirmarRestauracion('{{ $file['name'] }}')"
                                            class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold px-3 py-2 rounded-xl transition-all uppercase"
                                            title="RESTAURAR BASE DE DATOS">
                                            <i class="fas fa-undo"></i> RESTAURAR
                                        </button>
                                        <form id="form-restaurar-{{ $file['name'] }}" action="{{ route('admin.backups.restore', $file['name']) }}" method="POST" class="hidden">
                                            @csrf
                                        </form>

                                        {{-- Eliminar --}}
                                        <button type="button" onclick="confirmarEliminacion('{{ $file['name'] }}')"
                                            class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold px-3 py-2 rounded-xl transition-all uppercase"
                                            title="ELIMINAR RESPALDO">
                                            <i class="fas fa-trash-alt"></i> ELIMINAR
                                        </button>
                                        <form id="form-eliminar-{{ $file['name'] }}" action="{{ route('admin.backups.destroy', $file['name']) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <span class="material-symbols-outlined text-6xl text-gray-300">database_off</span>
                                            <p class="text-gray-400 font-bold uppercase">NO SE ENCONTRARON ARCHIVOS DE COPIA DE SEGURIDAD DISPONIBLES.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function generarBackup() {
            Swal.fire({
                title: 'GENERANDO COPIA DE SEGURIDAD',
                html: '<div class="uppercase text-sm text-gray-600 mb-2">Extrayendo estructura y registros de la base de datos...</div>',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('form-crear-backup').submit();
        }

        function confirmarEliminacion(filename) {
            Swal.fire({
                title: '¿ELIMINAR ARCHIVO?',
                html: `<div class="text-sm uppercase text-gray-600 mb-2">¿Estás seguro de que deseas eliminar este respaldo?</div>
                       <div class="font-mono text-xs text-gray-800 font-bold mb-2">${filename}</div>
                       <small class="text-red-600 font-bold uppercase">ESTA ACCIÓN NO SE PUEDE DESHACER.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'SÍ, ELIMINAR',
                cancelButtonText: 'CANCELAR',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`form-eliminar-${filename}`).submit();
                }
            });
        }

        function confirmarRestauracion(filename) {
            Swal.fire({
                title: '¿RESTAURAR BASE DE DATOS?',
                html: `<div class="text-sm uppercase text-gray-600 mb-4">
                         Se reemplazará la base de datos actual con la del archivo:<br>
                         <strong class="text-gray-900 font-bold font-mono text-xs">${filename}</strong>
                       </div>
                       <div class="text-xs text-red-600 font-bold uppercase mb-4">
                         ¡ATENCIÓN! ESTA ACCIÓN REESCRIBIRÁ TODOS LOS DATOS ACTUALES Y NO SE PUEDE DESHACER.
                       </div>
                       <div class="text-xs uppercase text-gray-700 font-semibold mb-2">
                         Para confirmar, escribe la palabra <strong class="text-red-600">RESTAURAR</strong> abajo:
                       </div>`,
                input: 'text',
                inputPlaceholder: 'Escribe RESTAURAR aquí...',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'RESTAURAR AHORA',
                cancelButtonText: 'CANCELAR',
                reverseButtons: true,
                preConfirm: (value) => {
                    if (value !== 'RESTAURAR') {
                        Swal.showValidationMessage('DEBES ESCRIBIR EXACTAMENTE "RESTAURAR"');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'RESTAURANDO BASE DE DATOS',
                        html: '<div class="uppercase text-sm text-gray-600 mb-2">Ejecutando sentencias SQL. Por favor espere y no cierre el navegador...</div>',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById(`form-restaurar-${filename}`).submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
