<?php

namespace App\Support;

/**
 * Basis pengetahuan IKPA untuk landing page (panel rincian satker + Asisten IKPA).
 *
 * Sumber: "Petunjuk Teknis Penilaian Kinerja Pelaksanaan Anggaran Tahun 2024
 * (PER-5/PB/2024)", Direktorat Pelaksanaan Anggaran, DJPb.
 *
 * Isi file ini bisa diedit langsung (misalnya bila aturan berubah). Setiap
 * perubahan otomatis ikut tampil di panel rincian dan jawaban asisten.
 *
 * Catatan kata_kunci: tanda "~" di depan berarti "mengandung potongan kata ini"
 * (contoh "~ingkat" cocok dengan "meningkatkan", "peningkatan", "tingkatkan").
 */
class IkpaPanduan
{
    /** Batas nilai IKPA untuk dinyatakan memenuhi target (awal kategori "Baik") */
    public const TARGET = 89;

    public static function data(): array
    {
        return [
            'target'    => self::TARGET,
            'sumber'    => 'PER-5/PB/2024',
            'kategori'  => self::kategori(),
            'indikator' => self::indikator(),
            'dispensasi' => self::dispensasi(),
            'jadwal'    => self::jadwal(),
            'faq'       => self::faq(),
        ];
    }

    /** Kategori nilai IKPA (slide "Sistematika Perdirjen IKPA 2024") */
    public static function kategori(): array
    {
        return [
            ['kode' => 'A', 'nama' => 'Sangat Baik', 'min' => 95,  'aturan' => 'nilai IKPA ≥ 95'],
            ['kode' => 'B', 'nama' => 'Baik',        'min' => 89,  'aturan' => '89 ≤ nilai IKPA < 95'],
            ['kode' => 'C', 'nama' => 'Cukup',       'min' => 70,  'aturan' => '70 ≤ nilai IKPA < 89'],
            ['kode' => 'D', 'nama' => 'Kurang',      'min' => 0,   'aturan' => 'nilai IKPA < 70'],
        ];
    }

