<x-app-layout>
    <div class="min-h-screen bg-[#f9f9fc] py-8">
        <div class="max-w-7xl mx-auto px-6 space-y-8">
            <section class="bg-white rounded-2xl shadow p-8 border border-gray-100">
                <p class="text-sm font-semibold uppercase tracking-widest text-[#006837]">PANEL TÉCNICO</p>
                <h1 class="text-3xl font-bold text-[#004d27] mt-2">Panel Técnico</h1>
                <p class="text-gray-600 mt-3 max-w-3xl">Acceso operativo a rutas seguras y obstáculos.</p>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('rutas.index') }}" class="bg-white rounded-2xl shadow p-6 border border-gray-100 hover:border-[#006837] transition">
                    <p class="text-sm font-semibold uppercase text-gray-500">MÓDULO</p>
                    <h2 class="text-xl font-bold text-[#004d27] mt-2">RUTAS SEGURAS</h2>
                </a>

                <a href="{{ route('obstaculos.index') }}" class="bg-white rounded-2xl shadow p-6 border border-gray-100 hover:border-[#006837] transition">
                    <p class="text-sm font-semibold uppercase text-gray-500">MÓDULO</p>
                    <h2 class="text-xl font-bold text-[#004d27] mt-2">OBSTÁCULOS</h2>
                </a>
            </section>
        </div>
    </div>
</x-app-layout>
