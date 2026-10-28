<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('ikpa.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
    <!-- CSS Custom -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v=3">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    @stack('styles')
</head>

<body>

    <!-- ==================== TOP NAVBAR ==================== -->
    <nav class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link text-white d-md-none" id="btnToggleSidebar">
                <i class="bi bi-list fs-3"></i>
            </button>
            <img src="{{ asset('assets/img/log051.png') }}" alt="Logo KPPN" class="topbar-logo">
            <div>
                <h1 class="topbar-title mb-0">{{ config('ikpa.name') }}</h1>
                <small class="topbar-subtitle">{{ config('ikpa.subtitle') }}</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <div class="topbar-date d-none d-sm-flex align-items-center gap-2">
                <i class="bi bi-calendar3"></i>
                <span>{{ now()->locale('id')->translatedFormat('d F Y') }}</span>
            </div>

            <div class="dropdown">
                <button class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2"
                    type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span>{{ ucfirst(auth()->user()->username) }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profil') }}"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="{{ route('pengaturan') }}"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="app-wrapper d-flex">

        @include('partials.sidebar')

        <main class="main-content flex-grow-1">
            @yield('content')
        </main>

    </div><!-- /.app-wrapper -->

    <!-- ==================== FOOTER ==================== -->
    <footer class="app-footer text-center">
        <small>
            Sumber Data : Data diambil dari file Excel yang diupload setiap periode
            &nbsp;|&nbsp;
            &copy; {{ date('Y') }} {{ config('ikpa.name') }}
        </small>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    {{-- Data JS per halaman (variabel grafik) harus ada SEBELUM script.js --}}
    @stack('scripts')

    <!-- JS Custom -->
    <script src="{{ asset('assets/js/script.js') }}?v=6"></script>
</body>

</html>
