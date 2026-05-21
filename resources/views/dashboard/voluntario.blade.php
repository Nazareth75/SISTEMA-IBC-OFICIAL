<x-app-layout>
    <div class="min-h-screen bg-[#f9f9fc] py-8">
        <div class="max-w-5xl mx-auto px-6 space-y-8">
            <section class="bg-white rounded-2xl shadow p-8 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-[#006837]">PANEL VOLUNTARIO</p>
                    <h1 class="text-3xl font-bold text-[#004d27] mt-2">Panel de Voluntario</h1>
                    <p class="text-gray-600 mt-3 max-w-3xl">Tu acceso es limitado y puedes reportar obstáculos durante recorridos.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('reportes-obstaculos.mis-reportes') }}" class="px-6 py-3 bg-white border border-[#006837] text-[#006837] font-bold rounded-xl shadow hover:bg-gray-50 transition-all text-center">
                        Mis Reportes
                    </a>
                    <a href="{{ route('reportes-obstaculos.create') }}" class="px-6 py-3 bg-[#006837] text-white font-bold rounded-xl shadow-lg hover:bg-[#004d27] transition-all text-center">
                        + Nuevo Reporte
                    </a>
                </div>
            </section>

            <section class="bg-[#e8f7ef] rounded-2xl border border-[#bfe7cf] p-6">
                <h2 class="text-xl font-bold text-[#004d27]">ACCESO LIMITADO</h2>
                <p class="text-gray-700 mt-2">Este panel no muestra módulos administrativos ni funciones de validación.</p>
            </section>
        </div>
    </div>
</x-app-layout>