    /** Tujuh indikator berbobot + dispensasi SPM sebagai pengurang */
    public static function indikator(): array
    {
        return [
            [
                'key'    => 'revisi',
                'nama'   => 'Revisi DIPA',
                'aspek'  => 'Perencanaan',
                'bobot'  => 10,
                'ikon'   => 'bi-clipboard2-check-fill',
                'ringkas' => 'Mengukur kualitas perencanaan anggaran dari seberapa sering satker merevisi DIPA.',
                'cara'   => [
                    'Dihitung per semester dan tidak kumulatif. Hanya 14 jenis revisi pagu tetap (revisi yang **tidak mengubah pagu** satker) yang diperhitungkan.',
                    'Nilai kinerja revisi (NKRA) per semester: **0–1 revisi = 110**, **2 revisi = 100**, **3 revisi atau lebih = 50**.',
                    'Nilai indikator = 50% × NKRA Semester I + 50% × NKRA Semester II.',
                ],
                'langkah' => [
                    'Reviu DIPA secara periodik (minimal triwulanan) untuk memastikan alokasi Program/Kegiatan/Output sesuai kebutuhan satker.',
                    'Konsolidasikan kebutuhan revisi dan tetapkan batas waktu revisi secara internal. Usahakan **tidak lebih dari 2 revisi per semester**, karena 3 revisi menurunkan nilai kinerja menjadi 50.',
                    'Siapkan lebih awal dokumen yang diperlukan bila masih ada anggaran yang diberi catatan (tanda blokir) di DIPA.',
                ],
                'kata_kunci' => ['revisi dipa', 'revisi', 'nkra', 'pagu tetap', 'blokir'],
            ],
            [
                'key'    => 'deviasi',
                'nama'   => 'Deviasi Halaman III DIPA',
                'aspek'  => 'Perencanaan',
                'bobot'  => 15,
                'ikon'   => 'bi-calendar2-range-fill',
                'ringkas' => 'Mengukur kesesuaian realisasi anggaran bulanan dengan rencana penarikan dana (RPD) pada Halaman III DIPA.',
                'cara'   => [
                    'Deviasi bulanan dihitung per jenis belanja (51, 52, 53, 57), lalu dibobot dengan proporsi pagu tiap jenis belanja.',
                    'Rata-rata deviasi sampai **5,0%** mendapat nilai **100**. Di atas 5,0% nilainya turun sesuai persentase deviasi. Nilai = 100 − rata-rata deviasi tertimbang.',
                    'Dihitung dari Januari sampai November. Data RPD dikunci berdasarkan tanggal posting DIPA hasil revisi pada sistem.',
                ],
                'langkah' => [
                    'Jadikan Halaman III DIPA sebagai alat kendali KPA, dan pastikan seluruh unit kerja melaksanakan kegiatan sesuai rencana yang tercantum di dalamnya.',
                    'Manfaatkan pemutakhiran RPD setiap triwulan. Batas penyampaian: **hari kerja kesepuluh** bulan Februari (TW I), April (TW II), Juli (TW III), dan Oktober (TW IV).',
                    'Jaga deviasi antara pelaksanaan dan rencana **tidak lebih dari 5%** setiap bulan.',
                ],
                'kata_kunci' => ['deviasi', 'halaman iii', 'hal iii', 'halaman 3', 'rpd', 'rencana penarikan dana', 'halaman tiga'],
            ],
            [
                'key'    => 'penyerapan',
                'nama'   => 'Penyerapan Anggaran',
                'aspek'  => 'Implementasi Pelaksanaan',
                'bobot'  => 20,
                'ikon'   => 'bi-cash-coin',
                'ringkas' => 'Mengukur tingkat penyerapan anggaran dibandingkan target penyerapan triwulanan untuk tiap jenis belanja.',
                'cara'   => [
                    'Nilai tiap triwulan = rata-rata tertimbang (menurut proporsi pagu) dari rasio penyerapan terhadap target tiap jenis belanja. Rasio di atas 100% dibatasi 100.',
                    'Target kumulatif (TW I / II / III / IV): **Belanja Pegawai** 20 / 50 / 75 / 95%, **Barang** 15 / 50 / 70 / 90%, **Modal** 10 / 40 / 70 / 90%, **Bansos** 25 / 50 / 75 / 95%.',
                    'Nilai indikator = rata-rata nilai kinerja seluruh triwulan. Satker BLU tidak termasuk objek penilaian.',
                ],
                'langkah' => [
                    'Tingkatkan kualitas perencanaan dan eksekusi kegiatan agar relevan dan terjadwal, serta **tidak menumpuk pencairan** di akhir tahun.',
                    'Percepat belanja, terutama belanja barang dan modal yang proses pengadaannya dapat dimulai sejak awal tahun anggaran.',
                    'Jaga penyerapan tetap proporsional setiap bulan sesuai target, rencana kegiatan, dan RPD yang telah disusun.',
                ],
                'kata_kunci' => ['penyerapan', 'serap', 'realisasi anggaran', 'target penyerapan', 'nkpat'],
            ],
            [
                'key'    => 'kontraktual',
                'nama'   => 'Belanja Kontraktual',
                'aspek'  => 'Implementasi Pelaksanaan',
                'bobot'  => 10,
                'ikon'   => 'bi-file-earmark-text-fill',
                'ringkas' => 'Mengukur kecepatan dan sebaran penandatanganan kontrak lewat tiga komponen: kontrak dini, akselerasi, dan distribusi.',
                'cara'   => [
                    'Tiga komponen: **Kontrak Pra DIPA (40%)**, **Akselerasi Kontrak 53 (40%)**, dan **Distribusi Akselerasi Kontrak (20%)**. Hanya kontrak bernilai **Rp50 juta ke atas** yang dihitung.',
                    'Kontrak Pra DIPA (tanggal kontrak sebelum 1 Januari) bernilai **120**. Kontrak tanggal 1 Januari–31 Maret bernilai **110**.',
                    'Akselerasi 53: kontrak belanja modal Rp50–200 juta yang **selesai sampai 31 Maret** bernilai 100. Selesai di TW II / III / IV bernilai 90 / 80 / 70.',
                    'Distribusi: rasio kontrak yang terbit sampai TW II terhadap seluruh kontrak setahun. Di atas 75% = 100, 50,01–75% = 80, 25,01–50% = 60, 0,01–25% = 50, 0% = 0.',
                ],
                'langkah' => [
                    'Upayakan proses pengadaan barang/jasa dilaksanakan **sebelum tahun anggaran dimulai**, agar kontrak dapat ditandatangani dan pekerjaan berjalan di awal tahun.',
                    'Pastikan pengadaan barang/jasa yang sifatnya sekaligus dengan nilai **sampai Rp200 juta** diselesaikan pada Triwulan I.',
                    'Segera susun RUP di awal tahun sesuai rencana kegiatan yang akan dilaksanakan di awal tahun anggaran.',
                    'Pastikan seluruh pengadaan yang memungkinkan sudah **ditandatangani dan didaftarkan paling lambat Semester I**.',
                ],
                'kata_kunci' => ['kontraktual', 'kontrak', 'pra dipa', 'pradipa', 'akselerasi', 'pbj', 'pengadaan', 'rup'],
            ],
            [
                'key'    => 'tagihan',
                'nama'   => 'Penyelesaian Tagihan',
                'aspek'  => 'Implementasi Pelaksanaan',
                'bobot'  => 10,
                'ikon'   => 'bi-receipt-cutoff',
                'ringkas' => 'Mengukur ketepatan waktu penyampaian tagihan SPM LS Kontraktual ke KPPN.',
                'cara'   => [
                    'Nilai = jumlah SPM LS Kontraktual yang tepat waktu ÷ seluruh SPM LS Kontraktual × 100 (belanja non pegawai).',
                    'Tepat waktu: SPM diterima KPPN paling lambat **17 hari kerja** sejak tanggal BAST/BAPP.',
                    'Tanggal BAST/BAPP yang dipakai adalah tanggal yang dipilih satker pada modul Komitmen SAKTI.',
                ],
                'langkah' => [
                    'Segera selesaikan pembayaran dan **jangan menunda** proses tagihan untuk pekerjaan yang sudah selesai (termasuk pekerjaan termin).',
                    'Pantau batas **17 hari kerja** sejak timbulnya hak tagih kepada negara.',
                    'Periksa kembali ketepatan input tanggal BAST/BAPP pada modul Komitmen SAKTI sebelum SPM diajukan.',
                ],
                'kata_kunci' => ['tagihan', 'bast', 'bapp', 'spm ls', 'ls kontraktual', '17 hari', 'hak tagih', 'termin'],
            ],
            [
                'key'    => 'uptup',
                'nama'   => 'Pengelolaan UP dan TUP',
                'aspek'  => 'Implementasi Pelaksanaan',
                'bobot'  => 10,
                'ikon'   => 'bi-wallet2',
                'ringkas' => 'Mengukur disiplin pengelolaan Uang Persediaan (UP), Tambahan UP (TUP), dan penggunaan Kartu Kredit Pemerintah (KKP).',
                'cara'   => [
                    'Nilai = **90% × komponen UP/TUP Tunai** + **10% × komponen UP KKP**.',
                    'Komponen Tunai: ketepatan waktu **50%**, %GUP disebulankan **25%**, dan setoran TUP **25%**. Ketepatan waktu dinilai dari SP2D GUP/GUP Nihil/GTUP yang disampaikan ke KPPN dalam 1 bulan. Ada penalti bila setoran UP/TUP belum disampaikan sampai 31 Desember.',
                    'Setoran TUP: nilai = 100 − (setoran TUP ÷ total TUP × 100), jadi makin kecil TUP yang harus disetor kembali, makin tinggi nilainya.',
                    'Komponen KKP: target transaksi per triwulan 1%, 5%, 9%, 12,5% dari UP KKP yang disetahunkan. Target tercapai = 110, belum tercapai = 100. Satker tanpa UP KKP atau tanpa transaksi KKP dinilai dengan konversi 90%.',
                ],
                'langkah' => [
                    'Hitung kembali kebutuhan operasional bulanan dan ajukan UP Tunai **secara rasional** sesuai kebutuhan bulanan.',
                    'Gunakan UP Tunai secara efektif dan efisien dengan mempercepat revolving UP Tunai **paling sedikit 100% dalam satu bulan**.',
                    'Prioritaskan penggunaan **UP KKP** untuk memenuhi kebutuhan operasional satker.',
                    'Ajukan TUP sesuai kebutuhan sehingga sisa yang harus disetor kembali kecil.',
                ],
                'kata_kunci' => ['up dan tup', 'up tup', 'uang persediaan', 'tambahan up', 'tup', 'gup', 'gtup', 'kkp', 'kartu kredit', 'revolving', 'setoran', 'up tunai', 'sp2d gup'],
            ],
            [
                'key'    => 'output',
                'nama'   => 'Capaian Output',
                'aspek'  => 'Hasil',
                'bobot'  => 25,
                'ikon'   => 'bi-bullseye',
                'ringkas' => 'Mengukur ketepatan waktu pelaporan dan capaian Rincian Output (RO) satker. Bobotnya terbesar di antara seluruh indikator.',
                'cara'   => [
                    'Nilai = **30% ketepatan waktu pelaporan** + **70% capaian RO**.',
                    'Ketepatan waktu: laporan capaian output bulanan disampaikan paling lambat **5 hari kerja** pada bulan berikutnya = 100, terlambat = 0.',
                    'Capaian RO: Januari–November = PCRO ÷ Target PCRO (bila PCRO sudah 100%, dihitung RVRO ÷ volume RO pada DIPA). Desember = RVRO ÷ volume RO pada DIPA.',
                    'RO yang **belum terkonfirmasi bernilai 0**. PCRO kumulatif 0% juga bernilai 0 walaupun targetnya 0%. Target PCRO diisi satker pada Modul Target Kinerja.',
                ],
                'langkah' => [
                    'Tetapkan target dan metode perhitungan capaian output untuk setiap RO yang dikelola, khususnya output teknis.',
                    'Hitung secara periodik tingkat kemajuan aktivitas (progres/PCRO) dan capaian (realisasi volume RO). Perhatikan gap antara progres capaian output dan penyerapan anggaran.',
                    'Isi data capaian output bulanan secara **akurat dan disiplin** sebelum batas akhir open period reguler (5 hari kerja setelah bulan berakhir).',
                    'Pantau status data di aplikasi **OMSPAN** dan pastikan seluruh RO berstatus **Terkonfirmasi**.',
                ],
                'kata_kunci' => ['capaian output', 'output', 'rincian output', 'pcro', 'rvro', 'omspan', 'terkonfirmasi', 'target kinerja', 'ro'],
            ],
        ];
    }

