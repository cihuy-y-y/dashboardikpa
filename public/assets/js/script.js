/* =============================================================
   script.js
   Interaksi & inisialisasi grafik Dashboard Monitoring IKPA
   Variabel DATA_ASPEK, DATA_TREND, DATA_TOP10, NILAI_AKHIR
   didefinisikan secara inline di index.php sebelum file ini
   dipanggil.
   ============================================================= */


function setupCanvasSize(canvas) {
    const ratio = (window.devicePixelRatio || 1);
    const width = canvas.clientWidth || 320;
    const height = canvas.clientHeight || 220;
    if (canvas.width !== Math.round(width * ratio) || canvas.height !== Math.round(height * ratio)) {
        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
    }
    const ctx = canvas.getContext('2d');
    if (ctx && typeof ctx.setTransform === 'function') {
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    }
    return { ctx, width, height };
}

function createChartInstance(canvas, config) {
    if (typeof window !== 'undefined' && window.Chart && typeof window.Chart === 'function') {
        return new window.Chart(canvas, config);
    }
    return null;
}

document.addEventListener('DOMContentLoaded', function () {

    // ---------- Toggle sidebar (mobile) ----------
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('sidebar');
    if (btnToggle && sidebar) {
        btnToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }

    // Jalankan grafik hanya jika data tersedia di halaman ini
    if (typeof DATA_ASPEK !== 'undefined') {
        initChartKontribusi();
        initChartTrend();
        initChartTop10();
        initGaugeAspek();
    }
});

/**
 * Pie chart: Kontribusi Bobot Terhadap Nilai Akhir
 */
function initChartKontribusi() {
    const ctx = document.getElementById('chartKontribusi');
    if (!ctx) return;

    const perencanaan = parseFloat(DATA_ASPEK.perencanaan.nilai_akhir_aspek) || 0;
    const pelaksanaan = parseFloat(DATA_ASPEK.pelaksanaan.nilai_akhir_aspek) || 0;
    const hasil = parseFloat(DATA_ASPEK.hasil.nilai_akhir_aspek) || 0;

    const labels = [
        'Perencanaan (' + perencanaan.toFixed(2) + ')',
        'Pelaksanaan (' + pelaksanaan.toFixed(2) + ')',
        'Hasil (' + hasil.toFixed(2) + ')',
    ];
    const values = [perencanaan, pelaksanaan, hasil];

    const chartInstance = createChartInstance(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: ['#1e6fd9', '#28a745', '#f5891f'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } }
            }
        }
    });

    if (chartInstance) {
        return;
    }

    renderPieChartFallback(ctx, labels, values, ['#1e6fd9', '#28a745', '#f5891f']);
}

/**
 * Line chart: Trend Nilai Akhir per Bulan
 */
function initChartTrend() {
    const ctx = document.getElementById('chartTrend');
    if (!ctx) return;

    const labels = DATA_TREND.label || [];
    const values = DATA_TREND.data || [];

    const chartInstance = createChartInstance(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Nilai Akhir',
                data: values,
                borderColor: '#1e6fd9',
                backgroundColor: 'rgba(30,111,217,.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#1e6fd9',
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100 } }
        }
    });

    if (chartInstance) {
        return;
    }

    renderLineChartFallback(ctx, labels, values, '#1e6fd9', DATA_TREND.perubahan_label || '');
}

/**
 * Bar chart (horizontal): Top 10 Satker berdasarkan Nilai Akhir
 */
function initChartTop10() {

    const container = document.getElementById("chartTop10");

    if (!container) return;

    if (!DATA_TOP10 || DATA_TOP10.length === 0) {
        container.innerHTML = '<p style="color: #999; text-align: center;">Belum ada data</p>';
        return;
    }

    container.innerHTML = "";

    DATA_TOP10.forEach((item, index) => {

        const div = document.createElement("div");
        div.className = "top10-item";

        const rank = document.createElement("div");
        rank.className = "top10-rank";
        rank.textContent = (index + 1);

        const content = document.createElement("div");
        content.className = "top10-content";

        const bar = document.createElement("div");
        bar.className = "top10-bar";

        const fill = document.createElement("div");
        fill.className = "top10-fill";
        fill.style.width = item.nilai + "%";

        const name = document.createElement("span");
        name.className = "top10-name";
        name.textContent = item.nama;

        const value = document.createElement("span");
        value.className = "top10-value";
        value.textContent = item.nilai.toFixed(2);

        fill.appendChild(name);
        fill.appendChild(value);
        bar.appendChild(fill);
        content.appendChild(bar);

        div.appendChild(rank);
        div.appendChild(content);
        container.appendChild(div);

    });

}

