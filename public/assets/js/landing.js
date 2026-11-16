/* =====================================================================
   Landing Page Dashboard IKPA  (public/assets/js/landing.js)
   - menu HP, animasi muncul, hitung naik angka
   - pencarian / filter / urutan / halaman tabel satker
   - grafik Chart.js (data dari window.LANDING di landing.blade.php)
   ===================================================================== */
(function () {
    'use strict';

    var DATA = window.LANDING || {};

    /* ---------- Navbar ---------- */
    var nav = document.getElementById('nav');
    var toggle = document.getElementById('navToggle');
    var links = document.getElementById('navLinks');

    function onScroll() {
        nav.classList.toggle('is-scrolled', window.scrollY > 10);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (toggle) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.querySelector('i').className = open ? 'bi bi-x-lg' : 'bi bi-list';
        });
        links.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                nav.classList.remove('is-open');
                toggle.querySelector('i').className = 'bi bi-list';
            }
        });
    }

    // Tandai menu aktif sesuai bagian yang sedang dilihat
    var sections = ['beranda', 'statistik', 'peringkat', 'daftar', 'tentang']
        .map(function (id) { return document.getElementById(id); })
        .filter(Boolean);
    if ('IntersectionObserver' in window) {
        var spy = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    links.querySelectorAll('a').forEach(function (a) {
                        a.classList.toggle('is-active', a.getAttribute('href') === '#' + en.target.id);
                    });
                }
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        sections.forEach(function (s) { spy.observe(s); });
    }

    /* ---------- Animasi muncul saat scroll ---------- */
    var reveals = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        reveals.forEach(function (el) { el.classList.add('show'); });
    } else {
        var rev = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    en.target.classList.add('show');
                    rev.unobserve(en.target);
                }
            });
        }, { threshold: 0.12 });
        reveals.forEach(function (el) { rev.observe(el); });
    }

    /* ---------- Hitung naik angka statistik ---------- */
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function format(n, d) {
        return n.toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
    }
    function countUp(el) {
        var target = parseFloat(el.getAttribute('data-count'));
        var d = parseInt(el.getAttribute('data-decimals') || '0', 10);
        if (isNaN(target) || reduce) { return; }
        var start = null, dur = 1200;
        function step(ts) {
            if (start === null) { start = ts; }
            var p = Math.min((ts - start) / dur, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = format(target * eased, d);
            if (p < 1) { requestAnimationFrame(step); } else { el.textContent = format(target, d); }
        }
        requestAnimationFrame(step);
    }
    var counters = document.querySelectorAll('[data-count]');
    if ('IntersectionObserver' in window) {
        var co = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { countUp(en.target); co.unobserve(en.target); }
            });
        }, { threshold: 0.6 });
        counters.forEach(function (el) { co.observe(el); });
    }

    /* ---------- Tabel satker: cari, filter, urut, halaman ---------- */
    var table = document.getElementById('tbl');
    if (table) {
        var PER_HALAMAN = 10;
        var tbody = table.querySelector('tbody');
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        var inputCari = document.getElementById('tblCari');
        var heroCari = document.getElementById('heroCari');
        var heroForm = document.getElementById('heroSearch');
        var selectUrut = document.getElementById('tblUrut');
        var segBtns = document.querySelectorAll('#tblFilter .seg__btn');
        var kosong = document.getElementById('tblKosong');
        var pagerInfo = document.getElementById('pagerInfo');
        var pagerBtns = document.getElementById('pagerBtns');

        var state = { q: (inputCari.value || '').trim().toLowerCase(), filter: 'semua', urut: 'peringkat', hal: 1 };

        function cocok(tr) {
            var okFilter = state.filter === 'semua' || tr.getAttribute('data-status') === state.filter;
            if (!okFilter) { return false; }
            if (!state.q) { return true; }
            return tr.getAttribute('data-nama').indexOf(state.q) !== -1 ||
                   tr.getAttribute('data-kode').toLowerCase().indexOf(state.q) !== -1;
        }

        function urutkan(arr) {
            var by = state.urut;
            return arr.slice().sort(function (a, b) {
                if (by === 'nama') { return a.getAttribute('data-nama').localeCompare(b.getAttribute('data-nama'), 'id'); }
                if (by === 'kode') { return a.getAttribute('data-kode').localeCompare(b.getAttribute('data-kode'), 'id', { numeric: true }); }
                return parseInt(a.getAttribute('data-peringkat'), 10) - parseInt(b.getAttribute('data-peringkat'), 10);
            });
        }

        function tombol(label, hal, opts) {
            var b = document.createElement('button');
            b.type = 'button';
            b.innerHTML = label;
            if (opts && opts.aktif) { b.className = 'is-active'; }
            if (opts && opts.mati) { b.disabled = true; }
            b.addEventListener('click', function () { state.hal = hal; render(false); scrollKeTabel(); });
            return b;
        }

        function scrollKeTabel() {
            var top = document.getElementById('daftar').getBoundingClientRect().top + window.scrollY - 90;
            window.scrollTo({ top: top, behavior: reduce ? 'auto' : 'smooth' });
        }

        function render() {
            var hasil = urutkan(rows.filter(cocok));
            var total = hasil.length;
            var jumlahHal = Math.max(1, Math.ceil(total / PER_HALAMAN));
            if (state.hal > jumlahHal) { state.hal = jumlahHal; }
            var awal = (state.hal - 1) * PER_HALAMAN;
            var tampil = hasil.slice(awal, awal + PER_HALAMAN);

            rows.forEach(function (tr) { tr.hidden = true; });
            tampil.forEach(function (tr) { tr.hidden = false; tbody.appendChild(tr); });

            kosong.hidden = total !== 0;
            table.hidden = total === 0;
            pagerInfo.textContent = total === 0
                ? 'Tidak ada data'
                : 'Menampilkan ' + (awal + 1) + '-' + (awal + tampil.length) + ' dari ' + total + ' satker';

            // Tombol halaman (maksimal 7 tombol + titik-titik)
            pagerBtns.innerHTML = '';
            if (jumlahHal > 1) {
                pagerBtns.appendChild(tombol('<i class="bi bi-chevron-left"></i>', state.hal - 1, { mati: state.hal === 1 }));
                var daftar = [];
                for (var i = 1; i <= jumlahHal; i++) {
                    if (i === 1 || i === jumlahHal || Math.abs(i - state.hal) <= 1) { daftar.push(i); }
                    else if (daftar[daftar.length - 1] !== '...') { daftar.push('...'); }
                }
                daftar.forEach(function (n) {
                    if (n === '...') {
                        var s = document.createElement('span');
                        s.textContent = '...';
                        s.style.cssText = 'align-self:center;padding:0 .3rem';
                        pagerBtns.appendChild(s);
                    } else {
                        pagerBtns.appendChild(tombol(String(n), n, { aktif: n === state.hal }));
                    }
                });
                pagerBtns.appendChild(tombol('<i class="bi bi-chevron-right"></i>', state.hal + 1, { mati: state.hal === jumlahHal }));
            }
        }

        inputCari.addEventListener('input', function () {
            state.q = inputCari.value.trim().toLowerCase();
            state.hal = 1;
            if (heroCari) { heroCari.value = inputCari.value; }
            render();
        });
        selectUrut.addEventListener('change', function () { state.urut = selectUrut.value; state.hal = 1; render(); });
        segBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                segBtns.forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');
                state.filter = btn.getAttribute('data-filter');
                state.hal = 1;
                render();
            });
        });

        // Kolom cari di bagian atas: langsung isi tabel tanpa memuat ulang halaman
        if (heroForm) {
            heroForm.addEventListener('submit', function (e) {
                e.preventDefault();
                inputCari.value = heroCari.value;
                state.q = heroCari.value.trim().toLowerCase();
                state.hal = 1;
                render();
                var top = document.getElementById('daftar').getBoundingClientRect().top + window.scrollY - 90;
                window.scrollTo({ top: top, behavior: reduce ? 'auto' : 'smooth' });
            });
        }

        render();
    }

    /* ---------- Grafik ---------- */
    if (typeof Chart === 'undefined') { return; }
    Chart.defaults.font.family = "'Poppins', Arial, sans-serif";
    Chart.defaults.color = '#6c7a99';

    var elTren = document.getElementById('chartTren');
    if (elTren && DATA.labelTren && DATA.labelTren.length) {
        var ctx = elTren.getContext('2d');
        var grad = ctx.createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, 'rgba(29,123,255,.35)');
        grad.addColorStop(1, 'rgba(29,123,255,0)');

        new Chart(elTren, {
            type: 'line',
            data: {
                labels: DATA.labelTren,
                datasets: [
                    {
                        label: 'Rata-rata IKPA',
                        data: DATA.nilaiTren,
                        borderColor: '#0a5be0',
                        backgroundColor: grad,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0a5be0',
                        pointBorderWidth: 3
                    },
                    {
                        label: 'Target (' + DATA.target + ')',
                        data: DATA.labelTren.map(function () { return DATA.target; }),
                        borderColor: '#e0a200',
                        borderDash: [6, 6],
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        callbacks: {
                            label: function (c) { return ' ' + c.dataset.label + ': ' + format(c.parsed.y, 2); }
                        }
                    }
                },
                scales: {
                    y: { suggestedMin: 60, suggestedMax: 100, grid: { color: '#e9f0fb' }, border: { display: false } },
                    x: { grid: { display: false }, border: { display: false } }
                }
            }
        });
    }

    var elStatus = document.getElementById('chartStatus');
    if (elStatus) {
        new Chart(elStatus, {
            type: 'doughnut',
            data: {
                labels: ['Memenuhi target', 'Perlu perhatian'],
                datasets: [{
                    data: [DATA.memenuhi, DATA.perhatian],
                    backgroundColor: ['#2fb65f', '#ef5350'],
                    borderColor: '#fff',
                    borderWidth: 4,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { display: false } }
            }
        });
    }
})();