    /** Dispensasi SPM: bukan indikator berbobot, melainkan pengurang nilai akhir */
    public static function dispensasi(): array
    {
        return [
            'key'    => 'dispensasi',
            'nama'   => 'Dispensasi SPM',
            'ikon'   => 'bi-exclamation-octagon-fill',
            'ringkas' => 'Bukan indikator berbobot. Nilainya menjadi pengurang nilai akhir IKPA.',
            'cara'   => [
                'Rasio dispensasi = SPM dispensasi ÷ SPM triwulan IV × 1.000 (permil).',
                'Pengurang nilai: tidak ada dispensasi = **0**, 0,01–0,099 = **0,25**, 0,1–0,99 = **0,50**, 1–4,99 = **0,75**, 5 atau lebih = **1,00** poin.',
            ],
            'langkah' => [
                'Pantau progres penyelesaian kegiatan sesuai rencana dengan memperhatikan batas akhir penyampaian SPM pada akhir tahun anggaran.',
                'Tetapkan mitigasi risiko penyelesaian pekerjaan dan pembayaran menjelang akhir tahun anggaran.',
                'Hitung prognosis belanja agar dapat dieksekusi tepat waktu dan tidak menumpuk pencairan di akhir tahun.',
            ],
            'kata_kunci' => ['dispensasi', 'dispensasi spm', 'pengurang', 'permil', 'spm akhir tahun'],
        ];
    }

