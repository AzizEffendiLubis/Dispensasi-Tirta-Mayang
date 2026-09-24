<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistem Informasi Dispensasi - Masuk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --navy-900: #081b34;
            --navy-800: #0a2f5c;
            --blue-700: #145da0;
            --cyan-500: #1eb4c9;
            --cyan-300: #7fdbe8;
            --cyan-100: #d9f5f8;
            --olive-500: #74804a;
            --olive-100: #eef1e4;
            --ink: #101828;
            --muted: #667085;
            --border: #e4e9f0;
            --surface: #f7f9fc;
            --shadow-card: 0 24px 60px -16px rgba(2, 10, 24, .45);
            --shadow-btn: 0 8px 18px rgba(20, 93, 160, .28);
            --radius-lg: 24px;
            --radius-md: 10px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--surface);
            color: var(--ink);
            line-height: 1.5;
        }

        h1, h2 {
            font-family: 'Fraunces', serif;
            font-optical-sizing: auto;
        }

        a { color: inherit; }

        /* ═══════════════════════ LAYOUT ═══════════════════════ */
        .split {
            position: relative;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
            background: linear-gradient(155deg, var(--navy-900) 0%, var(--navy-800) 42%, var(--blue-700) 100%);
        }

        .bg-photo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                radial-gradient(circle at 88% 8%, rgba(30, 180, 201, .20), transparent 42%),
                radial-gradient(circle at 8% 92%, rgba(30, 180, 201, .10), transparent 38%),
                linear-gradient(165deg, rgba(6, 22, 45, .93) 0%, rgba(10, 47, 92, .68) 48%, rgba(6, 22, 45, .95) 100%);
        }

        .ring {
            position: absolute;
            top: -160px;
            right: -160px;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .10);
            z-index: 1;
        }

        .ring::before {
            content: '';
            position: absolute;
            inset: 46px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .08);
        }

        /* ─────────────── KOLOM KIRI ─────────────── */
        .split-left {
            position: relative;
            z-index: 2;
            flex: 1 1 56%;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: clamp(22px, 3.6vh, 44px) clamp(28px, 5.5vw, 88px);
        }

        .split-left-inner {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #fff;
        }

        .brand-row img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 11px;
            background: #fff;
            padding: 6px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .22);
            flex: none;
        }

        .brand-row .brand-name {
            font-size: 14.5px;
            font-weight: 800;
            letter-spacing: -.1px;
        }

        .brand-row .brand-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, .62);
            margin-top: 1px;
        }

        .tagline {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #fff;
            padding: clamp(20px, 4vh, 40px) 0;
        }

        .tagline .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            width: fit-content;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--cyan-300);
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .16);
            padding: 6px 13px;
            border-radius: 999px;
            margin-bottom: 22px;
        }

        .tagline h2 {
            font-size: clamp(26px, 2.6vw, 34px);
            font-weight: 600;
            line-height: 1.24;
            letter-spacing: -.3px;
            margin-bottom: 14px;
        }

        .tagline p {
            font-size: 14px;
            line-height: 1.75;
            color: rgba(255, 255, 255, .72);
            max-width: 400px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 13px;
            margin-top: clamp(16px, 2.6vh, 28px);
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .feature-icon {
            flex: none;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: var(--cyan-300);
            font-size: 12.5px;
        }

        .feature-text {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, .85);
        }

        .stat-row {
            display: flex;
            gap: 40px;
            margin-top: clamp(18px, 3vh, 32px);
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, .13);
        }

        .stat-item .stat-num {
            font-size: 19px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.2px;
        }

        .stat-item .stat-label {
            font-size: 11px;
            color: rgba(255, 255, 255, .55);
            margin-top: 2px;
        }

        .wave-accent {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 22px;
            overflow: hidden;
            margin-top: 28px;
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
            mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
        }

        .wave-accent svg {
            display: block;
            height: 100%;
            width: 200%;
        }

        @media (prefers-reduced-motion: no-preference) {
            .wave-accent svg {
                animation: wave-drift 26s linear infinite;
            }
        }

        @keyframes wave-drift {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }

        .split-left .footnote {
            position: relative;
            z-index: 2;
            font-size: 11.5px;
            color: rgba(255, 255, 255, .45);
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 18px;
        }

        /* ─────────────── KOLOM KANAN: FORM ─────────────── */
        .split-right {
            position: relative;
            z-index: 2;
            flex: 1 1 44%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
        }

        .card {
            width: 100%;
            max-width: 420px;
            padding: 52px 40px;
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
        }

        .card form {
            width: 100%;
        }

        .card-head {
            margin-bottom: 26px;
        }

        .card-head h1 {
            font-size: 30px;
            font-weight: 600;
            color: var(--navy-800);
            letter-spacing: -.3px;
        }

        .card-head p {
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
            margin-top: 6px;
        }

        .card-head .mobile-logo {
            display: none;
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 14px;
            margin: 0 auto 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .25);
        }

        .alert {
            padding: 11px 14px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            line-height: 1.5;
        }

        .alert i { margin-top: 1.5px; flex: none; }

        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-success { background: var(--olive-100); border: 1px solid #cdd6b4; color: #4d5a30; }
        .alert-info    { background: var(--cyan-100); border: 1px solid #a9e4ec; color: #0e6e7d; }

        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #344054;
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap .icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #98a2b3;
            font-size: 13px;
            pointer-events: none;
            transition: color .15s;
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 44px 12px 39px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 14px;
            font-family: inherit;
            color: var(--ink);
            background: #fff;
            outline: none;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }

        .input-wrap input::placeholder { color: #a2aabb; }

        .input-wrap input:focus {
            border-color: var(--blue-700);
            box-shadow: 0 0 0 3.5px rgba(20, 93, 160, .11);
        }

        .input-wrap:focus-within .icon-left {
            color: var(--blue-700);
        }

        .input-wrap input.is-invalid { border-color: #dc2626; background: #fffbfb; }
        .input-wrap input.is-invalid:focus { box-shadow: 0 0 0 3.5px rgba(220, 38, 38, .1); }

        .toggle-pw {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            cursor: pointer;
            color: #98a2b3;
            font-size: 13px;
            border-radius: 7px;
            transition: color .15s, background .15s;
        }

        .toggle-pw:hover { color: #475569; background: var(--surface); }
        .input-wrap.no-toggle input { padding-right: 14px; }

        .error-msg {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #dc2626;
            margin-top: 6px;
            font-weight: 500;
        }

        .remember-row { margin: 2px 0 22px; }

        .remember-row label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #344054;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 0;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--blue-700);
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--navy-800) 0%, var(--blue-700) 100%);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 14.5px;
            font-weight: 700;
            letter-spacing: .1px;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: var(--shadow-btn);
            transition: filter .15s, box-shadow .15s, transform .1s;
        }

        .btn-login:hover  { filter: brightness(1.08); box-shadow: 0 10px 24px rgba(20, 93, 160, .36); }
        .btn-login:active { transform: translateY(1px); box-shadow: 0 4px 12px rgba(20, 93, 160, .28); }
        .btn-login:disabled { filter: grayscale(.15) brightness(.92); cursor: not-allowed; }

        .input-wrap input:focus-visible,
        .btn-login:focus-visible,
        .toggle-pw:focus-visible,
        .remember-row input:focus-visible {
            outline: 2px solid var(--cyan-500);
            outline-offset: 2px;
        }

        .note {
            margin-top: 22px;
            padding: 12px 14px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            line-height: 1.6;
        }

        /* ─────────────── RESPONSIVE ─────────────── */
        @media (max-width: 900px) {
            .split-left { display: none; }
            .split-right { min-height: 100vh; }
            .card-head { text-align: center; }
            .card-head .mobile-logo { display: block; }
        }

        @media (max-width: 600px) {
            .card { padding: 40px 30px; }
        }

        @media (max-width: 480px) {
            .split-right { padding: 20px 14px; }
            .card { max-width: 100%; padding: 30px 22px; border-radius: 20px; }
        }
    </style>
</head>

<body>

    <div class="split">

        @if (file_exists(public_path('images/login-bg.jpg')))
            <img
                src="{{ asset('images/login-bg.jpg') }}"
                alt=""
                class="bg-photo"
                aria-hidden="true">
        @endif

        <div class="overlay"></div>
        <div class="ring" aria-hidden="true"></div>

        {{-- ==================== KOLOM KIRI ==================== --}}
        <div class="split-left">

            <div class="split-left-inner">

                <div class="brand-row">

                    @if (file_exists(public_path('images/logo1.png')))
                        <img
                            src="{{ asset('images/logo1.png') }}"
                            alt="Logo Perumdam Tirta Mayang">
                    @endif

                    <div>
                        <div class="brand-name">Perumdam Tirta Mayang</div>
                        <div class="brand-sub">Kota Jambi</div>
                    </div>

                </div>

                <div class="tagline">

                    <span class="eyebrow"><i class="fas fa-shield-halved" aria-hidden="true"></i> Portal Internal Pegawai</span>

                    <h2>Sistem Informasi Dispensasi</h2>

                    <p>
                        Dulu urus izin dispensasi harus ke HRD dan isi formulir kertas.
                        Sekarang cukup ajukan dari sini, dan pantau sendiri sampai mana prosesnya.
                    </p>

                    <div class="feature-list">
                        <div class="feature-item">
                            <span class="feature-icon"><i class="fas fa-file-circle-check" aria-hidden="true"></i></span>
                            <span class="feature-text">Isi formulir dispensasi dari mana saja, tanpa perlu ke kantor</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon"><i class="fas fa-bolt" aria-hidden="true"></i></span>
                            <span class="feature-text">Lihat langsung status pengajuan Anda, sudah sampai mana</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></span>
                            <span class="feature-text">Semua riwayat pengajuan tersimpan, tinggal dibuka lagi kapan perlu</span>
                        </div>
                    </div>

                    <div class="stat-row">
                        <div class="stat-item">
                            <div class="stat-num">24/7</div>
                            <div class="stat-label">Kapan Saja Diakses</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num">100%</div>
                            <div class="stat-label">Tanpa Kertas</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num">1</div>
                            <div class="stat-label">Sistem Terpadu</div>
                        </div>
                    </div>

                </div>

                <div class="wave-accent" aria-hidden="true">
                    <svg viewBox="0 0 1200 22" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 11 C 25 2, 50 2, 75 11 S 125 20, 150 11 S 200 2, 225 11 S 275 20, 300 11
                                 S 325 2, 350 11 S 400 20, 425 11 S 475 2, 500 11 S 550 20, 575 11 S 600 11, 600 11
                                 L 600 22 L 0 22 Z"
                              fill="rgba(127,219,232,.16)"/>
                        <path d="M0 14 C 25 6, 50 6, 75 14 S 125 22, 150 14 S 200 6, 225 14 S 275 22, 300 14
                                 S 325 6, 350 14 S 400 22, 425 14 S 475 6, 500 14 S 550 22, 575 14 S 600 14, 600 14"
                              fill="none" stroke="rgba(127,219,232,.55)" stroke-width="1.5"/>
                        <path d="M600 11 C 625 2, 650 2, 675 11 S 725 20, 750 11 S 800 2, 825 11 S 875 20, 900 11
                                 S 925 2, 950 11 S 1000 20, 1025 11 S 1075 2, 1100 11 S 1150 20, 1175 11 S 1200 11, 1200 11
                                 L 1200 22 L 600 22 Z"
                              fill="rgba(127,219,232,.16)"/>
                        <path d="M600 14 C 625 6, 650 6, 675 14 S 725 22, 750 14 S 800 6, 825 14 S 875 22, 900 14
                                 S 925 6, 950 14 S 1000 22, 1025 14 S 1075 6, 1100 14 S 1150 22, 1175 14 S 1200 14, 1200 14"
                              fill="none" stroke="rgba(127,219,232,.55)" stroke-width="1.5"/>
                    </svg>
                </div>

                <div class="footnote">
                    <i class="fas fa-copyright" aria-hidden="true"></i>
                    {{ date('Y') }} Perumdam Tirta Mayang Kota Jambi. Seluruh hak cipta dilindungi.
                </div>

            </div>

        </div>

        {{-- ==================== KOLOM KANAN: FORM LOGIN ==================== --}}
        <div class="split-right">

            <div class="card">

                <div class="card-head">

                    @if (file_exists(public_path('images/logo1.png')))
                        <img
                            src="{{ asset('images/logo1.png') }}"
                            alt="Logo Perumdam Tirta Mayang"
                            class="mobile-logo">
                    @endif

                    <h1>Masuk</h1>

                    <p>Masuk pakai email dan password akun Anda.</p>

                </div>

                {{-- ==================== FLASH / ERROR MESSAGE ====================
                     Logika disamakan dengan versi sebelumnya: satu blok error
                     tunggal yang menampilkan $errors->first(), tanpa memecah
                     berdasarkan nama field tertentu. --}}

                @if ($errors->any())
                    <div class="alert alert-error" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- ==================== FORM LOGIN ==================== --}}

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    {{-- Email --}}
                    <div class="form-group">

                        <label for="email">Email</label>

                        <div class="input-wrap no-toggle">

                            <i class="fas fa-envelope icon-left" aria-hidden="true"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username">

                        </div>

                    </div>

                    {{-- Password --}}
                    <div class="form-group">

                        <label for="password">Password</label>

                        <div class="input-wrap">

                            <i class="fas fa-lock icon-left" aria-hidden="true"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                required
                                autocomplete="current-password">

                            <button
                                type="button"
                                class="toggle-pw"
                                onclick="togglePw()"
                                aria-pressed="false"
                                aria-label="Tampilkan password">

                                <i
                                    class="fas fa-eye-slash"
                                    id="eyeIcon"
                                    aria-hidden="true">
                                </i>

                            </button>

                        </div>

                    </div>

                    {{-- Remember me --}}
                    <div class="remember-row">

                        <label>
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                @checked(old('remember'))>

                            Ingat saya
                        </label>

                    </div>

                    {{-- Tombol Login --}}
                    <button type="submit" class="btn-login" id="btnLogin">
                        <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                        <span>Masuk</span>
                    </button>

                </form>

            </div>

        </div>

    </div>

    <script>
        function togglePw() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eyeIcon');
            const btn   = document.querySelector('.toggle-pw');
            const showing = input.type === 'password';

            input.type = showing ? 'text' : 'password';
            icon.className = showing ? 'fas fa-eye' : 'fas fa-eye-slash';
            btn.setAttribute('aria-pressed', String(showing));
            btn.setAttribute('aria-label', showing ? 'Sembunyikan password' : 'Tampilkan password');
        }

        // Cegah pengiriman ganda saat form login disubmit.
        document.querySelector('form').addEventListener('submit', function () {
            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Memproses...';
        });
    </script>

</body>
</html>