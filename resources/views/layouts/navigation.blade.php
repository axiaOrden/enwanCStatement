<nav x-data="{ open: false }" class="mx-3 mt-3 rounded-[26px] border border-white/90 bg-white/90 shadow-[0_12px_38px_rgba(55,39,74,.08)] backdrop-blur-lg">
    <div class="mx-auto max-w-7xl px-3 sm:px-5">
        <div class="flex h-[74px] items-center justify-between">
            <a href="{{ route('statements.index') }}" class="flex items-center gap-3 rounded-2xl text-[#25232a] no-underline focus:outline-none focus:ring-4 focus:ring-[#6042a6]/15">
                <span class="grid h-12 w-12 -rotate-3 place-items-center rounded-[17px_17px_17px_6px] bg-[#6042a6] text-white shadow-lg shadow-[#6042a6]/20">
                    <x-application-logo class="h-7 w-7 rotate-3" />
                </span>
                <span class="grid">
                    <small class="text-[9px] font-extrabold uppercase tracking-[.1em] text-[#6e6878]">Finance workspace</small>
                    <strong class="text-lg tracking-[-.04em]">Statements</strong>
                </span>
            </a>

            <div class="hidden items-center gap-2 sm:flex">
                <a href="{{ route('statements.index') }}" class="rounded-full bg-[#f1eef5] px-4 py-3 text-xs font-bold text-[#4c4652] no-underline transition hover:bg-[#e9ddff] hover:text-[#6042a6]">Back to statements</a>
                <span class="grid h-10 w-10 place-items-center rounded-full bg-[#e9ddff] text-sm font-extrabold text-[#32176f]">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-full px-3 py-3 text-xs font-bold text-[#6e6878] transition hover:bg-[#f1eef5] hover:text-[#6042a6]">Sign out</button></form>
            </div>

            <button @click="open = !open" class="grid h-11 w-11 place-items-center rounded-full bg-[#f1eef5] text-[#6042a6] sm:hidden" aria-label="Toggle menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path x-show="!open" stroke-linecap="round" stroke-width="2" d="M5 8h14M5 16h14"/><path x-show="open" stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <div x-show="open" x-transition class="space-y-2 border-t border-[#ece7f0] py-3 sm:hidden" style="display:none">
            <a href="{{ route('statements.index') }}" class="block rounded-2xl bg-[#f1eef5] px-4 py-3 text-sm font-bold text-[#4c4652] no-underline">Back to statements</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-bold text-[#6e6878]">Sign out</button></form>
        </div>
    </div>
</nav>
