<style>
    .stat-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }

    .stat-card .card-body {
        padding: 1.25rem 1.5rem;
    }

    .stat-card .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #000 !important;
        margin-bottom: 0.4rem;
    }

    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #000 !important;
        line-height: 1;
        margin: 0;
    }

    .stat-card .stat-icon {
        font-size: 1.8rem;
        opacity: 0.15;
    }

    .stat-card-total   { background: #f0f4ff; border-left: 4px solid #4f81ff; }
    .stat-card-duha   { background: #e8f5e9; border-left: 4px solid #4caf50; }
    .stat-card-dzuhur { background: #fff3e0; border-left: 4px solid #ff9800; }
    .stat-card-blm-absen { background: #fce4ec; border-left: 4px solid #e91e63; }

    .chart-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07) !important;
    }

    .chart-card .card-body {
        padding: 1.5rem;
    }

    .chart-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #000;
        margin-bottom: 1rem;
    }

    @media (max-width: 576px) {
        .stat-card .stat-value {
            font-size: 1.7rem;
        }
        .stat-card .card-body {
            padding: 1rem 1.2rem;
        }
        .stats-grid {
            gap: 0.6rem !important;
        }
    }
</style>

<div class="stats-grid d-flex flex-wrap gap-3 mt-4">

    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-total h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Karyawan</div>
                    <div class="stat-value">{{ $totalKaryawanSholat }}</div>
                </div>
                <i class="fas fa-users stat-icon text-primary"></i>
            </div>
        </div>
    </div>

    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-duha h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Sholat Duha</div>
                    <div class="stat-value">{{ $totalDuha }}</div>
                </div>
                <i class="fas fa-sun stat-icon text-success"></i>
            </div>
        </div>
    </div>

    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-dzuhur h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Sholat Dzuhur</div>
                    <div class="stat-value">{{ $totalDzuhur }}</div>
                </div>
                <i class="fas fa-mosque stat-icon text-warning"></i>
            </div>
        </div>
    </div>

    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-blm-absen h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Belum Absen</div>
                    <div class="stat-value">{{ $belumAbsenSholat }}</div>
                </div>
                <i class="fas fa-user-clock stat-icon text-danger"></i>
            </div>
        </div>
    </div>

</div>
<div class="mt-4 card">
    <div class="card-body">
        <h5>Grafik Absensi Sholat 7 Hari Terakhir</h5>
        <canvas id="absensiSholatChart" height="100"></canvas>
    </div>
</div>
@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctxSholat = document.getElementById('absensiSholatChart');
new Chart(ctxSholat, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($grafikSholat,'tanggal')) !!},
        datasets: [
            {
                label: 'Sholat Duha',
                data: {!! json_encode(array_column($grafikSholat,'duha')) !!},
                backgroundColor: 'rgba(76, 175, 80, 0.7)',
                borderColor: '#4caf50',
                borderWidth: 1
            },
            {
                label: 'Sholat Dzuhur',
                data: {!! json_encode(array_column($grafikSholat,'dzuhur')) !!},
                backgroundColor: 'rgba(255, 152, 0, 0.7)',
                borderColor: '#ff9800',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: { beginAtZero: true, stepSize: 1 }
        }
    }
});
</script>
@endpush