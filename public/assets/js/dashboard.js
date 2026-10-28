/* =============================================================
   dashboard.js
   Inisialisasi grafik untuk halaman Dashboard (dashboard.php)
   Variabel DATA_PERKEMBANGAN, DATA_RATA_INDIKATOR,
   DATA_KONTRIBUSI_ASPEK, DATA_DISTRIBUSI_NILAI,
   DATA_TOP10_TERBAIK, DATA_TOP10_TERBAWAH
   didefinisikan secara inline di dashboard.php.
   ============================================================= */

document.addEventListener('DOMContentLoaded', function () {
    if (typeof DATA_PERKEMBANGAN === 'undefined') return;

    if (typeof window.Chart !== 'undefined') {
        initChartPerkembangan();
        initChartKontribusiAspek();
        initChartRataIndikator();
        initChartDistribusiNilai();
        initChartTop10Terbaik();
        initChartTop10Terbawah();
    } else {
        renderFallbackCharts();
    }

});


function renderFallbackCharts() {
    if (typeof DATA_PERKEMBANGAN !== 'undefined') {
        renderFallbackLineChart('chartPerkembangan', DATA_PERKEMBANGAN.label || [], DATA_PERKEMBANGAN.data || []);
    }

    if (typeof DATA_KONTRIBUSI_ASPEK !== 'undefined') {
        renderFallbackDonutChart('chartKontribusiAspek', DATA_KONTRIBUSI_ASPEK);
    }

    if (typeof DATA_RATA_INDIKATOR !== 'undefined') {
        renderFallbackBarChart('chartRataIndikator', DATA_RATA_INDIKATOR.map(function (item) { return item.nama; }), DATA_RATA_INDIKATOR.map(function (item) { return item.rata2; }), '#7c3aed');
    }

    if (typeof DATA_DISTRIBUSI_NILAI !== 'undefined') {
        renderFallbackBarChart('chartDistribusiNilai', DATA_DISTRIBUSI_NILAI.label || [], DATA_DISTRIBUSI_NILAI.data || [], '#f5891f');
    }

    if (typeof DATA_TOP10_TERBAIK !== 'undefined') {
        renderFallbackHorizontalBarChart('chartTop10Terbaik', DATA_TOP10_TERBAIK, '#28a745');
    }

    if (typeof DATA_TOP10_TERBAWAH !== 'undefined') {
        renderFallbackHorizontalBarChart('chartTop10Terbawah', DATA_TOP10_TERBAWAH, '#dc3545');
    }
}

function getChartHost(id) {
    const host = document.getElementById(id);
    if (!host) return null;

    host.style.height = '300px';
    host.style.minHeight = '300px';
    host.style.width = '100%';

    return host;
}

function renderFallbackLineChart(id, labels, values) {
    const host = document.getElementById(id);
    if (!host) return;

    const max = Math.max(...values, 100);
    const min = Math.min(...values, 0);
    const points = values.map(function (value, index) {
        const x = 8 + (index / Math.max(1, values.length - 1)) * 84;
        const y = 86 - ((value - min) / Math.max(1, max - min)) * 70;
        return x + ',' + y;
    }).join(' ');

    const labelsMarkup = labels.map(function (label) {
        return '<span class="chart-fallback__label">' + label + '</span>';
    }).join('');

    host.outerHTML = '<div class="chart-fallback chart-fallback--line"><svg viewBox="0 0 100 100" class="chart-fallback__svg"><polyline points="' + points + '" fill="none" stroke="#1e6fd9" stroke-width="3"></polyline><line x1="8" y1="86" x2="92" y2="86" stroke="#dfe6f0" stroke-width="1"></line></svg><div class="chart-fallback__labels">' + labelsMarkup + '</div></div>';
}

