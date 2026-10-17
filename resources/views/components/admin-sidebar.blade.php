@php
    $user = auth()->user();
    $isAdmin = $user->hasRole(\App\Role::Admin);
    $isOwner = $user->hasRole(\App\Role::Owner);

    $groups = [
        'Operasional' => array_values(array_filter([
            ['route' => 'pos.index', 'match' => 'pos.*', 'label' => 'Kasir (POS)', 'icon' => 'monitor', 'allowed' => $isAdmin],
            ['route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'label' => 'Laporan', 'icon' => 'bar-chart-3', 'allowed' => true],
            ['route' => 'admin.menu-items.index', 'match' => 'admin.menu-items.*', 'label' => 'Menu', 'icon' => 'coffee', 'allowed' => $isAdmin],
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => 'tag', 'allowed' => $isAdmin],
        ], fn($item) => $item['allowed'])),
        'Loyalitas' => array_values(array_filter([
            ['route' => 'admin.members.index', 'match' => 'admin.members.*', 'label' => 'Member', 'icon' => 'user-check', 'allowed' => $isAdmin],
            ['route' => 'admin.loyalty.edit', 'match' => 'admin.loyalty.*', 'label' => 'Aturan poin', 'icon' => 'coins', 'allowed' => $isAdmin],
            ['route' => 'admin.tiers.index', 'match' => 'admin.tiers.*', 'label' => 'Tier', 'icon' => 'award', 'allowed' => $isAdmin],
            ['route' => 'admin.vouchers.index', 'match' => 'admin.vouchers.*', 'label' => 'Voucer', 'icon' => 'ticket', 'allowed' => $isAdmin],
        ], fn($item) => $item['allowed'])),
        'Akses' => array_values(array_filter([
            ['route' => 'admin.staff.index', 'match' => 'admin.staff.*', 'label' => 'Staf', 'icon' => 'users', 'allowed' => $isAdmin],
        ], fn($item) => $item['allowed'])),
    ];
@endphp

