<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de Administración IBC
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-4">Bienvenido</h3>

                <p><strong>Usuario:</strong> {{ auth()->user()->nombre_completo }}</p>
                <p><strong>Correo:</strong> {{ auth()->user()->cor_usu }}</p>
                <p><strong>Rol:</strong> {{ auth()->user()->role?->nom_rol }}</p>
                <p><strong>Estado:</strong> {{ auth()->user()->est_usu }}</p>

                <hr class="my-4">

                <p class="text-gray-600 mb-4">
                    Vista provisional del panel del sistema administrativo IBC.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-100 rounded p-4">
                        <h4 class="font-bold">Usuarios</h4>
                        <p class="text-sm">Gestión provisional</p>
                    </div>

                    <div class="bg-green-100 rounded p-4">
                        <h4 class="font-bold">Afiliaciones</h4>
                        <p class="text-sm">Gestión provisional</p>
                    </div>

                    <div class="bg-yellow-100 rounded p-4">
                        <h4 class="font-bold">Rutas</h4>
                        <p class="text-sm">Gestión provisional</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>