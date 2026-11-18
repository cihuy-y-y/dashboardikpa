/* =====================================================================
   Panel Rincian Satker + Mesin Rekomendasi + Asisten IKPA
   (public/assets/js/asisten.js)

   Data dikirim dari server lewat window.IKPA_DATA (lihat landing.blade.php):
     - panduan : isi app/Support/IkpaPanduan.php (indikator, langkah, FAQ, jadwal)
     - rapor   : nilai tiap satker pada periode terpilih
   Tidak ada permintaan jaringan: semua berjalan di browser.
   ===================================================================== */
(function () {
    'use strict';

    var D = window.IKPA_DATA;
    if (!D || !D.panduan) { return; }

    var P = D.panduan;
    var R = D.rapor || { satker: [], rata: {} };
    var TARGET = P.target;
    var IND = P.indikator;
    var DISP = P.dispensasi;
    var byKode = {};
    R.satker.forEach(function (s) { byKode[s.kode] = s; });

    /* ------------------------------------------------------------ util */
    function fmt(n, d) {
        if (n === null || n === undefined || isNaN(n)) { return '-'; }
        d = d === undefined ? 2 : d;
        return Number(n).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
    }
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    /** teks aman + **tebal** + *miring* */
    function rich(text) {
        return esc(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>');
    }
    function clamp(n, a, b) { return Math.max(a, Math.min(b, n)); }

    /* ------------------------------------------------------------ mesin rekomendasi */
    function kategoriNilai(n) {
        var daftar = P.kategori.slice().sort(function (a, b) { return b.min - a.min; });
        for (var i = 0; i < daftar.length; i++) {
            if (n >= daftar[i].min) { return daftar[i]; }
        }
        return daftar[daftar.length - 1];
    }

    /** konversi bobot bisa tersimpan 100 (persen) atau 1 (pecahan) */
    function faktorKonversi(k) {
        if (k === null || k === undefined || isNaN(k) || k <= 0) { return 1; }
        return k > 1 ? Math.min(k, 100) / 100 : k;
    }

    function statusIndikator(v) {
        if (v >= 100) { return { kelas: 'ok', teks: 'Maksimal' }; }
        if (v >= TARGET) { return { kelas: 'ok', teks: 'Baik' }; }
        return { kelas: 'warn', teks: 'Perlu ditingkatkan' };
    }

    /**
     * Menganalisis satu satker. Potensi kenaikan tiap indikator =
     *   bobot% x (100 - nilai) / konversi bobot   (estimasi bila indikator mencapai 100)
     */
    function analisis(s) {
        var f = faktorKonversi(s.konversi);
        var items = IND.map(function (ik) {
            var v = s.ind[ik.key];
            var ada = v !== null && v !== undefined;
            // nilai 0 dengan konversi < 100% kemungkinan berarti tidak ada transaksi (tidak dihitung)
            var tanpaData = ada && v === 0 && f < 1;
            var gap = ada ? Math.max(0, 100 - v) : 0;
            var potensi = ada && !tanpaData ? (ik.bobot / 100) * gap / f : 0;
            return {
                ik: ik, key: ik.key, nama: ik.nama, bobot: ik.bobot, ikon: ik.ikon,
                nilai: v, ada: ada, tanpaData: tanpaData, gap: gap, potensi: potensi,
                rata: R.rata ? R.rata[ik.key] : null
            };
        });

        var disp = s.dispensasi;
        var dispPotensi = (disp !== null && disp !== undefined && disp > 0 && disp <= 1) ? disp : 0;

        var urut = items.filter(function (i) { return i.potensi > 0.005; })
            .sort(function (a, b) { return b.potensi - a.potensi; });

        var butuh = Math.max(0, TARGET - s.nilai);
        var kum = 0, n = 0;
        for (var i = 0; i < urut.length; i++) {
            kum += urut[i].potensi; n++;
            if (s.nilai + kum >= TARGET) { break; }
        }
        var total = urut.reduce(function (t, x) { return t + x.potensi; }, 0);

        return {
            s: s, kategori: kategoriNilai(s.nilai), butuh: butuh, items: items, urut: urut,
            disp: disp, dispPotensi: dispPotensi,
            jalur: { jumlah: n, estimasi: s.nilai + kum, cukup: s.nilai + kum >= TARGET },
            totalPotensi: total,
            tanpaDataList: items.filter(function (i) { return !i.ada; })
        };
    }

    /* ------------------------------------------------------------ panel rincian */
    var modal = document.getElementById('modalRapor');
    var raporIsi = document.getElementById('raporIsi');
    var lastFocus = null;

    function sparkline(riwayat) {
        if (!riwayat || riwayat.length < 2) { return ''; }
        var W = 360, H = 90, kiri = 8, kanan = 8, atas = 14, bawah = 22;
        var vals = riwayat.map(function (r) { return r.v; }).filter(function (v) { return v !== null; });
        if (vals.length < 2) { return ''; }
        var lo = Math.floor(Math.min.apply(null, vals.concat([TARGET])) / 5) * 5 - 2;
        var hi = Math.ceil(Math.max.apply(null, vals.concat([TARGET])) / 5) * 5 + 2;
        var px = function (i) { return kiri + (W - kiri - kanan) * i / (riwayat.length - 1); };
        var py = function (v) { return atas + (H - atas - bawah) * (1 - (v - lo) / (hi - lo)); };
        var pts = riwayat.map(function (r, i) { return r.v === null ? null : [px(i), py(r.v), r]; }).filter(Boolean);
        var garis = pts.map(function (p) { return p[0].toFixed(1) + ',' + p[1].toFixed(1); }).join(' ');
        var yt = py(TARGET).toFixed(1);
        var s = '<svg class="spark" viewBox="0 0 ' + W + ' ' + H + '" role="img" aria-label="Riwayat nilai IKPA satker">' +
            '<line x1="' + kiri + '" x2="' + (W - kanan) + '" y1="' + yt + '" y2="' + yt + '" class="spark__target"/>' +
            '<text x="' + (W - kanan) + '" y="' + (yt - 4) + '" text-anchor="end" class="spark__t">Target ' + TARGET + '</text>' +
            '<polyline points="' + garis + '" class="spark__line"/>';
        pts.forEach(function (p, i) {
            s += '<circle cx="' + p[0].toFixed(1) + '" cy="' + p[1].toFixed(1) + '" r="4" class="spark__pt"><title>' + esc(p[2].l) + ': ' + fmt(p[2].v) + '</title></circle>';
            if (i === 0 || i === pts.length - 1 || pts.length <= 6) {
                s += '<text x="' + p[0].toFixed(1) + '" y="' + (H - 6) + '" text-anchor="' + (i === 0 ? 'start' : (i === pts.length - 1 ? 'end' : 'middle')) + '" class="spark__x">' + esc(p[2].l) + '</text>';
            }
        });
        return s + '</svg>';
    }

    function listHtml(arr, ordered) {
        var tag = ordered ? 'ol' : 'ul';
        return '<' + tag + '>' + arr.map(function (x) { return '<li>' + rich(x) + '</li>'; }).join('') + '</' + tag + '>';
    }

    function barisIndikator(it) {
        if (!it.ada) {
            return '<tr><td><span class="ik"><i class="bi ' + it.ikon + '"></i>' + esc(it.nama) + '</span></td><td>' + it.bobot + '%</td>' +
                '<td colspan="3" class="muted">Data belum tersedia</td></tr>';
        }
        var st = statusIndikator(it.nilai);
        var catatan = it.tanpaData ? '<small class="muted">Kemungkinan tidak ada transaksi (tidak dihitung)</small>' : '';
        return '<tr>' +
            '<td><span class="ik"><i class="bi ' + it.ikon + '"></i>' + esc(it.nama) + '</span></td>' +
            '<td>' + it.bobot + '%</td>' +
            '<td><div class="score"><b>' + fmt(it.nilai) + '</b><div class="bar ' + (st.kelas === 'warn' ? 'bar--warn' : '') + '"><i style="width:' + clamp(it.nilai, 0, 100) + '%"></i></div></div>' + catatan + '</td>' +
            '<td class="muted">' + fmt(it.rata) + '</td>' +
            '<td><span class="badge badge--' + (it.tanpaData ? 'netral' : st.kelas) + '">' + (it.tanpaData ? 'Tidak dihitung' : st.teks) + '</span></td>' +
            '</tr>';
    }

    function kartuLangkah(it, nomor, terbuka) {
        var ik = it.ik || it;
        var judulNilai = it.isDisp
            ? 'Pengurang saat ini <b>' + fmt(it.nilai) + '</b> poin'
            : 'Nilai <b>' + fmt(it.nilai) + '</b> &middot; bobot ' + it.bobot + '%';
        return '<details class="langkah"' + (terbuka ? ' open' : '') + '>' +
            '<summary>' +
                '<span class="langkah__no">' + nomor + '</span>' +
                '<span class="langkah__nama"><i class="bi ' + ik.ikon + '"></i> ' + esc(ik.nama) + '<small>' + judulNilai + '</small></span>' +
                '<span class="langkah__gain">+' + fmt(it.potensi) + ' poin<small>potensi</small></span>' +
            '</summary>' +
            '<div class="langkah__isi">' +
                '<h5>Cara indikator ini dinilai</h5>' + listHtml(ik.cara, false) +
                '<h5>Langkah yang bisa dilakukan</h5>' + listHtml(ik.langkah, true) +
                (it.rata !== null && it.rata !== undefined && !it.isDisp ? '<p class="langkah__rata">Rata-rata satker lain pada periode ini: <b>' + fmt(it.rata) + '</b></p>' : '') +
            '</div>' +
        '</details>';
    }

    function renderRapor(a) {
        var s = a.s;
        var kurang = s.nilai < TARGET;
        var selisih = Math.abs(TARGET - s.nilai);

        var html = '<div class="rapor__head">' +
            '<div class="rapor__id"><small>Rincian Nilai IKPA &middot; Periode ' + esc(D.periodeLabel) + '</small>' +
            '<h2 id="raporJudul">' + esc(s.nama) + '</h2>' +
            '<span class="mono">Kode satker ' + esc(s.kode) + '</span></div>' +
            '<div class="rapor__skor"><div class="ring ' + (kurang ? 'ring--warn' : '') + '" style="--p:' + clamp(s.nilai, 0, 100) + '"><span>' + fmt(s.nilai) + '</span></div>' +
            '<div class="rapor__meta"><span class="badge badge--kat-' + a.kategori.kode.toLowerCase() + '">Kategori ' + a.kategori.kode + ' &middot; ' + esc(a.kategori.nama) + '</span>' +
            '<small>Peringkat <b>' + s.peringkat + '</b> dari ' + R.jumlah + ' satker</small></div></div>' +
            '</div>';

        html += '<div class="banner ' + (kurang ? 'banner--warn' : 'banner--ok') + '">' +
            '<i class="bi ' + (kurang ? 'bi-exclamation-triangle-fill' : 'bi-patch-check-fill') + '"></i>' +
            '<div>' + (kurang
                ? '<b>Nilai di bawah target ' + TARGET + '.</b> Perlu naik <b>+' + fmt(selisih) + ' poin</b> agar memenuhi target.'
                : '<b>Memenuhi target ' + TARGET + '.</b> Nilai Anda ' + fmt(selisih) + ' poin di atas batas.') +
            '</div></div>';

        // ringkasan aspek
        var bobotAspek = { perencanaan: 0, pelaksanaan: 0, hasil: 0 };
        IND.forEach(function (ik) {
            if (ik.aspek === 'Perencanaan') { bobotAspek.perencanaan += ik.bobot; }
            else if (ik.aspek === 'Hasil') { bobotAspek.hasil += ik.bobot; }
            else { bobotAspek.pelaksanaan += ik.bobot; }
        });
        html += '<div class="aspek3">' +
            '<div><small>Aspek Perencanaan (bobot ' + bobotAspek.perencanaan + '%)</small><b>' + fmt(s.aspek.perencanaan) + '</b></div>' +
            '<div><small>Aspek Implementasi Pelaksanaan (bobot ' + bobotAspek.pelaksanaan + '%)</small><b>' + fmt(s.aspek.pelaksanaan) + '</b></div>' +
            '<div><small>Aspek Hasil (bobot ' + bobotAspek.hasil + '%)</small><b>' + fmt(s.aspek.hasil) + '</b></div>' +
        '</div>';

        // tabel indikator
        html += '<h3 class="rapor__h"><i class="bi bi-list-check"></i> Nilai Tiap Indikator</h3>' +
            '<div class="table-wrap"><table class="rapor__tbl"><thead><tr><th>Indikator</th><th>Bobot</th><th>Nilai</th><th>Rata-rata satker</th><th>Status</th></tr></thead><tbody>' +
            a.items.map(barisIndikator).join('') +
            '<tr class="sub"><td><span class="ik"><i class="bi ' + DISP.ikon + '"></i>Dispensasi SPM</span></td><td>pengurang</td>' +
            '<td colspan="3">' + (a.disp === null || a.disp === undefined ? '<span class="muted">Data belum tersedia</span>' : '<b>' + fmt(a.disp) + '</b> poin dikurangkan dari nilai akhir') + '</td></tr>' +
            '</tbody></table></div>' +
            '<div class="ringkasan4">' +
                '<div><small>Nilai total</small><b>' + fmt(s.total) + '</b></div>' +
                '<div><small>Konversi bobot</small><b>' + fmt(s.konversi) + '</b></div>' +
                '<div><small>Dispensasi SPM</small><b>' + fmt(s.dispensasi) + '</b></div>' +
                '<div><small>Nilai akhir</small><b>' + fmt(s.nilai) + '</b></div>' +
            '</div>';

        var spark = sparkline(s.riwayat);
        if (spark) {
            html += '<h3 class="rapor__h"><i class="bi bi-graph-up"></i> Riwayat Nilai Satker</h3><div class="spark-wrap">' + spark + '</div>';
        }

        // langkah perbaikan
        var daftarLangkah = a.urut.slice();
        if (a.dispPotensi > 0) {
            daftarLangkah.push({ isDisp: true, ik: DISP, nama: DISP.nama, nilai: a.disp, potensi: a.dispPotensi, rata: null });
            daftarLangkah.sort(function (x, y) { return y.potensi - x.potensi; });
        }

        html += '<div class="langkah-wrap">';
        if (kurang) {
            html += '<h3 class="rapor__h rapor__h--warn"><i class="bi bi-rocket-takeoff-fill"></i> Langkah Menaikkan Nilai IKPA</h3>';
            if (daftarLangkah.length) {
                html += '<p class="langkah__ringkas">Nilai Anda <b>' + fmt(s.nilai) + '</b>, perlu <b>+' + fmt(a.butuh) + ' poin</b> untuk mencapai ' + TARGET + '. ';
                if (a.jalur.cukup) {
                    html += 'Memperbaiki <b>' + a.jalur.jumlah + ' indikator prioritas</b> teratas hingga mencapai 100 diperkirakan membawa nilai ke sekitar <b>' + fmt(a.jalur.estimasi) + '</b>.';
                } else {
                    html += 'Bila seluruh indikator di bawah ini mencapai 100, estimasi nilai menjadi sekitar <b>' + fmt(s.nilai + a.totalPotensi + a.dispPotensi) + '</b>, sehingga perbaikan perlu dilakukan menyeluruh dan bertahap.';
                }
                html += '</p><p class="langkah__catatan">Urutan berdasarkan potensi tambahan nilai terbesar. Angka bersifat estimasi.</p>';
                html += daftarLangkah.map(function (it, i) { return kartuLangkah(it, i + 1, i < 2); }).join('');
            } else {
                html += '<p class="langkah__ringkas">Data indikator belum lengkap sehingga langkah prioritas belum dapat disusun. Silakan tanyakan ke Asisten IKPA atau hubungi KPPN setempat.</p>';
            }
        } else {
            html += '<h3 class="rapor__h rapor__h--ok"><i class="bi bi-shield-check"></i> Pertahankan &amp; Tingkatkan</h3>';
            if (daftarLangkah.length) {
                html += '<p class="langkah__ringkas">Nilai Anda sudah memenuhi target. Untuk menjaga kategori dan menambah nilai, perhatikan indikator berikut yang masih bisa ditingkatkan.</p>';
                html += daftarLangkah.slice(0, 3).map(function (it, i) { return kartuLangkah(it, i + 1, false); }).join('');
            } else {
                html += '<p class="langkah__ringkas">Seluruh indikator sudah berada di nilai maksimal. Pertahankan disiplin pelaksanaan anggaran.</p>';
            }
        }
        if (a.tanpaDataList.length) {
            html += '<p class="langkah__catatan">Data belum tersedia untuk: ' + a.tanpaDataList.map(function (i) { return esc(i.nama); }).join(', ') + '.</p>';
        }
        html += '</div>';

        // jadwal
        html += '<details class="jadwal-mini"><summary><i class="bi bi-calendar-check-fill"></i> Jadwal penting IKPA</summary><ul>' +
            P.jadwal.map(function (j) { return '<li><b>' + esc(j.kapan) + '</b> &mdash; ' + esc(j.apa) + '</li>'; }).join('') + '</ul></details>';

        html += '<div class="rapor__aksi">' +
            '<button type="button" class="btn btn--primary" data-asisten-kode="' + esc(s.kode) + '"><i class="bi bi-chat-dots-fill"></i> Tanya Asisten IKPA</button>' +
            '<button type="button" class="btn btn--ghost" id="btnCetak"><i class="bi bi-printer-fill"></i> Cetak</button>' +
            '</div>' +
            '<p class="rapor__disc">Estimasi dihitung dari bobot indikator dan nilai periode ini. Perhitungan resmi mengikuti ' + esc(P.sumber) + ' dan data OMSPAN/SAKTI; beberapa indikator dihitung kumulatif sepanjang tahun sehingga perbaikan terlihat bertahap.</p>';

        return html;
    }

    function setUrl(kode) {
        try {
            var u = new URL(window.location.href);
            if (kode) { u.searchParams.set('satker', kode); } else { u.searchParams.delete('satker'); }
            window.history.replaceState(null, '', u.toString());
        } catch (e) { /* abaikan */ }
    }

    function bukaRapor(kode) {
        var s = byKode[kode];
        if (!s || !modal) { return false; }
        raporIsi.innerHTML = renderRapor(analisis(s));
        lastFocus = document.activeElement;
        modal.hidden = false;
        document.body.classList.add('no-scroll');
        var panel = modal.querySelector('.modal__panel');
        panel.scrollTop = 0;
        panel.focus();
        setUrl(kode);
        return true;
    }

    function tutupRapor() {
        if (!modal || modal.hidden) { return; }
        modal.hidden = true;
        document.body.classList.remove('no-scroll');
        setUrl(null);
        if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target.closest('[data-tutup]')) { tutupRapor(); return; }
            var tanya = e.target.closest('[data-asisten-kode]');
            if (tanya) {
                var kode = tanya.getAttribute('data-asisten-kode');
                tutupRapor();
                bukaAsisten({ kode: kode, kirim: 'Bagaimana cara menaikkan nilai IKPA saya?' });
                return;
            }
            if (e.target.closest('#btnCetak')) {
                document.body.classList.add('cetak-rapor');
                window.print();
            }
        });
    }
    window.addEventListener('afterprint', function () { document.body.classList.remove('cetak-rapor'); });

    // tombol "Rincian" di tabel / kartu peringkat
    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-rincian]');
        if (t) { bukaRapor(t.getAttribute('data-rincian')); }
        var ask = e.target.closest('[data-buka-asisten]');
        if (ask) { bukaAsisten({}); }
    });
    // dari kolom pencarian utama bila hasil tepat satu satker
    document.addEventListener('ikpa:rincian', function (e) { bukaRapor(e.detail.kode); });

    /* ------------------------------------------------------------ Asisten IKPA */
    var chat = document.getElementById('chat');
    var fab = document.getElementById('fabAsisten');
    var fabTip = document.getElementById('fabTip');
    var msgs = document.getElementById('chatMsgs');
    var chips = document.getElementById('chatChips');
    var form = document.getElementById('chatForm');
    var input = document.getElementById('chatInput');
    var ctxBar = document.getElementById('chatCtx');
    var ctxKode = null;
    var sudahSapa = false;

    var STOP = ['yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'apa', 'bagaimana', 'cara', 'nilai', 'ikpa', 'satker', 'saya',
        'kami', 'tolong', 'mohon', 'dong', 'ya', 'itu', 'ini', 'berapa', 'kenapa', 'mengapa', 'agar', 'supaya', 'naik',
        'menaikkan', 'meningkatkan', 'perbaiki', 'tanya', 'cek', 'lihat', 'rincian', 'analisis', 'bantu', 'saran',
        'langkah', 'dengan', 'pada', 'kode', 'nama', 'mau', 'ingin', 'bisa', 'apakah', 'adalah', 'tentang', 'soal', 'cari'];

    function norm(s) { return ' ' + String(s).toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim() + ' '; }

    /** skor kecocokan: kata utuh = (jumlah kata x2 + 1); kata berawalan "~" = potongan kata (+2) */
    function skor(q, kws) {
        var sc = 0;
        (kws || []).forEach(function (k) {
            var stem = k.charAt(0) === '~';
            if (stem) { k = k.slice(1); }
            var nk = norm(k).trim();
            if (!nk) { return; }
            if (stem) { if (q.indexOf(nk) !== -1) { sc += 2; } }
            else if (q.indexOf(' ' + nk + ' ') !== -1) { sc += nk.split(' ').length * 2 + 1; }
        });
        return sc;
    }

    /* --- membangun blok jawaban --- */
    function blokPlan(a) {
        var s = a.s, b = [];
        b.push({ t: 'kartu', kode: s.kode });
        if (a.butuh > 0) {
            b.push({ t: 'p', x: 'Nilai **' + s.nama + '** adalah **' + fmt(s.nilai) + '** (kategori ' + a.kategori.nama + '). Perlu **+' + fmt(a.butuh) + ' poin** untuk mencapai ' + TARGET + '.' });
        } else {
            b.push({ t: 'p', x: 'Nilai **' + s.nama + '** adalah **' + fmt(s.nilai) + '** (kategori ' + a.kategori.nama + '), sudah memenuhi target ' + TARGET + '. Berikut peluang untuk menjaga dan menambah nilai.' });
        }
        var list = a.urut.slice();
        if (a.dispPotensi > 0) { list.push({ nama: DISP.nama, nilai: a.disp, bobot: null, potensi: a.dispPotensi }); list.sort(function (x, y) { return y.potensi - x.potensi; }); }
        if (list.length) {
            b.push({ t: 'p', x: 'Prioritas perbaikan (berdasarkan potensi tambahan nilai):' });
            b.push({ t: 'ol', x: list.slice(0, 3).map(function (i) {
                return '**' + i.nama + '**: nilai ' + fmt(i.nilai) + (i.bobot ? ' (bobot ' + i.bobot + '%)' : ' (pengurang)') + ', potensi **+' + fmt(i.potensi) + ' poin**';
            }) });
            if (a.butuh > 0) {
                b.push({ t: 'p', x: a.jalur.cukup
                    ? 'Memperbaiki ' + a.jalur.jumlah + ' indikator teratas sampai nilai 100 diperkirakan membawa nilai ke sekitar **' + fmt(a.jalur.estimasi) + '** (estimasi).'
                    : 'Bila seluruh indikator mencapai 100, estimasi nilai menjadi sekitar **' + fmt(s.nilai + a.totalPotensi + a.dispPotensi) + '**, jadi perlu perbaikan menyeluruh dan bertahap.' });
            }
            b.push({ t: 'p', x: 'Ketik nama indikator (misalnya *' + list[0].nama + '*) untuk langkah rincinya.' });
        } else {
            b.push({ t: 'p', x: 'Seluruh indikator yang tersedia sudah maksimal atau datanya belum lengkap. Pertahankan disiplin pelaksanaan anggaran.' });
        }
        return { blocks: b, chips: chipsPlan(list) };
    }

    function chipsPlan(list) {
        var c = list.slice(0, 2).map(function (i) { return 'Cara menaikkan ' + i.nama; });
        c.push({ label: 'Buka rincian lengkap', aksi: 'rapor' });
        c.push('Jadwal penting');
        return c;
    }

    function blokUmum() {
        var urut = IND.slice().sort(function (a, b) { return b.bobot - a.bobot; }).slice(0, 4);
        return {
            blocks: [
                { t: 'p', x: 'Secara umum, nilai IKPA paling dipengaruhi indikator dengan **bobot terbesar**:' },
                { t: 'ol', x: urut.map(function (i) { return '**' + i.nama + '** (bobot ' + i.bobot + '%): ' + i.langkah[0]; }) },
                { t: 'p', x: 'Supaya saran sesuai kondisi satker Anda, **ketik nama atau kode satker** di sini. Nanti saya hitung prioritas perbaikannya.' }
            ],
            chips: urut.slice(0, 3).map(function (i) { return 'Cara menaikkan ' + i.nama; })
        };
    }

    function blokIndikator(ik, mode, isDisp) {
        var b = [{ t: 'h', x: ik.nama + (isDisp ? ' (pengurang nilai)' : ' (bobot ' + ik.bobot + '%)') }];
        var s = ctxKode ? byKode[ctxKode] : null;
        if (s) {
            var a = analisis(s);
            if (isDisp) {
                if (a.disp !== null && a.disp !== undefined) {
                    b.push({ t: 'p', x: 'Dispensasi SPM **' + s.nama + '** saat ini mengurangi nilai sebesar **' + fmt(a.disp) + ' poin**.' });
                }
            } else {
                var it = a.items.filter(function (i) { return i.key === ik.key; })[0];
                if (it && it.ada) {
                    if (it.tanpaData) {
                        b.push({ t: 'p', x: 'Nilai ' + ik.nama + ' untuk **' + s.nama + '** adalah 0 dengan konversi bobot di bawah 100%. Kemungkinan satker tidak memiliki transaksi untuk indikator ini, sehingga tidak dihitung.' });
                    } else {
                        b.push({ t: 'p', x: 'Nilai ' + ik.nama + ' **' + s.nama + '**: **' + fmt(it.nilai) + '**' + (it.rata !== null && it.rata !== undefined ? ' (rata-rata satker lain ' + fmt(it.rata) + ')' : '') +
                            (it.potensi > 0.005 ? ', potensi tambahan **+' + fmt(it.potensi) + ' poin**.' : ', sudah maksimal.') });
                    }
                }
            }
        }
        if (mode !== 'langkah') { b.push({ t: 'p', x: ik.ringkas }); b.push({ t: 'h', x: 'Cara dinilai' }); b.push({ t: 'ul', x: ik.cara }); }
        if (mode !== 'cara') { b.push({ t: 'h', x: 'Cara menaikkan nilai' }); b.push({ t: 'ol', x: ik.langkah }); }
        var c = [];
        c.push(mode === 'langkah' ? 'Rumus ' + ik.nama : 'Cara menaikkan ' + ik.nama);
        c.push('Jadwal penting');
        if (ctxKode) { c.push({ label: 'Buka rincian lengkap', aksi: 'rapor' }); } else { c.push('Cara menaikkan nilai IKPA'); }
        return { blocks: b, chips: c };
    }

    function blokFaq(f) {
        return { blocks: f.jawaban, chips: f.chips || ['Cara menaikkan nilai IKPA', 'Apa itu IKPA?'] };
    }

    /* --- pencarian satker lewat nama / kode --- */
    function cariSatker(teks) {
        var kodeMatch = String(teks).match(/\b\d{5,}\b/g);
        if (kodeMatch) {
            for (var i = 0; i < kodeMatch.length; i++) {
                if (byKode[kodeMatch[i]]) { return { pasti: byKode[kodeMatch[i]], kandidat: [] }; }
            }
        }
        var tokens = norm(teks).trim().split(' ').filter(function (t) { return t.length >= 3 && STOP.indexOf(t) === -1; });
        if (!tokens.length) { return null; }
        var hasil = [];
        R.satker.forEach(function (s) {
            var n = norm(s.nama);
            var cocok = tokens.filter(function (t) { return n.indexOf(' ' + t) !== -1; });
            if (cocok.length && cocok.length / tokens.length >= 0.6) { hasil.push({ s: s, n: cocok.length }); }
        });
        if (!hasil.length) { return null; }
        hasil.sort(function (a, b) { return b.n - a.n || a.s.nama.localeCompare(b.s.nama, 'id'); });
        if (hasil.length === 1 || (hasil[0].n >= 2 && hasil[0].n > hasil[1].n)) { return { pasti: hasil[0].s, kandidat: [] }; }
        return { pasti: null, kandidat: hasil.slice(0, 5).map(function (h) { return h.s; }), total: hasil.length };
    }

    /* --- otak asisten: menerima teks, mengembalikan {blocks, chips, ctx} --- */
    function jawab(teks) {
        var q = norm(teks);

        // sapaan & terima kasih
        if (/^ (halo|hai|hi|hallo|hello|selamat (pagi|siang|sore|malam)|assalamualaikum|permisi|pagi|siang|sore|malam) /.test(q) && q.trim().split(' ').length <= 4) {
            return { blocks: [{ t: 'p', x: 'Halo! Ada yang bisa saya bantu terkait nilai IKPA?' }], chips: ['Cara menaikkan nilai IKPA', 'Cek nilai satker saya', 'Apa itu IKPA?'] };
        }
        if (/ (terima kasih|makasih|thanks|thank you|trims|mantap|oke terima) /.test(q)) {
            return { blocks: [{ t: 'p', x: 'Sama-sama! Semoga nilai IKPA satker Anda terus meningkat. Silakan bertanya lagi kapan saja.' }], chips: ['Jadwal penting', 'Cara menaikkan nilai IKPA'] };
        }

        // kode satker disebut langsung
        var cari = null;
        var kodeLangsung = String(teks).match(/\b\d{5,}\b/g);
        if (kodeLangsung) { cari = cariSatker(teks); if (cari && cari.pasti) { return { ctx: cari.pasti.kode, ...blokPlan(analisis(cari.pasti)) }; } }

        // topik: indikator dahulu (lebih spesifik), lalu FAQ
        var terbaik = null, skorTerbaik = 0;
        IND.forEach(function (ik) {
            var sc = skor(q, ik.kata_kunci);
            if (sc > skorTerbaik) { skorTerbaik = sc; terbaik = { jenis: 'ind', ik: ik }; }
        });
        var scD = skor(q, DISP.kata_kunci);
        if (scD > skorTerbaik) { skorTerbaik = scD; terbaik = { jenis: 'disp', ik: DISP }; }
        P.faq.forEach(function (f) {
            var sc = skor(q, f.kata_kunci);
            if (sc > skorTerbaik) { skorTerbaik = sc; terbaik = { jenis: 'faq', f: f }; }
        });

        var kataRencana = skor(q, ['~naik', '~ingkat', '~perbaik', 'saran', 'langkah', 'strategi', 'solusi', 'tips', 'rendah', '~turun', 'kenapa', 'mengapa', 'supaya', 'agar']);
        var personal = / (saya|kami|kita|punya saya|punya kami) /.test(q) && /(nilai|ikpa|satker)/.test(q);
        var modeLangkah = kataRencana > 0;

        // indikator yang disebut jelas
        if (terbaik && (terbaik.jenis === 'ind' || terbaik.jenis === 'disp') && skorTerbaik >= 3) {
            var mode = modeLangkah ? 'langkah' : (/ (hitung|rumus|formula|dinilai|pengertian|arti|maksud) /.test(q) ? 'cara' : 'semua');
            return blokIndikator(terbaik.ik, mode, terbaik.jenis === 'disp');
        }

        // minta analisis / saran untuk satker sendiri
        if (personal || (kataRencana >= 2 && !(terbaik && skorTerbaik >= 3 && terbaik.jenis === 'faq' && terbaik.f.id !== 'kategori')) || / (cek|analisis) /.test(q) && / (nilai|satker) /.test(q)) {
            if (ctxKode && byKode[ctxKode]) { return blokPlan(analisis(byKode[ctxKode])); }
            var c = cariSatker(teks);
            if (c && c.pasti) { return { ctx: c.pasti.kode, ...blokPlan(analisis(c.pasti)) }; }
            if (c && c.kandidat.length) { return pilihanSatker(c); }
            var u = blokUmum();
            if ((personal && kataRencana === 0) || (/ (cek|analisis) /.test(q) && kataRencana === 0)) {
                return { blocks: [{ t: 'p', x: 'Baik, **ketik nama atau kode satker** Anda (misalnya sebagian nama satker atau 6 digit kode). Nanti saya tampilkan nilai dan langkah perbaikannya.' }], chips: ['Cara menaikkan nilai IKPA', 'Apa itu IKPA?'] };
            }
            return u;
        }

        if (terbaik && terbaik.jenis === 'faq' && skorTerbaik >= 3) { return blokFaq(terbaik.f); }

        // mungkin nama satker
        var c2 = cariSatker(teks);
        if (c2 && c2.pasti) { return { ctx: c2.pasti.kode, ...blokPlan(analisis(c2.pasti)) }; }
        if (c2 && c2.kandidat.length) { return pilihanSatker(c2); }

        return {
            blocks: [
                { t: 'p', x: 'Maaf, saya belum menemukan jawaban untuk pertanyaan itu di panduan PER-5/PB/2024.' },
                { t: 'p', x: 'Coba tanyakan tentang **indikator** (misalnya *Capaian Output* atau *UP dan TUP*), **cara menaikkan nilai**, **kategori nilai**, atau **jadwal penting**. Untuk hal di luar itu, silakan hubungi KPPN atau Kanwil DJPb setempat.' }
            ],
            chips: ['Cara menaikkan nilai IKPA', 'Rumus nilai IKPA', 'Jadwal penting', 'Hubungi siapa?']
        };
    }

    function pilihanSatker(c) {
        return {
            blocks: [{ t: 'p', x: 'Saya menemukan **' + c.total + ' satker** yang cocok. Pilih salah satu, atau ketik nama yang lebih lengkap:' }],
            chips: c.kandidat.map(function (s) { return { label: s.nama, aksi: 'pilih', kode: s.kode }; })
        };
    }

    /* --- tampilan chat --- */
    function bubble(peran) {
        var d = document.createElement('div');
        d.className = 'msg msg--' + peran;
        msgs.appendChild(d);
        return d;
    }
    function gulung() { msgs.scrollTop = msgs.scrollHeight; }

    function renderBlok(parent, blocks) {
        blocks.forEach(function (b) {
            var n;
            if (b.t === 'p') { n = document.createElement('p'); n.innerHTML = rich(b.x); }
            else if (b.t === 'h') { n = document.createElement('h4'); n.textContent = b.x; }
            else if (b.t === 'ul' || b.t === 'ol') {
                n = document.createElement(b.t);
                b.x.forEach(function (t) { var li = document.createElement('li'); li.innerHTML = rich(t); n.appendChild(li); });
            } else if (b.t === 'jadwal') {
                n = document.createElement('ul'); n.className = 'msg__jadwal';
                P.jadwal.forEach(function (j) { var li = document.createElement('li'); li.innerHTML = '<strong>' + esc(j.kapan) + '</strong>: ' + esc(j.apa); n.appendChild(li); });
            } else if (b.t === 'kartu') {
                var s = byKode[b.kode]; if (!s) { return; }
                var kat = kategoriNilai(s.nilai);
                n = document.createElement('div'); n.className = 'msg__kartu ' + (s.nilai < TARGET ? 'msg__kartu--warn' : 'msg__kartu--ok');
                n.innerHTML = '<div><b>' + esc(s.nama) + '</b><small>Kode ' + esc(s.kode) + ' &middot; Peringkat ' + s.peringkat + '</small></div>' +
                    '<div class="msg__skor"><b>' + fmt(s.nilai) + '</b><small>' + esc(kat.nama) + '</small></div>';
            }
            if (n) { parent.appendChild(n); }
        });
    }

    function setChips(list) {
        chips.innerHTML = '';
        (list || []).forEach(function (c) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'chip-btn';
            if (typeof c === 'string') { b.textContent = c; b.setAttribute('data-q', c); }
            else { b.textContent = c.label; b.setAttribute('data-aksi', c.aksi); if (c.kode) { b.setAttribute('data-kode', c.kode); } }
            chips.appendChild(b);
        });
    }

    function setCtx(kode) {
        ctxKode = kode && byKode[kode] ? kode : null;
        if (!ctxKode) { ctxBar.hidden = true; ctxBar.innerHTML = ''; return; }
        var s = byKode[ctxKode];
        ctxBar.hidden = false;
        ctxBar.innerHTML = '<span><i class="bi bi-building"></i> <b>' + esc(s.nama) + '</b> &middot; ' + fmt(s.nilai) + '</span>' +
            '<button type="button" id="ctxGanti">Ganti</button>';
    }

    function botBalas(hasil, cepat) {
        var tampil = function () {
            var t = msgs.querySelector('.msg--typing'); if (t) { t.remove(); }
            if (hasil.ctx) { setCtx(hasil.ctx); }
            var d = bubble('bot');
            renderBlok(d, hasil.blocks);
            setChips(hasil.chips);
            gulung();
        };
        if (cepat || window.IKPA_FAST) { tampil(); return; }
        var ty = bubble('bot'); ty.className = 'msg msg--bot msg--typing'; ty.innerHTML = '<span></span><span></span><span></span>';
        gulung();
        setTimeout(tampil, 450 + Math.random() * 250);
    }

    function kirim(teks) {
        teks = String(teks || '').trim();
        if (!teks) { return; }
        var u = bubble('user'); u.textContent = teks;
        gulung();
        var hasil;
        try { hasil = jawab(teks); } catch (e) { hasil = { blocks: [{ t: 'p', x: 'Maaf, terjadi kendala saat memproses pertanyaan. Silakan coba lagi.' }], chips: [] }; if (window.console) { console.error(e); } }
        botBalas(hasil);
    }

    function sapa() {
        if (sudahSapa) { return; }
        sudahSapa = true;
        var d = bubble('bot');
        renderBlok(d, [
            { t: 'p', x: 'Halo! Saya **Asisten IKPA**. Saya bisa membantu menjelaskan cara nilai IKPA dihitung dan **langkah menaikkan nilai** sesuai PER-5/PB/2024.' },
            { t: 'p', x: 'Ketik **nama atau kode satker** Anda untuk analisis nilai, atau pilih pertanyaan di bawah.' }
        ]);
        setChips(['Cara menaikkan nilai IKPA', 'Cek nilai satker saya', 'Apa itu IKPA?', 'Jadwal penting']);
    }

    function bukaAsisten(opsi) {
        opsi = opsi || {};
        if (!chat) { return; }
        chat.hidden = false;
        fab.setAttribute('aria-expanded', 'true');
        fab.classList.add('is-open');
        if (fabTip) { fabTip.hidden = true; }
        sapa();
        if (opsi.kode) { setCtx(opsi.kode); }
        if (opsi.kirim) { kirim(opsi.kirim); }
        setTimeout(function () { if (input) { input.focus(); } }, 50);
        gulung();
    }
    function tutupAsisten() {
        if (!chat) { return; }
        chat.hidden = true;
        fab.setAttribute('aria-expanded', 'false');
        fab.classList.remove('is-open');
    }

    if (chat && fab) {
        fab.addEventListener('click', function () { if (chat.hidden) { bukaAsisten({}); } else { tutupAsisten(); } });
        document.getElementById('chatTutup').addEventListener('click', tutupAsisten);
        form.addEventListener('submit', function (e) { e.preventDefault(); var v = input.value; input.value = ''; kirim(v); });

        chips.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) { return; }
            var aksi = b.getAttribute('data-aksi');
            if (aksi === 'rapor' && ctxKode) { bukaRapor(ctxKode); return; }
            if (aksi === 'pilih') {
                var k = b.getAttribute('data-kode');
                var u = bubble('user'); u.textContent = b.textContent;
                botBalas({ ctx: k, ...blokPlan(analisis(byKode[k])) });
                return;
            }
            kirim(b.getAttribute('data-q'));
        });
        ctxBar.addEventListener('click', function (e) {
            if (e.target.id === 'ctxGanti') {
                setCtx(null);
                var d = bubble('bot');
                renderBlok(d, [{ t: 'p', x: 'Silakan **ketik nama atau kode satker** yang ingin dianalisis.' }]);
                setChips(['Cara menaikkan nilai IKPA', 'Apa itu IKPA?']);
                gulung(); input.focus();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') { return; }
        if (modal && !modal.hidden) { tutupRapor(); }
        else if (chat && !chat.hidden) { tutupAsisten(); }
    });

    // gelembung ajakan di dekat tombol asisten
    if (fabTip && fab) {
        setTimeout(function () { if (chat.hidden) { fabTip.hidden = false; setTimeout(function () { fabTip.hidden = true; }, 7000); } }, 4000);
        fabTip.addEventListener('click', function () { bukaAsisten({}); });
    }

    // buka otomatis bila alamat memuat ?satker=KODE
    if (D.bukaSatker && byKode[D.bukaSatker]) {
        setTimeout(function () { bukaRapor(D.bukaSatker); }, 300);
    }

    // untuk pengujian & penggunaan lanjutan
    window.IKPA_ENGINE = { analisis: analisis, jawab: jawab, kategoriNilai: kategoriNilai, setCtx: setCtx, cariSatker: cariSatker };
})();