<april:sidebar collapsible="icon" aria-label="Sidebar utama">
    <slot:header class="gap-2 p-3 group-data-[collapsible=icon]:p-2.5 group-data-[collapsible=icon]:items-center">
        <april:sidebar-trigger class="size-11 self-end md:hidden" aria-label="Tutup sidebar" />
        <a href="{{ route('home') }}"
            class="flex items-center gap-3 rounded-xl px-2 py-1.5 outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0"
            title="{{ config('app.name') }}">
            <span
                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-[#1c325c] p-1.5 shadow-xs ring-1 ring-black/10 dark:ring-white/10 group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-2">
                <img src="{{ asset('icons/Logo.png') }}" alt="{{ config('app.name') }}" class="size-full object-contain">
            </span>
            <div class="flex flex-col group-data-[collapsible=icon]:hidden min-w-0">
                <span class="truncate font-serif text-base font-semibold leading-tight">{{ config('app.name') }}</span>
                <span class="text-[10px] text-muted-foreground">Coffee & Eatery</span>
            </div>
        </a>
    </slot:header>

    <slot:content class="px-2 group-data-[collapsible=icon]:px-1.5">
        <nav aria-label="Navigasi utama" class="flex flex-col gap-1.5">
            <april:sidebar-menu class="gap-1.5">
                <april:sidebar-menu-item>
                    <april:sidebar-menu-button-link href="{{ route('admin.dashboard') }}"
                        :active="request()->routeIs('admin.dashboard')" title="Dashboard"
                        class="h-11 gap-3 group-data-[collapsible=icon]:size-11! group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0! group-data-[collapsible=icon]:mx-auto group-data-[collapsible=icon]:rounded-xl [&>svg]:size-5! group-data-[collapsible=icon]:[&>svg]:size-5.5!">
                        <x-lucide-home class="size-5 shrink-0 group-data-[collapsible=icon]:size-5.5" />
                        <span>Dashboard</span>
                    </april:sidebar-menu-button-link>
                </april:sidebar-menu-item>
            </april:sidebar-menu>

            @foreach ($groups as $heading => $links)
                @if (count($links) > 0)
                    <april:sidebar-group class="p-1 group-data-[collapsible=icon]:p-0">
                        <div class="hidden my-2 mx-auto h-px w-8 bg-border/70 group-data-[collapsible=icon]:block"
                            aria-hidden="true"></div>
                        <april:sidebar-group-label
                            class="group-data-[collapsible=icon]:hidden">{{ $heading }}</april:sidebar-group-label>
                        <april:sidebar-group-content>
                            <april:sidebar-menu class="gap-1.5">
                                @foreach ($links as $link)
                                    <april:sidebar-menu-item>
                                        <april:sidebar-menu-button-link href="{{ route($link['route']) }}"
                                            :active="request()->routeIs($link['match'])" :title="$link['label']"
                                            class="h-11 gap-3 group-data-[collapsible=icon]:size-11! group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0! group-data-[collapsible=icon]:mx-auto group-data-[collapsible=icon]:rounded-xl [&>svg]:size-5! group-data-[collapsible=icon]:[&>svg]:size-5.5!">
                                            <x-dynamic-component :component="'lucide-' . $link['icon']"
                                                class="size-5 shrink-0 group-data-[collapsible=icon]:size-5.5" />
                                            <span>{{ $link['label'] }}</span>
                                        </april:sidebar-menu-button-link>
                                    </april:sidebar-menu-item>
                                @endforeach
                            </april:sidebar-menu>
                        </april:sidebar-group-content>
                    </april:sidebar-group>
                @endif
            @endforeach
        </nav>
    </slot:content>

    <slot:footer class="border-t border-sidebar-border p-2.5 group-data-[collapsible=icon]:p-2 flex flex-col gap-1.5">
        <div class="flex flex-col px-2 pb-1 group-data-[collapsible=icon]:hidden">
            <span class="truncate text-sm font-medium">{{ $user->name }}</span>
            <span class="text-xs text-muted-foreground">{{ $user->role->label() }}</span>
        </div>

        <button type="button" x-on:click="sidebar.toggle()"
            class="flex h-11 w-full items-center gap-3 rounded-xl px-2.5 text-sm text-muted-foreground transition-colors hover:bg-sidebar-accent hover:text-sidebar-foreground group-data-[collapsible=icon]:size-11! group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0! group-data-[collapsible=icon]:mx-auto group-data-[collapsible=icon]:rounded-xl [&>svg]:size-5! group-data-[collapsible=icon]:[&>svg]:size-5.5!"
            :title="sidebar.open ? 'Kecilkan sidebar' : 'Perluas sidebar'">
            <template x-if="sidebar.open">
                <x-lucide-panel-left-close class="size-5 shrink-0 group-data-[collapsible=icon]:size-5.5" />
            </template>
            <template x-if="!sidebar.open">
                <x-lucide-panel-left-open class="size-5 shrink-0 group-data-[collapsible=icon]:size-5.5" />
            </template>
            <span class="group-data-[collapsible=icon]:hidden"
                x-text="sidebar.open ? 'Kecilkan sidebar' : 'Perluas sidebar'"></span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Keluar"
                class="flex h-11 w-full items-center gap-3 rounded-xl px-2.5 text-sm text-destructive/80 transition-colors hover:bg-destructive/10 hover:text-destructive group-data-[collapsible=icon]:size-11! group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0! group-data-[collapsible=icon]:mx-auto group-data-[collapsible=icon]:rounded-xl [&>svg]:size-5! group-data-[collapsible=icon]:[&>svg]:size-5.5!">
                <x-lucide-log-out class="size-5 shrink-0 group-data-[collapsible=icon]:size-5.5" />
                <span class="group-data-[collapsible=icon]:hidden">Keluar</span>
            </button>
        </form>
    </slot:footer>

    <april:sidebar-rail />
</april:sidebar>