function renderFallbackBarChart(id, labels, values, color) {
    const host = document.getElementById(id);
    if (!host) return;
    const max = Math.max(...values, 1);
    const bars = labels.map(function (label, index) {
        const ratio = values[index] / max;
        return `
        <div class="chart-fallback__bar-row">
            <div class="chart-fallback__bar-name">${label}</div>
            <div class="chart-fallback__track">
                <div class="chart-fallback__fill"
                    style="width:${Math.max(6, ratio * 100)}%;
                    background:${color}">
                </div>
            </div>
            <div class="chart-fallback__bar-value">
                ${values[index]}
            </div>
        </div>`;
    }).join('');
    host.outerHTML =
        `<div id="${id}" class="chart-fallback chart-fallback--bars">${bars}</div>`;
}

function renderFallbackHorizontalBarChart(id, data, color) {
    const host = document.getElementById(id);
    if (!host) return;

    const values = (data || []).map(function (item) {
        return Number(item.nilai_akhir ?? item.nilai ?? 0);
    });
    const max = Math.max(...values, 100);
    const rows = (data || []).map(function (item, index) {
        const value = Number(item.nilai_akhir ?? item.nilai ?? 0);
        const width = Math.max(8, Math.round((value / max) * 100));
        return '<div class="chart-fallback__bar-row"><div class="chart-fallback__bar-name">' + (item.nama || 'Satker ' + (index + 1)) + '</div><div class="chart-fallback__track"><div class="chart-fallback__fill" style="width:' + width + '%;background:' + color + '"></div></div><div class="chart-fallback__bar-value">' + value + '</div></div>';
    }).join('');

    host.outerHTML = '<div class="chart-fallback chart-fallback--bars">' + rows + '</div>';
}

function renderFallbackDonutChart(id, data) {
    const host = document.getElementById(id);
    if (!host) return;

    const values = (data || []).map(function (item) { return Number(item.nilai || 0); });
    const total = values.reduce(function (sum, value) { return sum + value; }, 0) || 1;
    let offset = 0;
    const segments = (data || []).map(function (item, index) {
        const color = ['#1e6fd9', '#28a745', '#f5891f'][index] || '#7c3aed';
        const percent = (Number(item.nilai || 0) / total) * 100;
        const dash = (percent / 100) * 251.2;
        const segment = '<circle cx="50" cy="50" r="40" fill="none" stroke="' + color + '" stroke-width="12" stroke-dasharray="' + dash + ' 251.2" stroke-dashoffset="-' + offset + '" transform="rotate(-90 50 50)"></circle>';
        offset += dash;
        return segment;
    }).join('');

    const legend = (data || []).map(function (item, index) {
        const color = ['#1e6fd9', '#28a745', '#f5891f'][index] || '#7c3aed';
        return '<div class="chart-fallback__legend-item"><span class="chart-fallback__dot" style="background:' + color + '"></span><span>' + (item.nama || '') + '</span></div>';
    }).join('');

    host.outerHTML = '<div class="chart-fallback chart-fallback--donut"><svg viewBox="0 0 100 100" class="chart-fallback__svg chart-fallback__svg--donut"><circle cx="50" cy="50" r="40" fill="none" stroke="#e8edf5" stroke-width="12"></circle>' + segments + '</svg><div class="chart-fallback__legend">' + legend + '</div></div>';
}

/**
 * 1. Grafik Perkembangan Nilai IKPA per Bulan (line chart)
 */
