<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-shield-alt mr-2" style="color:#2d6a27"></i> NIVELES DE SEGURIDAD
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">CATÁLOGO DE NIVELES PARA EVALUACIÓN DE RUTAS Y TRAMOS</p>
            </div>
            <a href="{{ route('rutas.index') }}"
                class="inline-flex items-center gap-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold px-4 py-2 rounded uppercase">
                <i class="fas fa-route"></i> VER RUTAS
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead style="background-color:#2d6a27">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold text-white uppercase">NIVEL</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-white uppercase">NOMBRE</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-white uppercase">DESCRIPCIÓN</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-white uppercase">COLOR</th>
                            @if(Auth::user()->puedeGestionarNivelesSeguridad())
                                <th class="px-5 py-3 text-center text-xs font-bold text-white uppercase">ACCIÓN</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($niveles as $nivel)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-white text-sm"
                                        style="background-color:{{ $nivel->colorHex() }}">
                                        {{ $nivel->niv_nse }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-bold text-sm px-3 py-1 rounded uppercase"
                                        style="background-color:{{ $nivel->colorHex() }}20; color:{{ $nivel->colorHex() }}">
                                        {{ $nivel->nom_nse }}
                                        @if($nivel->esCritico())
                                            <i class="fas fa-exclamation-circle ml-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 uppercase">
                                    {{ $nivel->des_nse ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full border border-gray-300 inline-block"
                                            style="background-color:{{ $nivel->colorHex() }}"></span>
                                        <span class="font-mono text-xs text-gray-500">{{ $nivel->colorHex() }}</span>
                                    </div>
                                </td>
                                @if(Auth::user()->puedeGestionarNivelesSeguridad())
                                    <td class="px-5 py-4 text-center">
                                        <a href="{{ route('niveles-seguridad.edit', $nivel) }}"
                                            class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-bold px-3 py-1 rounded uppercase">
                                            <i class="fas fa-pencil-alt mr-1"></i> EDITAR
                                        </a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-4 text-xs text-gray-400 uppercase text-center">
                LOS NOMBRES Y NÚMEROS DE NIVEL SON FIJOS DEL SISTEMA — SOLO SE PUEDE EDITAR DESCRIPCIÓN Y COLOR
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const s = document.querySelector('meta[name="flash-success"]');
        if (s) Swal.fire({ toast:true, position:'top-end', icon:'success', title: s.content, showConfirmButton:false, timer:3500, timerProgressBar:true });
    });
    </script>
    @endpush
</x-app-layout>
