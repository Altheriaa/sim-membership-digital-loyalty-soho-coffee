@php
    $user = auth()->user();
    $isAdmin = $user->hasRole(\App\Role::Admin);

    $groups = [
        'Operasional' => [
            ['route' => 'admin.menu-items.index', 'match' => 'admin.menu-items.*', 'label' => 'Menu', 'icon' => 'coffee'],
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => 'tag'],
        ],
        'Loyalitas' => [
            ['route' => 'admin.loyalty.edit', 'match' => 'admin.loyalty.*', 'label' => 'Aturan poin', 'icon' => 'coins'],
            ['route' => 'admin.tiers.index', 'match' => 'admin.tiers.*', 'label' => 'Tier', 'icon' => 'award'],
            ['route' => 'admin.vouchers.index', 'match' => 'admin.vouchers.*', 'label' => 'Voucer', 'icon' => 'ticket'],
        ],
        'Akses' => [
            ['route' => 'admin.staff.index', 'match' => 'admin.staff.*', 'label' => 'Staf', 'icon' => 'users'],
        ],
    ];
@endphp

<april:sidebar collapsible="offcanvas" aria-label="Sidebar utama">
    <slot:header class="gap-4 p-4">
        <april:sidebar-trigger class="size-11 self-end md:hidden" aria-label="Tutup sidebar" />
        <a href="{{ route('home') }}" class="flex flex-col rounded-md px-2 py-1 outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring">
            <span class="truncate font-serif text-xl font-semibold leading-tight">{{ config('app.name') }}</span>
            <span class="text-xs text-muted-foreground">Back office</span>
        </a>
    </slot:header>

    <slot:content class="px-2">
        <nav aria-label="Navigasi utama" class="flex flex-col gap-2">
            <april:sidebar-menu>
                <april:sidebar-menu-item>
                    <april:sidebar-menu-button-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" class="h-11 gap-3 md:h-10">
                        <x-icon name="home" />
                        <span>Dashboard</span>
                    </april:sidebar-menu-button-link>
                </april:sidebar-menu-item>
            </april:sidebar-menu>

            @if ($isAdmin)
                @foreach ($groups as $heading => $links)
                    <april:sidebar-group>
                        <april:sidebar-group-label>{{ $heading }}</april:sidebar-group-label>
                        <april:sidebar-group-content>
                            <april:sidebar-menu>
                                @foreach ($links as $link)
                                    <april:sidebar-menu-item>
                                        <april:sidebar-menu-button-link href="{{ route($link['route']) }}" :active="request()->routeIs($link['match'])" class="h-11 gap-3 md:h-10">
                                            <x-icon :name="$link['icon']" />
                                            <span>{{ $link['label'] }}</span>
                                        </april:sidebar-menu-button-link>
                                    </april:sidebar-menu-item>
                                @endforeach
                            </april:sidebar-menu>
                        </april:sidebar-group-content>
                    </april:sidebar-group>
                @endforeach
            @endif
        </nav>
    </slot:content>

    <slot:footer class="border-t border-sidebar-border p-3">
        <div class="flex flex-col px-2 pb-2">
            <span class="truncate text-sm font-medium">{{ $user->name }}</span>
            <span class="text-xs text-muted-foreground">{{ $user->role->label() }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex h-11 w-full items-center gap-3 rounded-md px-2 text-sm outline-none transition-colors hover:bg-sidebar-accent focus-visible:ring-2 focus-visible:ring-sidebar-ring md:h-10">
                <x-icon name="logout" />
                <span>Keluar</span>
            </button>
        </form>
    </slot:footer>
</april:sidebar>