    /** Kalender hal-hal yang tidak boleh terlewat */
    public static function jadwal(): array
    {
        return [
            ['kapan' => 'Setiap bulan',                        'apa' => 'Laporkan capaian output paling lambat 5 hari kerja pada bulan berikutnya, dan pastikan status RO Terkonfirmasi di OMSPAN.', 'ikon' => 'bi-bullseye'],
            ['kapan' => 'Setiap bulan',                        'apa' => 'Sampaikan SP2D GUP/GUP Nihil/GTUP ke KPPN tepat waktu (dalam 1 bulan).', 'ikon' => 'bi-wallet2'],
            ['kapan' => 'Setiap SPM LS Kontraktual',           'apa' => 'Sampaikan ke KPPN paling lambat 17 hari kerja sejak tanggal BAST/BAPP.', 'ikon' => 'bi-receipt-cutoff'],
            ['kapan' => 'Hari kerja ke-10: Feb, Apr, Jul, Okt', 'apa' => 'Batas pemutakhiran RPD Halaman III DIPA untuk triwulan yang baru dimulai.', 'ikon' => 'bi-calendar2-range-fill'],
            ['kapan' => 'Sampai 31 Maret (Triwulan I)',        'apa' => 'Selesaikan kontrak belanja modal Rp50–200 juta agar masuk akselerasi, dan tandatangani kontrak lebih awal.', 'ikon' => 'bi-file-earmark-text-fill'],
            ['kapan' => 'Semester I',                          'apa' => 'Tandatangani dan daftarkan seluruh kontrak yang memungkinkan.', 'ikon' => 'bi-pen-fill'],
            ['kapan' => 'Menjelang 31 Desember',               'apa' => 'Sampaikan setoran UP/TUP, hindari penumpukan SPM akhir tahun, dan minimalkan dispensasi SPM.', 'ikon' => 'bi-exclamation-octagon-fill'],
        ];
    }

