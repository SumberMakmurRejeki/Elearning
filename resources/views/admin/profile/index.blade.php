<x-layouts.admin title="Profil Admin - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Pengaturan User"
            title="Profil Admin"
            description="Kelola informasi profil dan password akun admin Anda."
        />

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Profil --}}
            <x-card.base class="space-y-6">
                <h3 class="text-lg font-semibold text-ink">Informasi Profil</h3>

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-form.input label="Nama" name="name" type="text" :value="old('name', $user->name)" required />
                    <x-form.input label="Username" name="username" type="text" :value="old('username', $user->username)" required />

                    <div class="pt-2">
                        <x-button.primary type="submit">Simpan Profil</x-button.primary>
                    </div>
                </form>
            </x-card.base>

            {{-- Ubah Password --}}
            <x-card.base class="space-y-6">
                <h3 class="text-lg font-semibold text-ink">Ubah Password</h3>

                <form method="POST" action="{{ route('admin.profile.update-password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-form.input label="Password Lama" name="current_password" type="password" autocomplete="current-password" />
                    <x-form.input label="Password Baru" name="new_password" type="password" autocomplete="new-password" help="Minimal 8 karakter." />
                    <x-form.input label="Konfirmasi Password Baru" name="new_password_confirmation" type="password" autocomplete="new-password" />

                    <div class="pt-2">
                        <x-button.primary type="submit">Ubah Password</x-button.primary>
                    </div>
                </form>
            </x-card.base>
        </div>
    </div>
</x-layouts.admin>
