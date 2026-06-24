<x-layouts.guest title="Login - {{ config('app.name') }}">
    <x-card.base class="w-full max-w-md">
        <div class="mb-8 text-center">
            <p class="text-sm font-medium text-graphite">Sistem Internal Perusahaan</p>
            <h1 class="mt-2 text-2xl font-semibold text-ink">Login E-Learning</h1>
            <p class="mt-2 text-sm text-charcoal">Masuk sebagai Admin atau Karyawan menggunakan username dan password.</p>
        </div>

        <form class="space-y-5">
            <x-form.input label="Username" name="username" placeholder="Masukkan username" />
            <x-form.input label="Password" name="password" type="password" placeholder="Masukkan password" />

            <x-alert variant="danger" title="Preview UI">
                Halaman ini baru layout foundation. Logic auth dikerjakan di epic berikutnya.
            </x-alert>

            <x-button.primary type="submit" class="w-full">Login</x-button.primary>
        </form>
    </x-card.base>
</x-layouts.guest>
