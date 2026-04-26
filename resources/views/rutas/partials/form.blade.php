{{-- Campos comunes para crear y editar ruta --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Nombre --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            NOMBRE DE LA RUTA <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nom_rut"
            value="{{ old('nom_rut', $ruta->nom_rut ?? '') }}"
            placeholder="EJ: RUTA AL HOSPITAL DE CLÍNICAS"
            title="NOMBRE DESCRIPTIVO DE LA RUTA — OBLIGATORIO"
            maxlength="150"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('nom_rut') border-red-500 @enderror">
        @error('nom_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Origen --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            ORIGEN <span class="text-red-500">*</span>
        </label>
        <input type="text" name="ori_rut"
            value="{{ old('ori_rut', $ruta->ori_rut ?? '') }}"
            placeholder="EJ: PLAZA MURILLO"
            title="PUNTO DE PARTIDA DE LA RUTA — OBLIGATORIO"
            maxlength="200"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('ori_rut') border-red-500 @enderror">
        @error('ori_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Destino --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            DESTINO <span class="text-red-500">*</span>
        </label>
        <input type="text" name="dst_rut"
            value="{{ old('dst_rut', $ruta->dst_rut ?? '') }}"
            placeholder="EJ: HOSPITAL DE CLÍNICAS"
            title="PUNTO DE LLEGADA DE LA RUTA — OBLIGATORIO"
            maxlength="200"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('dst_rut') border-red-500 @enderror">
        @error('dst_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Distancia --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            DISTANCIA (METROS)
        </label>
        <input type="number" name="dis_rut" step="0.01" min="0"
            value="{{ old('dis_rut', $ruta->dis_rut ?? '') }}"
            placeholder="EJ: 850.50"
            title="DISTANCIA TOTAL DE LA RUTA EN METROS — OPCIONAL"
            class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-600 focus:outline-none @error('dis_rut') border-red-500 @enderror">
        @error('dis_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tiempo --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            TIEMPO ESTIMADO (MINUTOS)
        </label>
        <input type="number" name="tim_rut" min="1"
            value="{{ old('tim_rut', $ruta->tim_rut ?? '') }}"
            placeholder="EJ: 15"
            title="TIEMPO ESTIMADO DE RECORRIDO EN MINUTOS — OPCIONAL"
            class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-600 focus:outline-none @error('tim_rut') border-red-500 @enderror">
        @error('tim_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Descripción --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">DESCRIPCIÓN</label>
        <textarea name="des_rut" rows="3" maxlength="300"
            placeholder="DESCRIPCIÓN GENERAL DE LA RUTA (OPCIONAL)"
            title="DESCRIPCIÓN OPCIONAL DE LA RUTA"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('des_rut') border-red-500 @enderror">{{ old('des_rut', $ruta->des_rut ?? '') }}</textarea>
        @error('des_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Observaciones --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">OBSERVACIONES</label>
        <textarea name="obs_rut" rows="2" maxlength="1000"
            placeholder="OBSERVACIONES ADICIONALES (OPCIONAL)"
            title="OBSERVACIONES ADICIONALES"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none">{{ old('obs_rut', $ruta->obs_rut ?? '') }}</textarea>
    </div>
</div>