function initChartPerkembangan() {
    const ctx = getChartHost('chartPerkembangan');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: DATA_PERKEMBANGAN.label,
            datasets: [{
                data: DATA_PERKEMBANGAN.data,
                borderColor: '#1e6fd9',
                backgroundColor: 'rgba(30,111,217,.15)',
                fill: false,
                borderWidth: 3,
                tension: 0.35,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#1e6fd9',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            
            layout: {
            padding: {
            top: 20,
            right: 35,   // tambahkan ini
            left: 10,
            bottom: 10
            }
            
        },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return context.raw.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#475569',
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        color: '#64748b'
                    },
                    grid: {
                        color: '#e2e8f0'
                    }
                }
            }
        },
        plugins: [{
            id: 'labelValue',
            afterDatasetsDraw(chart) {
                const {
                    ctx
                } = chart;
                const meta = chart.getDatasetMeta(0);
                const values = chart.data.datasets[0].data;
                ctx.save();
                meta.data.forEach((point, index) => {
                    // angka di atas titik
                    ctx.fillStyle = '#334155';
                    ctx.font = '11px Poppins';
                    ctx.textAlign = 'center';
                    ctx.fillText(
                        Number(values[index]).toFixed(2),
                        point.x,
                        point.y - 12
                    );
                    // label persen di titik terakhir
                    if (index === values.length - 1 && DATA_PERKEMBANGAN.perubahan_label) {
                        ctx.fillStyle = '#16a34a';
                        ctx.font = 'bold 12px Poppins';
                        ctx.fillText(
                            DATA_PERKEMBANGAN.perubahan_label,
                            point.x,
                            point.y + 20
                        );
                    }
                });
                ctx.restore();
            }
        }]
    });
}

/**
 * 2. Grafik Rata-rata Tiap Indikator (bar chart)
 */
function initChartRataIndikator() {
    const ctx = getChartHost('chartRataIndikator');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: DATA_RATA_INDIKATOR.map(i => i.nama),
            datasets: [{
                label: 'Rata-rata Nilai',
                data: DATA_RATA_INDIKATOR.map(i => i.rata2),
                backgroundColor: '#7c3aed',
                borderRadius: 4,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100 },
                x: { ticks: { autoSkip: false, maxRotation: 40, minRotation: 20, font: { size: 10 } } }
            }
        }
    });
}

/**
 * 3. Grafik Kontribusi Tiga Aspek (doughnut chart)
 */
function initChartKontribusiAspek() {
    const ctx = getChartHost('chartKontribusiAspek');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: DATA_KONTRIBUSI_ASPEK.map(a => a.nama + ' (' + a.bobot + '%)'),
            datasets: [{
                data: DATA_KONTRIBUSI_ASPEK.map(a => a.nilai),
                backgroundColor: ['#1e6fd9', '#28a745', '#f5891f'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
}

/**
 * 4. Grafik Distribusi Nilai Satker (bar chart / histogram sederhana)
 */
function initChartDistribusiNilai() {
    const ctx = getChartHost('chartDistribusiNilai');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: DATA_DISTRIBUSI_NILAI.label,
            datasets: [{
                label: 'Jumlah Satker',
                data: DATA_DISTRIBUSI_NILAI.data,
                backgroundColor: '#f5891f',
                borderRadius: 4,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

/**
 * 5a. Grafik 10 Satker Terbaik (horizontal bar, hijau)
 */
function initChartTop10Terbaik() {
    const host = getChartHost('chartTop10Terbaik');
    if (!host) return;

    new Chart(host, {
        type: 'bar',
        data: {
            labels: DATA_TOP10_TERBAIK.map(s => s.nama),
            datasets: [{
                label: 'Nilai Akhir',
                data: DATA_TOP10_TERBAIK.map(s => s.nilai),
                backgroundColor: '#28a745',
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { min: 0, max: 100 } }
        }
    });
}

/**
 * 5b. Grafik 10 Satker Terbawah (horizontal bar, merah)
 */
function initChartTop10Terbawah() {
    const host = getChartHost('chartTop10Terbawah');
    if (!host) return;

    new Chart(host, {
        type: 'bar',
        data: {
            labels: DATA_TOP10_TERBAWAH.map(s => s.nama),
            datasets: [{
                label: 'Nilai Akhir',
                data: DATA_TOP10_TERBAWAH.map(s => s.nilai),
                backgroundColor: '#dc3545',
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { min: 0, max: 100 } }
        }
    });
}
