<header class="sticky top-0 z-30 border-b border-fog bg-white/90 backdrop-blur">
    @php($user = auth()->user())
    <div class="flex items-center justify-between gap-4 px-4 py-4 md:px-6 xl:px-8">
        <div class="flex items-center gap-3">
            <button type="button" class="rounded-xl border border-fog px-3 py-2 text-sm lg:hidden" data-toggle-sidebar="#admin-sidebar">Menu</button>
            <div>
                <p class="text-xs font-medium text-graphite">Role Admin</p>
                <h2 class="text-lg font-semibold text-ink">Dashboard Admin</h2>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-semibold text-ink">{{ $user?->name ?? 'Admin Training' }}</p>
                <p class="text-xs text-graphite">{{ $user?->role ?? 'admin' }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">{{ collect(explode(' ', $user?->name ?? 'AT'))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button.outline type="submit">Logout</x-button.outline>
            </form>
        </div>
    </div>
</header>
