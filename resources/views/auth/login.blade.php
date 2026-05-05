<x-guest-layout>
    <main class="min-h-screen flex items-center justify-center p-6 lg:p-8 bg-[#f9f9fc]">
        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 bg-white rounded-xl overflow-hidden custom-shadow">

            <!-- Sección visual -->
            <section class="relative flex flex-col items-center justify-center p-10 lg:p-16 overflow-hidden min-h-[400px] bg-gradient-to-br from-[#f3f3f6] to-[#eeeef0]">
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-[#006837] opacity-10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-[#ff8928] opacity-10 rounded-full blur-3xl"></div>

                <div class="relative z-10 flex flex-col items-center gap-6">
                    <div class="w-64 h-72 md:w-80 md:h-96 rounded-2xl bg-white/80 backdrop-blur-sm p-4 shadow-2xl border border-white/50 flex items-center justify-center overflow-hidden">

                        <!-- Aquí va tu imagen o mascota -->
                        <img
                            src="{{ asset('images/mascota-ibc.png') }}"
                            alt="Mascota oficial IBC"
                            class="w-full h-full object-contain"
                        >
                    </div>

                    <div class="text-center space-y-2 max-w-sm">
                        <h2 class="text-[#004d27] text-2xl font-bold uppercase tracking-[0.2em]">
                            Bienvenido
                        </h2>

                        <p class="text-[#3f4940] text-lg italic">
                            Tu seguridad y autonomía son nuestra prioridad institucional.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Sección formulario -->
            <section class="bg-white p-8 md:p-12 flex flex-col justify-center">
                <header class="mb-10">
                    <h1 class="text-3xl font-bold text-[#004d27] mb-2">
                        Sistema Web IBC
                    </h1>

                    <p class="text-[#3f4940]">
                        Plataforma institucional de gestión y rutas seguras
                    </p>
                </header>

                <!-- Estado de sesión -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Correo -->
                    <div class="space-y-2">
                        <label for="cor_usu" class="text-sm font-bold text-[#1a1c1e] flex items-center gap-2 uppercase tracking-wide">
                            <span class="material-symbols-outlined text-[#004d27]" style="font-size: 20px;">mail</span>
                            Correo electrónico
                        </label>

                        <input
                            id="cor_usu"
                            name="cor_usu"
                            type="email"
                            value="{{ old('cor_usu') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="ejemplo@ibc.gob.bo"
                            class="w-full h-[56px] px-6 rounded-lg border-2 border-[#6f7a70] focus:border-[#004d27] focus:ring-4 focus:ring-[#004d27]/10 transition-all outline-none text-base"
                        >

                        <x-input-error :messages="$errors->get('cor_usu')" class="mt-2" />
                    </div>

                    <!-- Contraseña -->
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-bold text-[#1a1c1e] flex items-center gap-2 uppercase tracking-wide">
                            <span class="material-symbols-outlined text-[#004d27]" style="font-size: 20px;">lock</span>
                            Contraseña
                        </label>

                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••••••"
                                class="w-full h-[56px] px-6 pr-14 rounded-lg border-2 border-[#6f7a70] focus:border-[#004d27] focus:ring-4 focus:ring-[#004d27]/10 transition-all outline-none text-base"
                            >

                            <button
                                id="togglePassword"
                                type="button"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[#3f4940] hover:text-[#004d27] p-2"
                                aria-label="Mostrar contraseña"
                            >
                                <span id="eyeIcon" class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Recordarme y olvidaste contraseña -->
                    <div class="flex items-center justify-between gap-6 py-1">
                        <label for="remember_me" class="flex items-center gap-3 cursor-pointer group">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="w-5 h-5 rounded border-2 border-[#6f7a70] text-[#004d27] focus:ring-[#004d27]"
                            >

                            <span class="text-[#1a1c1e] group-hover:text-[#004d27] transition-colors">
                                Recordarme
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="text-sm font-bold text-[#964900] hover:underline underline-offset-4 decoration-2"
                            >
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Botón iniciar sesión -->
                    <button
                        type="submit"
                        class="w-full h-[56px] bg-[#004d27] text-white font-semibold text-base rounded-lg shadow-lg hover:bg-[#006837] active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                    >
                        Iniciar sesión
                        <span class="material-symbols-outlined">login</span>
                    </button>
                </form>

                <footer class="mt-12 pt-8 border-t border-[#bec9be] text-center space-y-6">
                    <a
                        href="/"
                        class="inline-flex items-center gap-2 font-semibold text-[#004d27] hover:bg-[#f3f3f6] px-6 py-2 rounded-full transition-colors"
                    >
                        <span class="material-symbols-outlined">arrow_back</span>
                        Volver al inicio
                    </a>

                    <div class="flex flex-wrap justify-center gap-6 pt-4">
                        <a class="text-sm font-bold text-[#0d522e] hover:text-[#004d27]" href="#">
                            Accesibilidad
                        </a>

                        <span class="text-[#bec9be]">•</span>

                        <a class="text-sm font-bold text-[#0d522e] hover:text-[#004d27]" href="#">
                            Soporte Técnico
                        </a>
                    </div>
                </footer>
            </section>
        </div>
    </main>

    <!-- Footer inferior -->
    <footer class="w-full py-8 px-6 flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-50 border-t border-slate-200">
        <div class="flex items-center gap-2">
            <span class="text-base font-bold text-slate-900 uppercase tracking-wider">
                IBC
            </span>

            <span class="text-slate-300">|</span>

            <p class="text-sm font-medium text-slate-500">
                © 2026 Instituto Boliviano de la Ceguera - IBC
            </p>
        </div>

        <div class="flex gap-8">
            <a class="text-sm font-medium text-slate-500 hover:text-emerald-600 underline transition-all" href="#">
                Accesibilidad
            </a>

            <a class="text-sm font-medium text-slate-500 hover:text-emerald-600 underline transition-all" href="#">
                Contacto
            </a>

            <a class="text-sm font-medium text-slate-500 hover:text-emerald-600 underline transition-all" href="#">
                Privacidad
            </a>
        </div>
    </footer>

    <!-- Script del ojito -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';

                passwordInput.type = isPassword ? 'text' : 'password';
                eyeIcon.textContent = isPassword ? 'visibility_off' : 'visibility';

                togglePassword.setAttribute(
                    'aria-label',
                    isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'
                );
            });
        });
    </script>
</x-guest-layout>