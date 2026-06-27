<x-layouts.guest title="Login - {{ config('app.name') }}">

    {{-- ============================================================ --}}
    {{-- DESKTOP: Split Screen 2-col | MOBILE: Single Column          --}}
    {{-- ============================================================ --}}
    <div class="grid min-h-screen w-full lg:grid-cols-[1.05fr_0.95fr]">

        {{-- ====================================================== --}}
        {{-- LEFT PANEL — Branding / Hero                            --}}
        {{-- ====================================================== --}}
        <div class="relative hidden overflow-hidden bg-[#f7f7f7] lg:flex">

            {{-- Chevron decorative background --}}
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.06]" viewBox="0 0 400 700" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M180 0 L400 0 L400 700 L0 700 L220 0 Z" fill="#024ad8"/>
                <path d="M230 0 L400 0 L400 700 L50 700 L270 0 Z" fill="#024ad8" opacity="0.5"/>
            </svg>

            {{-- Dot pattern top-right --}}
            <div class="pointer-events-none absolute right-8 top-8 grid grid-cols-4 gap-2 opacity-15">
                @for ($i = 0; $i < 16; $i++)
                    <div class="h-1 w-1 rounded-full bg-[#024ad8]"></div>
                @endfor
            </div>

            {{-- Content wrapper: flex column, justify between, fill height --}}
            <div class="relative z-10 flex flex-1 flex-col justify-between p-8 xl:p-12">

                {{-- Logo --}}
                <div class="flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#024ad8]">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold tracking-tight text-[#1a1a1a]">E-LEARNING</p>
                        <p class="text-[11px] text-[#636363]">Sistem Internal Perusahaan</p>
                    </div>
                </div>

                {{-- Hero text: centered vertically in remaining space --}}
                <div class="flex flex-1 flex-col justify-center">
                    <div class="max-w-md space-y-5">
                        <div>
                            <h1 class="text-3xl font-semibold leading-tight tracking-tight text-[#1a1a1a] xl:text-4xl">
                                Belajar. Berkembang.
                            </h1>
                            <h1 class="text-3xl font-semibold leading-tight tracking-tight text-[#024ad8] xl:text-4xl">
                                Berkontribusi.
                            </h1>
                        </div>
                        <div class="h-1 w-10 rounded-full bg-[#024ad8]"></div>
                        <p class="text-sm leading-relaxed text-[#636363] xl:text-base">
                            Tingkatkan kompetensi dan raih potensi terbaik bersama platform pembelajaran perusahaan.
                        </p>
                    </div>
                </div>

                {{-- Dashboard mockup: constrained, no absolute bottom --}}
                <div class="flex items-end gap-4">
                    {{-- Laptop illustration --}}
                    <div class="max-w-[420px] flex-1 xl:max-w-[480px]">
                        <div class="overflow-hidden rounded-xl border border-[#e8e8e8] bg-white shadow-lg">
                            {{-- Browser bar --}}
                            <div class="flex items-center gap-2 border-b border-[#e8e8e8] bg-[#f7f7f7] px-3 py-2">
                                <div class="flex gap-1">
                                    <div class="h-2 w-2 rounded-full bg-[#e8e8e8]"></div>
                                    <div class="h-2 w-2 rounded-full bg-[#e8e8e8]"></div>
                                    <div class="h-2 w-2 rounded-full bg-[#e8e8e8]"></div>
                                </div>
                                <div class="ml-2 flex-1 rounded bg-white px-2 py-0.5 text-[10px] text-[#c2c2c2]">dashboard.e-learning.local</div>
                            </div>
                            {{-- Dashboard body --}}
                            <div class="flex">
                                <div class="hidden w-28 shrink-0 border-r border-[#e8e8e8] bg-[#f7f7f7] p-2.5 md:block">
                                    <div class="mb-2 flex items-center gap-1">
                                        <div class="flex h-4 w-4 items-center justify-center rounded bg-[#024ad8]">
                                            <svg class="h-2.5 w-2.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
                                        </div>
                                        <span class="text-[9px] font-semibold text-[#1a1a1a]">E-LEARNING</span>
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="rounded bg-[#024ad8] px-1.5 py-0.5 text-[9px] font-medium text-white">Dashboard</div>
                                        <div class="rounded px-1.5 py-0.5 text-[9px] text-[#636363]">Training Saya</div>
                                        <div class="rounded px-1.5 py-0.5 text-[9px] text-[#636363]">Riwayat</div>
                                    </div>
                                </div>
                                <div class="flex-1 p-3">
                                    <div class="mb-2">
                                        <p class="text-[11px] font-semibold text-[#1a1a1a]">Dashboard</p>
                                        <p class="text-[9px] text-[#636363]">Selamat datang kembali!</p>
                                    </div>
                                    <div class="mb-2 grid grid-cols-3 gap-1.5">
                                        <div class="rounded border border-[#e8e8e8] p-1.5">
                                            <p class="text-[9px] text-[#636363]">Progress</p>
                                            <p class="text-xs font-semibold text-[#024ad8]">72%</p>
                                        </div>
                                        <div class="rounded border border-[#e8e8e8] p-1.5">
                                            <p class="text-[9px] text-[#636363]">Kelas Aktif</p>
                                            <p class="text-xs font-semibold text-[#1a1a1a]">4</p>
                                        </div>
                                        <div class="rounded border border-[#e8e8e8] p-1.5">
                                            <p class="text-[9px] text-[#636363]">Sertifikat</p>
                                            <p class="text-xs font-semibold text-[#1a1a1a]">12</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="overflow-hidden rounded border border-[#e8e8e8]">
                                            <div class="h-6 bg-[#c9e0fc]"></div>
                                            <div class="p-1">
                                                <p class="text-[9px] font-medium text-[#1a1a1a]">K3 & APD</p>
                                                <div class="mt-0.5 h-0.5 w-full overflow-hidden rounded-full bg-[#e8e8e8]"><div class="h-full w-[80%] rounded-full bg-[#024ad8]"></div></div>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden rounded border border-[#e8e8e8]">
                                            <div class="h-6 bg-[#dcfce7]"></div>
                                            <div class="p-1">
                                                <p class="text-[9px] font-medium text-[#1a1a1a]">Leadership</p>
                                                <div class="mt-0.5 h-0.5 w-full overflow-hidden rounded-full bg-[#e8e8e8]"><div class="h-full w-[45%] rounded-full bg-[#024ad8]"></div></div>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden rounded border border-[#e8e8e8]">
                                            <div class="h-6 bg-[#fef3c7]"></div>
                                            <div class="p-1">
                                                <p class="text-[9px] font-medium text-[#1a1a1a]">Komunikasi</p>
                                                <div class="mt-0.5 h-0.5 w-full overflow-hidden rounded-full bg-[#e8e8e8]"><div class="h-full w-[20%] rounded-full bg-[#024ad8]"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating card --}}
                    <div class="mb-4 w-40 shrink-0 rounded-xl border border-[#e8e8e8] bg-white p-3 shadow-md xl:w-44">
                        <div class="mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-[#c9e0fc]">
                            <svg class="h-4 w-4 text-[#024ad8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold leading-snug text-[#1a1a1a]">Belajar Kapan Saja, di Mana Saja</p>
                        <p class="mt-1 text-[10px] leading-relaxed text-[#636363]">Akses materi kapan pun dan di mana pun.</p>
                    </div>
                </div>
            </div>

            {{-- Dot pattern bottom-left --}}
            <div class="pointer-events-none absolute bottom-6 left-8 grid grid-cols-4 gap-2 opacity-10">
                @for ($i = 0; $i < 16; $i++)
                    <div class="h-1 w-1 rounded-full bg-[#024ad8]"></div>
                @endfor
            </div>
        </div>

        {{-- ====================================================== --}}
        {{-- RIGHT PANEL — Login Card                                --}}
        {{-- ====================================================== --}}
        <div class="flex items-center justify-center bg-white px-6 py-12 lg:px-10 xl:px-14">

            {{-- Mobile logo --}}
            <div class="absolute left-5 top-5 flex items-center gap-2 lg:hidden">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#024ad8]">
                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-[#1a1a1a]">E-LEARNING</span>
            </div>

            <div class="w-full max-w-sm">
                {{-- Login card --}}
                <div class="rounded-2xl border border-[#e8e8e8] bg-white p-7 shadow-sm sm:p-8">

                    {{-- Eyebrow --}}
                    <div class="mb-5 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#e8e8e8] bg-[#f7f7f7] px-3 py-1 text-xs font-medium text-[#636363]">
                            <svg class="h-3 w-3 text-[#024ad8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Sistem Internal Perusahaan
                        </span>
                    </div>

                    {{-- Title --}}
                    <div class="mb-6 text-center">
                        <h1 class="text-2xl font-semibold tracking-tight text-[#1a1a1a]">Login E-Learning</h1>
                        <p class="mt-1.5 text-[13px] leading-relaxed text-[#636363]">
                            Masuk sebagai Admin atau Karyawan menggunakan username dan password.
                        </p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                        @csrf

                        @error('auth')
                            <div class="rounded-lg border border-[#b3262b]/20 bg-[#f9d4d2] px-3.5 py-2.5 text-sm text-[#b3262b]">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Username --}}
                        <div class="space-y-1">
                            <label for="username" class="block text-sm font-medium text-[#1a1a1a]">Username</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-4 w-4 text-[#c2c2c2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <input
                                    id="username"
                                    name="username"
                                    type="text"
                                    value="{{ old('username') }}"
                                    placeholder="Masukkan username"
                                    required
                                    autofocus
                                    class="block w-full rounded-lg border border-[#e8e8e8] bg-white py-2.5 pl-9 pr-4 text-sm text-[#1a1a1a] placeholder:text-[#c2c2c2] transition-colors focus:border-[#024ad8] focus:outline-none focus:ring-2 focus:ring-[#024ad8]/20"
                                >
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="space-y-1">
                            <label for="password" class="block text-sm font-medium text-[#1a1a1a]">Password</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-4 w-4 text-[#c2c2c2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="Masukkan password"
                                    required
                                    class="block w-full rounded-lg border border-[#e8e8e8] bg-white py-2.5 pl-9 pr-10 text-sm text-[#1a1a1a] placeholder:text-[#c2c2c2] transition-colors focus:border-[#024ad8] focus:outline-none focus:ring-2 focus:ring-[#024ad8]/20"
                                >
                                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#c2c2c2] transition-colors hover:text-[#636363]" tabindex="-1">
                                    <svg id="eye-open" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg id="eye-closed" class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remember + Forgot --}}
                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 text-sm text-[#636363]">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#c2c2c2] text-[#024ad8] focus:ring-[#024ad8]/20">
                                Ingat saya
                            </label>
                            <a href="#" class="text-sm font-medium text-[#024ad8] transition-colors hover:text-[#296ef9]">Lupa password?</a>
                        </div>

                        {{-- Login button --}}
                        <button type="submit" class="w-full rounded-lg bg-[#024ad8] px-4 py-2.5 text-sm font-semibold tracking-wide text-white uppercase shadow-sm transition-colors hover:bg-[#296ef9] focus:outline-none focus:ring-2 focus:ring-[#024ad8] focus:ring-offset-2">
                            Login
                        </button>
                    </form>

                    {{-- Footer --}}
                    <div class="mt-5 border-t border-[#e8e8e8] pt-4 text-center">
                        <p class="flex items-center justify-center gap-1.5 text-[11px] text-[#c2c2c2]">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            Akses aman &amp; hanya untuk pengguna terdaftar
                        </p>
                    </div>
                </div>

                {{-- Mobile tagline --}}
                <div class="mt-6 text-center lg:hidden">
                    <p class="text-sm font-semibold text-[#1a1a1a]">Belajar. Berkembang. Berkontribusi.</p>
                    <p class="mt-1 text-xs text-[#636363]">Tingkatkan kompetensi bersama platform pembelajaran perusahaan.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const p = document.getElementById('password');
            const o = document.getElementById('eye-open');
            const c = document.getElementById('eye-closed');
            if (p.type === 'password') { p.type = 'text'; o.classList.add('hidden'); c.classList.remove('hidden'); }
            else { p.type = 'password'; o.classList.remove('hidden'); c.classList.add('hidden'); }
        }
    </script>
</x-layouts.guest>