/**
 * Gauge sederhana (doughnut setengah lingkaran) untuk Nilai Aspek
 * pada panel "Kualitas Hasil Pelaksanaan Anggaran"
 */
function initGaugeAspek() {
    const ctx = document.getElementById('gaugeAspek');
    if (!ctx) return;

    const nilai = DATA_ASPEK.hasil.nilai_aspek;

    const chartInstance = createChartInstance(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [nilai, 100 - nilai],
                backgroundColor: ['#28a745', '#eef1f6'],
                borderWidth: 0,
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: '75%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        },
        plugins: [{
            id: 'gaugeText',
            afterDraw(chart) {
                const { ctx, chartArea } = chart;
                ctx.save();
                ctx.font = 'bold 22px Poppins, sans-serif';
                ctx.fillStyle = '#28a745';
                ctx.textAlign = 'center';
                ctx.fillText(
                    nilai.toFixed(2).replace('.', ','),
                    (chartArea.left + chartArea.right) / 2,
                    chartArea.bottom - 10
                );
                ctx.font = '12px Poppins, sans-serif';
                ctx.fillStyle = '#667';
                ctx.fillText(
                    'Nilai Aspek',
                    (chartArea.left + chartArea.right) / 2,
                    chartArea.bottom + 12
                );
                ctx.restore();
            }
        }]
    });

    if (chartInstance) {
        return;
    }

    renderGaugeFallback(ctx, nilai);
}

function renderPieChartFallback(canvas, labels, values, colors) {
    const { ctx, width, height } = setupCanvasSize(canvas);
    ctx.clearRect(0, 0, width, height);

    ctx.fillStyle = '#fbfdff';
    ctx.fillRect(0, 0, width, height);

    const total = values.reduce((sum, value) => sum + Math.max(0, Number(value) || 0), 0);
    if (total <= 0) {
        ctx.fillStyle = '#667';
        ctx.font = '14px Poppins, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Belum ada data', width / 2, height / 2);
        return;
    }

    let start = -Math.PI / 2;
    const radius = Math.min(width, height) * 0.24;
    const centerX = width * 0.34;
    const centerY = height * 0.5;

    values.forEach((value, index) => {
        const slice = (Math.max(0, Number(value) || 0) / total) * (Math.PI * 2);
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, start, start + slice);
        ctx.closePath();
        ctx.fillStyle = colors[index % colors.length];
        ctx.fill();
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#ffffff';
        ctx.stroke();
        start += slice;
    });

    ctx.fillStyle = '#fff';
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius * 0.46, 0, Math.PI * 2);
    ctx.fill();

    ctx.fillStyle = '#223';
    ctx.font = 'bold 11px Poppins, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Kontribusi', centerX, centerY - 2);
    ctx.font = '10px Poppins, sans-serif';
    ctx.fillText('Bobot', centerX, centerY + 10);

    const legendX = width * 0.55;
    const legendY = 28;
    labels.forEach((label, index) => {
        const y = legendY + index * 22;
        ctx.fillStyle = colors[index % colors.length];
        ctx.fillRect(legendX, y - 7, 10, 10);
        ctx.fillStyle = '#555';
        ctx.font = '9px Poppins, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(label, legendX + 16, y);
    });
}

