<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SI Dispensasi – @yield('title', 'Sistem Informasi Dispensasi')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:   { DEFAULT: '#0a2f5c', dark: '#081b34', light: '#d9f5f8' },
                        accent:    { DEFAULT: '#1eb4c9', soft: '#d9f5f8' },
                        green:     { DEFAULT: '#009933', dark: '#007529', soft: '#E1F7E7' },
                        secondary: { DEFAULT: '#009933', soft: '#E1F7E7' },
                        ink:       { DEFAULT: '#101828', soft: '#667085' },
                        canvas: '#f7f9fc',
                        line: '#e4e9f0',
                    },
                    fontFamily: {
                        display: ['Fraunces', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                    },
                    borderRadius: { xl2: '1rem' },
                    boxShadow: {
                        card: '0 1px 3px rgba(10,47,92,.08), 0 2px 8px rgba(10,47,92,.06)',
                    },
                }
            }
        }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --sidebar-w: 264px;
            --topbar-h: 68px;
        }
        [x-cloak] { display: none !important; }
        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f7f9fc;
            color: #101828;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1eb4c9; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #0a2f5c; }

        /* ── Ambient background — soft water-toned glows, fixed behind everything ── */
        .bg-decor { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .bg-decor::before, .bg-decor::after { content: ''; position: absolute; border-radius: 50%; filter: blur(70px); }
        .bg-decor::before {
            width: 480px; height: 480px; top: -200px; right: -160px;
            background: rgba(30,180,201,.14);
        }
        .bg-decor::after {
            width: 400px; height: 400px; bottom: -180px; left: -140px;
            background: rgba(0,153,51,.09);
        }

        /* ── Component classes ── */
        .card {
            position: relative;
            background: #fff;
            border: 1px solid #e4e9f0;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(22,58,92,0.05);
            overflow: hidden;
            transition: box-shadow .2s ease, border-color .2s ease, transform .2s ease;
        }
        /* Signature: a thin "waterline" that rises across the card on hover */
        .card::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 3px;
            background: #1eb4c9;
            transform: scaleX(0); transform-origin: left; transition: transform .3s ease;
        }
        .card-hover:hover { box-shadow: 0 8px 24px rgba(10,47,92,.10); border-color: #c9dcea; transform: translateY(-1px); }
        .card-hover:hover::after { transform: scaleX(1); }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .375rem;
            padding: .6rem 1.15rem; border-radius: .65rem; font-size: .875rem; font-weight: 600;
            transition: background-color .15s, background .15s, border-color .15s, color .15s, transform .1s, box-shadow .15s;
            border: none; cursor: pointer; font-family: inherit;
        }
        .btn:active { transform: scale(.97); }
        .btn-primary { background: #0a2f5c; color: #fff; box-shadow: 0 1px 2px rgba(10,47,92,.25), 0 4px 10px rgba(10,47,92,.16); }
        .btn-primary:hover { background: #081b34; box-shadow: 0 2px 6px rgba(10,47,92,.3), 0 8px 18px rgba(10,47,92,.22); }
        .btn-outline { background: #fff; color: #081b34; border: 1px solid #e4e9f0; }
        .btn-outline:hover { background: #F5F8FA; border-color: #CBD8E1; }
        .btn-danger { background: #C1483A; color: #fff; }
        .btn-danger:hover { background: #a53c30; }
        .btn-sm { padding: .38rem .8rem; font-size: .75rem; }

        .field-label { display: block; font-size: .8rem; font-weight: 600; color: #101828; margin-bottom: .35rem; }
        .field-input { width: 100%; border: 1px solid #e4e9f0; border-radius: .65rem; padding: .65rem .85rem; font-size: .9rem; background: #fff; color: #101828; transition: border-color .15s, box-shadow .15s; }
        .field-input:focus { outline: none; border-color: #0a2f5c; box-shadow: 0 0 0 3.5px #d9f5f8; }
        .field-error { color: #C1483A; font-size: .8rem; margin-top: .3rem; }

        {{-- Class disamakan dengan nilai kolom dispensasis.status_pengajuan
             (menunggu_persetujuan/disetujui/ditolak), dipendekkan jadi
             menunggu/disetujui/ditolak — sama seperti yang dipakai di
             dashboard.sdm. --}}
        .badge {
            display: inline-flex; align-items: center; gap: .35rem; padding: .28rem .75rem;
            border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap;
            letter-spacing: .01em; border: 1px solid transparent;
        }
        .badge::before { content: ''; width: .4rem; height: .4rem; border-radius: 999px; background: currentColor; box-shadow: 0 0 0 3px currentColor; opacity: .18; }
        .badge-menunggu  { background: #FCEACB; color: #9A6011; border-color: rgba(154,96,17,.12); }
        .badge-disetujui { background: #D7F5E0; color: #007529; border-color: rgba(0,117,41,.12); }
        .badge-ditolak   { background: #FAD9D3; color: #A3392C; border-color: rgba(163,57,44,.12); }
        .badge-default   { background: #d9f5f8; color: #0a2f5c; border-color: rgba(10,47,92,.12); }

        .table-pro { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .875rem; }
        .table-pro thead th { text-align: left; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #0a2f5c; background: #d9f5f8; padding: .8rem 1.1rem; }
        .table-pro thead th:first-child { border-top-left-radius: .75rem; }
        .table-pro thead th:last-child { border-top-right-radius: .75rem; }
        .table-pro tbody td { padding: .85rem 1.1rem; border-top: 1px solid #e4e9f0; }
        .table-pro tbody tr { transition: background-color .12s ease; }
        .table-pro tbody tr:hover { background: #F6FAFD; }
        .mono-data { font-family: 'IBM Plex Mono', monospace; font-size: .82rem; color: #081b34; }

        /* ── Table scroll wrapper — biar tabel lebar/panjang bisa digeser
             ke samping di layar sempit, tanpa merusak layout sidebar.
             Pakai: <div class="table-scroll-wrapper"><table class="table-pro">...</table></div> ── */
        .table-scroll-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: .75rem;
            border: 1px solid #e4e9f0;
        }
        .table-scroll-wrapper .table-pro {
            min-width: 640px;
        }
        .table-scroll-wrapper::-webkit-scrollbar { height: 6px; }
        .table-scroll-wrapper::-webkit-scrollbar-thumb { background: #1eb4c9; border-radius: 99px; }

        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid #0a2f5c; outline-offset: 2px; border-radius: 4px;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: #fff;
            border-right: 1px solid #e4e9f0;
            box-shadow: 2px 0 12px 0 rgba(10,47,92,.06);
            position: fixed;
            top: 0; left: 0;
            height: 100vh; height: 100dvh;
            display: flex; flex-direction: column;
            z-index: 150;
            transform: translateX(-100%);
            transition: transform .3s cubic-bezier(.4,0,.2,1);
        }
        .sidebar.open { transform: translateX(0); box-shadow: 0 8px 32px rgba(0,0,0,.14); }
        @media (min-width: 1024px) { .sidebar { transform: translateX(0); } }

        .sidebar-overlay {
            display: none; position: fixed; inset: 0; background: rgba(11,26,46,.55);
            z-index: 149; backdrop-filter: blur(2px);
            opacity: 0; transition: opacity .25s ease;
        }
        .sidebar-overlay.open { display: block; opacity: 1; }

        /* Brand header — subtle wave divider ties the mark to the "Tirta" (water) identity */
        .sidebar-brand { position: relative; overflow: hidden; }
        .sidebar-brand::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: 8px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 8' preserveAspectRatio='none'%3E%3Cpath d='M0 8 Q 25 0 50 8 T 100 8 V8 H0 Z' fill='%231eb4c9' fill-opacity='0.30'/%3E%3C/svg%3E");
            background-size: 100% 100%;
        }

        .nav-link {
            display: flex; align-items: center; gap: .7rem;
            padding: .62rem .75rem; border-radius: .65rem;
            font-size: .84rem; font-weight: 500; color: #667085;
            transition: background-color .15s, background .15s, color .15s, transform .1s;
            position: relative;
        }
        .nav-link:hover { background: #f7f9fc; color: #0a2f5c; }
        .nav-link:active { transform: scale(.98); }
        .nav-link.active { background: #d9f5f8; color: #0a2f5c; font-weight: 700; }
        .nav-link.active::before {
            content: ''; position: absolute; left: -.6rem; top: 18%; bottom: 18%;
            width: 3px; background: #0a2f5c; border-radius: 0 3px 3px 0;
        }
        .nav-link i { width: 18px; text-align: center; font-size: .82rem; }
        .nav-link.active i { color: #0a2f5c; }
        .nav-section-label {
            font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em;
            color: #5C7A9E; padding: .9rem .75rem .35rem;
        }

        /* ── Main / Topbar ── */
        .main-wrapper { position: relative; z-index: 1; margin-left: 0; padding-top: var(--topbar-h); min-height: 100vh; transition: margin-left .3s cubic-bezier(.4,0,.2,1); }
        @media (min-width: 1024px) { .main-wrapper { margin-left: var(--sidebar-w); } }

        .topbar {
            height: var(--topbar-h);
            position: fixed; top: 0; left: 0; right: 0;
            background: #0a2f5c;
            box-shadow: 0 1px 2px rgba(8,27,52,.15), 0 6px 18px rgba(8,27,52,.16), inset 0 -2px 0 0 #1eb4c9;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1rem;
            z-index: 100;
        }
        .topbar .page-title { color: #fff; font-family: 'Fraunces', serif; font-weight: 600; letter-spacing: .01em; }
        .topbar-icon-btn {
            background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); color: #fff;
        }
        .topbar-icon-btn:hover { background: rgba(255,255,255,.24); }
        @media (min-width: 1024px) { .topbar { left: var(--sidebar-w); padding: 0 1.75rem; } }

        /* ── Small utility animations ── */
        @keyframes fadeSlideIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeSlideIn .18s ease-out; }

        main { position: relative; z-index: 1; animation: fadeSlideIn .25s ease-out; }
    </style>

    <script>
       document.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;

            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.dataset.submitted === 'true') { e.preventDefault(); return; }
            form.dataset.submitted = 'true';
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (btn) {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memproses...';
            });
        }, false);
    </script>
    @stack('styles')
</head>
<body class="min-h-screen" x-data="{ sidebarOpen: false }">

<div class="bg-decor"></div>

@auth
    @php
        $dashboardUrl = auth()->user()->dashboardRoute();

        // Dulu daftar ini berisi satu nama route dashboard per role
        // (dashboard.manajer, dashboard.kepala-spi, dst.) karena role
        // di-hardcode per jabatan. Sekarang semua approver berbagi SATU
        // dashboard generik (dashboard.approver), jadi daftarnya cukup
        // dua entri saja.
        $rutePersetujuan = [
            'dashboard.approver',
            'approval.*',
        ];
    @endphp

    <div class="sidebar-overlay" :class="{ 'open': sidebarOpen }" @click="sidebarOpen = false"></div>

    <aside class="sidebar" :class="{ 'open': sidebarOpen }">

        <div class="sidebar-brand flex items-center gap-3 px-4 border-b border-line" style="height: var(--topbar-h); flex-shrink:0; background: #d9f5f8;">
            @if (file_exists(public_path('images/logo1.png')))
                <img src="{{ asset('images/logo1.png') }}" alt="Logo Tirta Mayang" class="h-10 w-auto object-contain flex-shrink-0">
            @else
                <div class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: #0a2f5c; box-shadow: 0 2px 6px rgba(10,47,92,.35);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C12 2 5 10.5 5 15C5 18.866 8.134 22 12 22C15.866 22 19 18.866 19 15C19 10.5 12 2 12 2Z" fill="#fff"/>
                    </svg>
                </div>
            @endif
            <div class="leading-tight min-w-0">
                <p class="text-primary font-bold text-sm tracking-wide truncate">TIRTA MAYANG</p>
                <p class="text-accent text-[11px] font-medium truncate">SI Dispensasi</p>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden ml-auto h-8 w-8 rounded-lg text-ink-soft hover:bg-canvas flex items-center justify-center flex-shrink-0">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-3" role="navigation" aria-label="Menu utama">

        <div class="nav-section-label">Menu Utama</div>

        @if (auth()->user()->isAdminSdm() && Route::has('sdm.dashboard'))
        <a href="{{ route('sdm.dashboard') }}" class="nav-link {{ request()->routeIs('sdm.dashboard') ? 'active' : '' }}">
            <i class="fas fa-gauge-high"></i><span>Dashboard</span>
        </a>
        @endif
            
            @if (auth()->user()->isAdminDepartemen() && Route::has('dispensasi.index'))
            <a href="{{ route('dispensasi.index') }}" class="nav-link {{ request()->routeIs(['dispensasi.index', 'dispensasi.show']) ? 'active' : '' }}">
                <i class="fas fa-file-lines"></i><span>Riwayat Dispensasi</span>
            </a>
            @endif
            
            @if (auth()->user()->isAdminDepartemen() && Route::has('dispensasi.create'))
            <a href="{{ route('dispensasi.create') }}" class="nav-link {{ request()->routeIs('dispensasi.create') ? 'active' : '' }}">
                <i class="fas fa-file-circle-plus"></i><span>Ajukan Dispensasi</span>
            </a>
            @endif

            @if (auth()->user()->isPemberiKeputusan())
            <a href="{{ $dashboardUrl }}" class="nav-link {{ request()->routeIs($rutePersetujuan) ? 'active' : '' }}">
                <i class="fas fa-square-check"></i><span>Persetujuan</span>
            </a>
            @endif

            @if (auth()->user()->isAdminSdm())
            <div class="nav-section-label">Administrasi</div>

            @if (Route::has('sdm.pegawai.index'))
            <a href="{{ route('sdm.pegawai.index') }}" class="nav-link {{ request()->routeIs('sdm.pegawai.*') ? 'active' : '' }}">
                <i class="fas fa-user-group"></i><span>Kelola Pegawai</span>
            </a>
            @endif

            @if (Route::has('sdm.monitoring.index'))
            <a href="{{ route('sdm.monitoring.index') }}" class="nav-link {{ request()->routeIs('sdm.monitoring.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i><span>Monitoring Dispensasi</span>
            </a>
            @endif

            @if (Route::has('sdm.arsip-e-dispensasi.index'))
            <a href="{{ route('sdm.arsip-e-dispensasi.index') }}" class="nav-link {{ request()->routeIs('sdm.arsip-e-dispensasi.*') ? 'active' : '' }}">
                <i class="fas fa-box-archive"></i><span>Arsip E-Dispensasi</span>
            </a>
            @endif

            @if (Route::has('sdm.pengguna.index'))
            <a href="{{ route('sdm.pengguna.index') }}" class="nav-link {{ request()->routeIs('sdm.pengguna.*') ? 'active' : '' }}">
                <i class="fas fa-users-gear"></i><span>Manajemen Pengguna</span>
            </a>
            @endif

            <div class="nav-section-label">Master Data</div>

            @if (Route::has('sdm.jabatan.index'))
            <a href="{{ route('sdm.jabatan.index') }}" class="nav-link {{ request()->routeIs('sdm.jabatan.*') ? 'active' : '' }}">
                <i class="fas fa-id-badge"></i><span>Jabatan</span>
            </a>
            @endif

            @if (Route::has('sdm.unit-organisasi.index'))
            <a href="{{ route('sdm.unit-organisasi.index') }}" class="nav-link {{ request()->routeIs('sdm.unit-organisasi.*') ? 'active' : '' }}">
                <i class="fas fa-sitemap"></i><span>Unit Organisasi</span>
            </a>
            @endif

            @if (Route::has('sdm.alur-approval.index'))
            <a href="{{ route('sdm.alur-approval.index') }}" class="nav-link {{ request()->routeIs('sdm.alur-approval.*') ? 'active' : '' }}">
                <i class="fas fa-route"></i><span>Alur Approval</span>
            </a>
            @endif
            @endif

        </nav>

        <div class="px-3 py-3 border-t border-line" style="flex-shrink:0;">
            <div class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 mb-2" style="background: #d9f5f8; border: 1px solid rgba(10,47,92,.10);">
                <div class="h-9 w-9 rounded-lg text-white text-xs font-bold flex items-center justify-center flex-shrink-0" style="background: #0a2f5c;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-ink truncate">{{ Str::limit(auth()->user()->name, 20) }}</p>
                    <p class="text-[11px] text-ink-soft capitalize truncate">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                </div>
            </div>

            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
            </form>
            <div x-data="{ confirmOpen: false }">
                <button type="button" @click="confirmOpen = true" class="nav-link w-full text-left" style="color:#C1483A;">
                    <i class="fas fa-right-from-bracket"></i><span class="font-semibold">Log Out</span>
                </button>

                <template x-teleport="body">
                    <div x-show="confirmOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         class="fixed inset-0 z-[300] flex items-center justify-center p-4"
                         style="background: rgba(15,23,42,.48); backdrop-filter: blur(2px);">
                        <div @click.outside="confirmOpen = false"
                             x-show="confirmOpen"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="card w-full max-w-sm p-6 text-center">
                            <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #FBE7E4; color:#C1483A;">
                                <i class="fas fa-right-from-bracket"></i>
                            </div>
                            <h3 class="font-bold text-ink mb-1">Keluar dari sistem?</h3>
                            <p class="text-sm text-ink-soft mb-5">Sesi Anda akan diakhiri. Anda perlu login kembali untuk melanjutkan.</p>
                            <div class="flex gap-2 justify-center">
                                <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                <button type="submit" form="logout-form" class="btn btn-danger">Ya, Keluar</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </aside>
@endauth

{{-- ══════════════════════════════════════
     MAIN WRAPPER
══════════════════════════════════════ --}}
<div class="{{ auth()->check() ? 'main-wrapper' : '' }}">

    @auth
    <header class="topbar">
        <div class="flex items-center gap-3 min-w-0">
            <button @click="sidebarOpen = !sidebarOpen" class="topbar-icon-btn lg:hidden h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bars"></i>
            </button>
            <span class="page-title font-bold text-base truncate">@yield('page-title', 'Dashboard')</span>
        </div>

        <div class="flex items-center gap-3 sm:gap-4">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="topbar-icon-btn relative transition-colors h-9 w-9 rounded-lg flex items-center justify-center">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                    @if (auth()->user()->unreadNotifications->count() > 0)
                    <span class="absolute -top-1.5 -right-1.5 bg-accent text-primary-dark text-[10px] font-bold rounded-full px-1.5 py-0.5 ring-2 ring-white">
                        {{ auth()->user()->unreadNotifications->count() }}
                    </span>
                    @endif
                </button>

                <div x-show="open" @click.outside="open = false" x-cloak
                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    class="absolute right-0 mt-3 w-80 bg-white border border-line rounded-xl2 shadow-lg z-20 max-h-96 overflow-y-auto">
                    <div class="px-4 py-3 border-b border-line font-semibold text-sm text-primary bg-primary-light sticky top-0 flex items-center justify-between">
                        <span>Notifikasi</span>
                        @if (auth()->user()->unreadNotifications->count() > 0 && Route::has('notifikasi.markAllRead'))
                        <form method="POST" action="{{ route('notifikasi.markAllRead') }}">
                            @csrf
                            <button type="submit" class="text-xs font-normal text-accent hover:underline">
                                Tandai semua terbaca
                            </button>
                        </form>
                        @endif
                    </div>
                    @forelse (auth()->user()->notifications->take(6) as $notif)
                    <a href="{{ Route::has('notifikasi.buka') ? route('notifikasi.buka', $notif) : ($notif->data['url'] ?? '#') }}"
                       class="relative block px-4 py-3 pl-5 border-b border-line text-sm hover:bg-canvas transition-colors {{ $notif->read_at ? 'text-ink-soft' : 'text-ink font-medium' }}">
                        @unless ($notif->read_at)
                        <span class="absolute left-0 top-0 bottom-0 w-[3px]" style="background: #0a2f5c;"></span>
                        @endunless
                        {{ $notif->data['pesan'] ?? 'Notifikasi baru' }}
                        <div class="text-ink-soft text-xs mt-1 font-normal">{{ $notif->created_at->diffForHumans() }}</div>
                    </a>
                    @empty
                    <div class="px-4 py-8 text-sm text-ink-soft text-center">
                        <i class="fas fa-bell-slash text-lg mb-2 block opacity-40"></i>
                        Belum ada notifikasi.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-2.5 pl-3" style="border-left: 2px solid rgba(255,255,255,.22);">
                <div class="h-8 w-8 rounded-md text-primary text-xs font-bold flex items-center justify-center" style="background: #fff; box-shadow: 0 0 0 2px rgba(30,180,201,.35);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="leading-tight">
                    <p class="text-white text-xs font-semibold">{{ Str::limit(auth()->user()->name, 18) }}</p>
                    <p class="text-[11px] capitalize" style="color: #9FE0FF;">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                </div>
            </div>
        </div>
    </header>
    @endauth

    <main class="max-w-6xl mx-auto px-4 lg:px-6 py-8">
        @if (session('success'))
            <div class="mb-6 pl-3 pr-4 py-3 rounded-xl2 text-sm font-medium flex items-center gap-3 animate-in" style="background: #E1F7E7; border: 1px solid rgba(0,153,51,.35); border-left: 4px solid #009933; color: #007529;">
                <span class="h-7 w-7 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(0,153,51,.14);"><i class="fas fa-circle-check"></i></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 pl-3 pr-4 py-3 rounded-xl2 text-sm flex items-center gap-3 animate-in" style="background: #FAD9D3; border: 1px solid rgba(193,72,58,.35); border-left: 4px solid #C1483A; color: #8a352a;">
                <span class="h-7 w-7 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(193,72,58,.14);"><i class="fas fa-circle-xmark"></i></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if (session('warning'))
            <div class="mb-6 pl-3 pr-4 py-3 rounded-xl2 text-sm flex items-center gap-3 animate-in" style="background: #FCEACB; border: 1px solid rgba(200,134,43,.35); border-left: 4px solid #C8862B; color: #8a5f1e;">
                <span class="h-7 w-7 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(200,134,43,.14);"><i class="fas fa-triangle-exclamation"></i></span>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @yield('content')
    </main>
</div>

</body>
</html>