    /** Pertanyaan umum untuk Asisten IKPA */
    public static function faq(): array
    {
        return [
            [
                'id' => 'apa_itu_ikpa',
                'judul' => 'Apa itu IKPA?',
                'kata_kunci' => ['apa itu ikpa', 'pengertian ikpa', 'arti ikpa', 'ikpa itu', 'definisi ikpa', 'maksud ikpa', 'tujuan ikpa', 'untuk apa ikpa', 'fungsi ikpa', 'manfaat ikpa'],
                'jawaban' => [
                    ['t' => 'p', 'x' => '**IKPA (Indikator Kinerja Pelaksanaan Anggaran)** adalah kumpulan indikator yang menggambarkan kinerja pelaksanaan anggaran, baik dari sisi *input* maupun *output* yang dihasilkan.'],
                    ['t' => 'ul', 'x' => [
                        'Menjadi alat monitoring dan evaluasi agar belanja negara menghasilkan nilai atau manfaat bagi masyarakat (*value for money*).',
                        'Dimanfaatkan sebagai instrumen monev Menteri Keuangan, salah satu indikator penilaian reformasi birokrasi K/L, serta komponen penilaian untuk penghargaan dan sanksi.',
                        'Aturan penilaian tahun 2024 mengacu pada **PER-5/PB/2024**.',
                    ]],
                ],
                'chips' => ['Bagaimana rumus nilai IKPA?', 'Apa saja kategori nilai IKPA?', 'Cara menaikkan nilai IKPA'],
            ],
            [
                'id' => 'rumus',
                'judul' => 'Rumus nilai IKPA',
                'kata_kunci' => ['rumus', 'formula', 'cara hitung nilai ikpa', 'hitung nilai ikpa', 'perhitungan nilai ikpa', 'nilai akhir', 'bobot', 'bobotnya', 'komponen nilai', 'aspek'],
                'jawaban' => [
                    ['t' => 'p', 'x' => '**Nilai IKPA = Σ ((nilai indikator × bobot) ÷ konversi bobot) − pengurang dispensasi SPM**'],
                    ['t' => 'p', 'x' => 'Bobot indikator tahun 2024:'],
                    ['t' => 'ul', 'x' => [
                        '**Perencanaan (25%)**: Revisi DIPA 10%, Deviasi Halaman III DIPA 15%',
                        '**Implementasi pelaksanaan (50%)**: Penyerapan Anggaran 20%, Belanja Kontraktual 10%, Penyelesaian Tagihan 10%, Pengelolaan UP dan TUP 10%',
                        '**Hasil (25%)**: Capaian Output 25%',
                        '**Dispensasi SPM**: tidak berbobot, menjadi pengurang nilai akhir',
                    ]],
                ],
                'chips' => ['Apa itu konversi bobot?', 'Apa saja kategori nilai IKPA?', 'Cara menaikkan nilai IKPA'],
            ],
            [
                'id' => 'kategori',
                'judul' => 'Kategori nilai',
                'kata_kunci' => ['kategori', 'sangat baik', 'predikat', 'batas', 'target', '89', 'memenuhi target', 'cukup', 'kurang', 'minimal nilai', 'standar'],
                'jawaban' => [
                    ['t' => 'p', 'x' => 'Kategori nilai IKPA menurut PER-5/PB/2024:'],
                    ['t' => 'ul', 'x' => [
                        '**A. Sangat Baik**: nilai ≥ 95',
                        '**B. Baik**: 89 ≤ nilai < 95',
                        '**C. Cukup**: 70 ≤ nilai < 89',
                        '**D. Kurang**: nilai < 70',
                    ]],
                    ['t' => 'p', 'x' => 'Karena itu dashboard ini memakai **89** sebagai batas target: satker bernilai di bawah 89 masuk kategori Cukup atau Kurang dan ditandai **Perlu Perhatian**.'],
                ],
                'chips' => ['Cara menaikkan nilai IKPA', 'Bagaimana rumus nilai IKPA?'],
            ],
            [
                'id' => 'konversi',
                'judul' => 'Konversi bobot',
                'kata_kunci' => ['konversi', 'konversi bobot', 'konversi bobot 100', 'data transaksi'],
                'jawaban' => [
                    ['t' => 'p', 'x' => '**Konversi bobot** menyesuaikan perhitungan bila satker tidak memiliki transaksi untuk indikator tertentu.'],
                    ['t' => 'ul', 'x' => [
                        'Bernilai **100%** bila satker memiliki seluruh data transaksi atas indikator yang dinilai.',
                        'Bernilai **di bawah 100%** bila satker tidak memiliki data transaksi untuk indikator tertentu (misalnya tidak ada UP KKP atau tidak ada kontrak).',
                    ]],
                ],
                'chips' => ['Bagaimana rumus nilai IKPA?', 'Cara menaikkan nilai IKPA'],
            ],
            [
                'id' => 'penyesuaian',
                'judul' => 'Penyesuaian data',
                'kata_kunci' => ['penyesuaian', 'permohonan', 'exception', 'koreksi', 'salah data', 'data salah', 'keberatan', 'ajukan', 'pengajuan', 'bencana', 'force majeur', 'ba bun', 'minta ubah'],
                'jawaban' => [
                    ['t' => 'p', 'x' => 'Penyesuaian perhitungan data dan transaksi IKPA (*exception*) dapat dilakukan melalui 2 mekanisme:'],
                    ['t' => 'ol', 'x' => [
                        '**Ditetapkan Direktur Pelaksanaan Anggaran tanpa permohonan satker**, bila ada gangguan/kendala sistem (pembaruan, transisi aplikasi, migrasi data) atau kondisi *force majeure*.',
                        '**Berdasarkan permohonan satker**, untuk alasan tertentu per indikator.',
                    ]],
                    ['t' => 'p', 'x' => 'Contoh alasan yang dapat diajukan:'],
                    ['t' => 'ul', 'x' => [
                        '**Deviasi Hal III**: keterlambatan posting revisi Halaman III DIPA oleh Kanwil/Direktorat PA/DJA, perubahan alokasi karena kebijakan khusus K/L atau penambahan dari BA BUN.',
                        '**Penyerapan Anggaran**: perubahan alokasi (kebijakan khusus K/L atau BA BUN) dan kegiatan penanganan bencana.',
                        '**Belanja Kontraktual**: tambahan alokasi, BA BUN, kontrak terjadwal yang tidak dapat diakselerasi, atau penanganan bencana.',
                        '**Penyelesaian Tagihan**: kesalahan input tanggal BAST/BAPP di SAKTI, atau keterlambatan pembayaran akibat kebijakan penganggaran/pelaksanaan anggaran.',
                    ]],
                    ['t' => 'p', 'x' => 'Indikator Revisi DIPA, Pengelolaan UP dan TUP, Capaian Output, dan Dispensasi SPM tidak memiliki alasan permohonan penyesuaian. Ajukan melalui KPPN/Kanwil DJPb setempat.'],
                ],
                'chips' => ['Cara menaikkan nilai IKPA', 'Kapan saja jadwal pentingnya?'],
            ],
            [
                'id' => 'jadwal',
                'judul' => 'Jadwal penting',
                'kata_kunci' => ['jadwal', 'deadline', 'batas waktu', 'kapan', 'tenggat', 'kalender', 'tanggal', 'waktu', 'hari kerja', 'open period'],
                'jawaban' => [
                    ['t' => 'p', 'x' => 'Jadwal penting yang berpengaruh pada nilai IKPA:'],
                    ['t' => 'jadwal'],
                ],
                'chips' => ['Cara menaikkan nilai IKPA', 'Apa itu Capaian Output?'],
            ],
            [
                'id' => 'reformulasi',
                'judul' => 'Perubahan formula 2024',
                'kata_kunci' => ['reformulasi', 'perubahan 2024', 'beda 2024', 'berubah', 'baru 2024', 'dibanding 2022', '2022', '2023', 'per 5', 'per-5', 'perdirjen', 'aturan baru'],
                'jawaban' => [
                    ['t' => 'p', 'x' => 'Reformulasi IKPA 2024 (PER-5/PB/2024) mengubah **6 indikator**:'],
                    ['t' => 'ul', 'x' => [
                        '**Revisi DIPA**: pengendalian revisi pagu tetap menjadi *semesteran* (sebelumnya triwulanan).',
                        '**Deviasi Hal III**: memakai rata-rata *tertimbang* proporsi pagu jenis belanja (sebelumnya rata-rata aritmatik).',
                        '**Penyerapan**: dinilai per jenis belanja berdasarkan trajektori dan dibobot proporsi pagu.',
                        '**Belanja Kontraktual**: berdasarkan kontrak dini (Pra DIPA), akselerasi kontrak 53, dan distribusi kontrak sampai TW II.',
                        '**UP dan TUP**: ada *reward* untuk penggunaan UP KKP yang mencapai target.',
                        '**Dispensasi SPM**: dihitung di luar komponen nilai, yaitu sebagai pengurang nilai IKPA.',
                    ]],
                    ['t' => 'p', 'x' => 'Penyelesaian Tagihan dan Capaian Output tetap.'],
                ],
                'chips' => ['Bagaimana rumus nilai IKPA?', 'Cara menaikkan nilai IKPA'],
            ],
            [
                'id' => 'cara_pakai',
                'judul' => 'Cara melihat nilai satker',
                'kata_kunci' => ['cara cek', 'cek nilai', 'lihat nilai', 'cari satker', 'rincian', 'rapor', 'cara pakai', 'cara menggunakan', 'bantuan', 'help', 'menu'],
                'jawaban' => [
                    ['t' => 'ol', 'x' => [
                        'Ketik kode atau nama satker di kolom pencarian, atau pilih dari **Daftar Nilai IKPA Seluruh Satker**.',
                        'Klik tombol **Rincian** pada satker Anda untuk melihat nilai tiap indikator.',
                        'Bila nilai di bawah 89, panel rincian menampilkan **langkah prioritas** untuk menaikkan nilai.',
                        'Ketik nama atau kode satker di sini juga bisa, nanti saya bantu analisisnya.',
                    ]],
                ],
                'chips' => ['Cara menaikkan nilai IKPA', 'Apa itu IKPA?'],
            ],
            [
                'id' => 'kontak',
                'judul' => 'Bantuan lebih lanjut',
                'kata_kunci' => ['kontak', 'hubungi', 'telepon', 'telpon', 'whatsapp', 'wa', 'alamat', 'konsultasi', 'tanya langsung', 'kppn', 'kanwil', 'petugas'],
                'jawaban' => [
                    ['t' => 'p', 'x' => 'Untuk konsultasi langsung, permohonan penyesuaian data, atau pertanyaan di luar panduan ini, silakan hubungi **KPPN** atau **Kanwil DJPb** setempat.'],
                    ['t' => 'p', 'x' => 'Perhitungan resmi mengikuti PER-5/PB/2024 dan data pada aplikasi OMSPAN/SAKTI. Jawaban di sini bersifat panduan.'],
                ],
                'chips' => ['Cara menaikkan nilai IKPA', 'Kapan saja jadwal pentingnya?'],
            ],
        ];
    }
}
