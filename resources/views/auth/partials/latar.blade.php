{{-- Latar bersama halaman Login & Register: titik, gelombang, logo, sambutan, foto gedung --}}
    {{-- Dekorasi: titik-titik --}}
    <div class="dots dots--top"></div>
    <div class="dots dots--right"></div>
    <div class="dots dots--left"></div>

    {{-- Dekorasi: gelombang biru (kanan atas & bawah) --}}
    <svg class="waves" viewBox="0 0 1672 941" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <linearGradient id="gBright" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#2f8bff"/>
                <stop offset="1" stop-color="#0a55d8"/>
            </linearGradient>
            <linearGradient id="gNavy" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#0a4bc4"/>
                <stop offset="1" stop-color="#012a6e"/>
            </linearGradient>
            <linearGradient id="gPale" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#e9f2ff"/>
                <stop offset="1" stop-color="#bcd6fb"/>
            </linearGradient>
            <linearGradient id="gHill" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#9ec9ff" stop-opacity=".9"/>
                <stop offset="1" stop-color="#eef5ff" stop-opacity=".95"/>
            </linearGradient>
        </defs>

        {{-- ===== Kanan atas ===== --}}
        <path d="M900 330 C1050 190 1250 100 1420 108 C1540 114 1640 160 1672 190 L1672 400 Z" fill="url(#gHill)"/>
        <path d="M900 235 C1000 175 1100 70 1165 0 L1240 0 C1180 85 1090 170 1015 212 Z" fill="#b4d3ff" opacity=".85"/>
        <path d="M1015 212 C1110 160 1210 70 1285 0 L1545 0 C1430 80 1310 135 1195 172 C1130 192 1070 205 1015 212 Z" fill="url(#gBright)"/>
        <path d="M1105 190 C1260 130 1430 58 1565 0 L1672 0 L1672 200 C1570 128 1430 108 1305 135 C1225 152 1155 172 1105 190 Z" fill="url(#gNavy)"/>

        {{-- ===== Bawah ===== --}}
        <path d="M0 768 C130 790 250 855 430 905 C600 950 800 945 1000 912 C1250 862 1450 780 1672 650 L1672 941 L0 941 Z" fill="url(#gPale)"/>
        <path d="M980 941 C1100 925 1250 892 1400 836 C1500 800 1600 778 1672 765 L1672 941 Z" fill="url(#gBright)"/>
        <path d="M1130 941 C1250 905 1400 862 1500 842 C1580 826 1630 822 1672 820 L1672 941 Z" fill="url(#gNavy)"/>
        <path d="M900 928 C1100 918 1350 852 1672 738" fill="none" stroke="#fff" stroke-opacity=".75" stroke-width="3"/>
        <path d="M0 790 C120 815 230 870 340 941 L0 941 Z" fill="url(#gBright)"/>
        <path d="M0 828 C110 852 210 895 300 941 L0 941 Z" fill="url(#gNavy)"/>
        <path d="M0 768 C130 790 250 855 430 905 C600 950 800 945 900 928" fill="none" stroke="#fff" stroke-opacity=".9" stroke-width="3"/>
    </svg>

    {{-- Logo Kemenkeu - DJPb - InTrezz --}}
    <div class="brand">
        <img src="{{ asset('assets/img/login/logo-instansi.png') }}" alt="Kementerian Keuangan RI, DJPb, InTrezz">
    </div>

    {{-- Teks sambutan --}}
    <section class="hero">
        <p class="hero__hi">Selamat Datang di</p>
        <h1 class="hero__title">Dashboard <span>IKPA</span></h1>
        <p class="hero__tagline">Transparansi <i>&bull;</i> Akuntabilitas <i>&bull;</i> Kinerja Lebih Baik</p>
        <div class="hero__line"></div>
        <p class="hero__desc">Sistem Monitoring IKPA untuk mendukung pengelolaan kinerja anggaran yang lebih optimal.</p>
    </section>

    {{-- Foto gedung --}}
    <img class="gedung" src="{{ asset('assets/img/login/gedung-kppn.png') }}" alt="Gedung KPPN">
