@php($user = auth()->user())
@php($initials = collect(explode(' ', $user?->name ?? 'AT'))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''))
@php($title = $title ?? 'Dashboard Admin')
@php($segments = array_filter(explode(' - ', $title)))
@php($breadcrumb = count($segments) > 1 ? $segments[0] . ' › ' . $segments[1] : ($segments[0] ?? 'Dashboard Admin'))

<header class="sticky top-0 z-30 border-b border-[#e8e8e8] bg-white">
    <div class="flex items-center justify-between gap-4 px-6 py-3.5 md:px-8 xl:px-10">
        {{-- Left: breadcrumb --}}
        <div class="flex items-center gap-3">
            <button type="button" class="rounded-lg border border-[#e8e8e8] px-3 py-2 text-sm lg:hidden" data-toggle-sidebar="#admin-sidebar">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div>
                <p class="text-xs font-medium text-[#636363]">Role Admin</p>
                <h2 class="text-sm font-semibold text-[#1a1a1a]">{{ $breadcrumb }}</h2>
            </div>
        </div>

        {{-- Right: profile --}}
        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-semibold text-[#1a1a1a]">{{ $user?->name ?? 'Admin Training' }}</p>
                <p class="text-[11px] text-[#636363]">{{ $user?->role ?? 'admin' }}</p>
            </div>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#024ad8] text-xs font-semibold text-white">{{ $initials }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-[#e8e8e8] bg-white px-3 py-2 text-xs font-medium text-[#636363] transition hover:border-[#024ad8] hover:text-[#024ad8] focus:outline-none focus:ring-2 focus:ring-[#024ad8] focus:ring-offset-2">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</header>
