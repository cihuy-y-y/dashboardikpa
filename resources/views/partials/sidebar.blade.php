@php
    // [icon, label, nama route]
    $menuSidebar = [
        ['bi-house-door-fill',            'Beranda',      'beranda'],
        ['bi-pie-chart-fill',             'Dashboard',    'dashboard'],
        ['bi-grid-3x3-gap-fill',          'Data Satker',  'data-satker'],
        ['bi-file-earmark-arrow-up-fill', 'Upload Excel', 'upload'],
        ['bi-file-earmark-text-fill',     'Laporan',      'laporan'],
        ['bi-gear-fill',                  'Pengaturan',   'pengaturan'],
    ];

    // Menu Approval hanya tampil untuk admin
    if (auth()->user()->isAdmin()) {
        $menuSidebar[] = ['bi-person-check', 'Approval User', 'admin.approval'];
    }
@endphp

<aside class="sidebar" id="sidebar">
    <ul class="sidebar-menu list-unstyled mb-0">
        @foreach ($menuSidebar as [$icon, $label, $rute])
            <li>
                <a href="{{ route($rute) }}" class="sidebar-link {{ request()->routeIs($rute) ? 'active' : '' }}">
                    <i class="bi {{ $icon }}"></i>
                    <span>{{ $label }}</span>
                </a>
            </li>
        @endforeach

        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link text-warning w-100 border-0 bg-transparent text-start"
                    style="font:inherit;cursor:pointer;">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </li>
    </ul>
</aside>