function renderLineChartFallback(canvas, labels, values, color, summaryLabel) {
    const { ctx, width, height } = setupCanvasSize(canvas);
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#fbfdff';
    ctx.fillRect(0, 0, width, height);

    const padding = { top: 20, right: 24, bottom: 40, left: 32 };
    const innerWidth = width - padding.left - padding.right;
    const innerHeight = height - padding.top - padding.bottom;

    ctx.strokeStyle = '#e3e8ef';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = padding.top + (innerHeight / 4) * i;
        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(width - padding.right, y);
        ctx.stroke();
    }

    if (!labels.length || !values.length) {
        ctx.fillStyle = '#667';
        ctx.font = '14px Poppins, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Belum ada data tren', width / 2, height / 2);
        return;
    }

    const maxValue = Math.max(...values, 100);
    const minValue = Math.min(...values, 0);
    const range = maxValue - minValue || 1;
    const points = values.map((value, index) => ({
        x: padding.left + (innerWidth / Math.max(1, values.length - 1)) * index,
        y: padding.top + innerHeight - ((Number(value) - minValue) / range) * innerHeight,
        value: Number(value)
    }));

    ctx.strokeStyle = color;
    ctx.lineWidth = 2.2;
    ctx.beginPath();
    points.forEach((pt, index) => {
        if (index === 0) {
            ctx.moveTo(pt.x, pt.y);
        } else {
            ctx.lineTo(pt.x, pt.y);
        }
    });
    ctx.stroke();

    points.forEach((pt, index) => {
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 3.8, 0, Math.PI * 2);
        ctx.fillStyle = color;
        ctx.fill();

        ctx.font = '10px Poppins, sans-serif';
        ctx.textAlign = 'center';

        if (index === points.length - 1 && summaryLabel) {

            // Nilai 100,00 di atas titik
            ctx.fillStyle = '#334155';
            ctx.fillText(pt.value.toFixed(2), pt.x, pt.y - 10);

            // Persentase di bawah titik
            ctx.fillStyle = '#16a34a';
            ctx.font = 'bold 11px Poppins';
            ctx.fillText(summaryLabel, pt.x, pt.y + 18);

        } else {

            ctx.fillStyle = '#334155';
            ctx.fillText(pt.value.toFixed(2), pt.x, pt.y - 10);

        }

        ctx.fillStyle = '#334155';
        ctx.font = '10px Poppins';
        ctx.fillText(labels[index], pt.x, height - 12);
    });


}

function renderBarChartFallback(canvas, labels, values, color) {
    const { ctx, width, height } = setupCanvasSize(canvas);
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#fbfdff';
    ctx.fillRect(0, 0, width, height);

    if (!labels.length || !values.length) {
        ctx.fillStyle = '#667';
        ctx.font = '14px Poppins, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Belum ada data satker', width / 2, height / 2);
        return;
    }

    const padding = { top: 16, right: 16, bottom: 20, left: 112 };
    const innerWidth = width - padding.left - padding.right;
    const innerHeight = height - padding.top - padding.bottom;
    const maxValue = Math.max(...values, 100);
    const barHeight = Math.max(12, (innerHeight / Math.max(1, labels.length)) - 6);

    labels.forEach((label, index) => {
        const barWidth = (Number(values[index]) / maxValue) * innerWidth;
        const x = padding.left;
        const y = padding.top + index * (barHeight + 6);
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.roundRect(x, y, barWidth, barHeight, 7);
        ctx.fill();

        ctx.fillStyle = '#1f2937';
        ctx.font = '10px Poppins, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(label, 6, y + barHeight / 2 + 3);
        ctx.fillStyle = '#667';
        ctx.font = '9px Poppins, sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText(Number(values[index]).toFixed(2), x + barWidth - 6, y + barHeight / 2 + 3);
    });
}

function renderGaugeFallback(canvas, nilai) {
    const { ctx, width, height } = setupCanvasSize(canvas);
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#fbfdff';
    ctx.fillRect(0, 0, width, height);

    const centerX = width / 2;
    const centerY = height * 0.72;
    const radius = Math.min(width, height - 24) * 0.3;
    const value = Math.max(0, Math.min(100, Number(nilai) || 0));

    ctx.lineWidth = 18;
    ctx.strokeStyle = '#e8ecf3';
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, Math.PI, 0);
    ctx.stroke();

    ctx.strokeStyle = '#28a745';
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, Math.PI, Math.PI + (Math.PI * (value / 100)));
    ctx.stroke();

    ctx.fillStyle = '#28a745';
    ctx.font = 'bold 24px Poppins, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(value.toFixed(2).replace('.', ','), centerX, centerY - 8);
    ctx.font = '12px Poppins, sans-serif';
    ctx.fillStyle = '#667';
    ctx.fillText('Nilai Aspek Hasil', centerX, centerY + 18);
}


$(document).ready(function () {

    $('#detailTable').DataTable({
        pageLength: 10,

        lengthMenu: [
            [10, 50, 100, -1],
            [10, 50, 100, "Semua"]
        ],

        language: {

            lengthMenu: "Tampilkan _MENU_ data",

            search: "Cari Satker :",

            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",

            infoEmpty: "Tidak ada data",

            zeroRecords: "Data tidak ditemukan",

            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "▶",
                previous: "◀"
            }

        }

    });

});