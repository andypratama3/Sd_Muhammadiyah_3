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
    .stat-card-hadir   { background: #eafaf1; border-left: 4px solid #27ae60; }
    .stat-card-absen   { background: #fdecea; border-left: 4px solid #e74c3c; }
    .stat-card-cuti    { background: #fff8e1; border-left: 4px solid #f39c12; }

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

    /* Mobile adjustments */
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

{{-- Stats Grid --}}
<div class="stats-grid d-flex flex-wrap gap-3">

    {{-- Total Karyawan --}}
    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-total h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Karyawan</div>
                    <div class="stat-value">{{ $totalKaryawan }}</div>
                </div>
                <i class="fas fa-users stat-icon text-primary"></i>
            </div>
        </div>
    </div>

    {{-- Hadir --}}
    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-hadir h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Hadir Hari Ini</div>
                    <div class="stat-value">{{ $hadirHariIni + $terlambatHariIni }}</div>
                </div>
                <i class="fas fa-user-check stat-icon text-success"></i>
            </div>
        </div>
    </div>

    {{-- Tidak Hadir --}}
    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-absen h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Tidak Hadir</div>
                    <div class="stat-value">{{ $tidakHadirHariIni }}</div>
                </div>
                <i class="fas fa-user-times stat-icon text-danger"></i>
            </div>
        </div>
    </div>

    {{-- Cuti Aktif --}}
    <div class="flex-fill" style="min-width: 140px">
        <div class="shadow-sm card stat-card stat-card-cuti h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Cuti Hari Ini</div>
                    <div class="stat-value">{{ $cutiAktif }}</div>
                </div>
                <i class="fas fa-umbrella-beach stat-icon text-warning"></i>
            </div>
        </div>
    </div>

</div>
<div class="mt-3 row">

    {{-- Cuti Aktif --}}
    <div class="col-md-4">
        <div class="shadow card">
            <div class="card-body">
                <h6>Cuti Aktif Hari Ini</h6>
                <h3>{{ $cutiAktif }}</h3>
            </div>
        </div>
    </div>

</div>

<div class="mt-4 card">
    <div class="card-body">
        <h5>Grafik Kehadiran 7 Hari Terakhir</h5>
        <canvas id="absensiChart"></canvas>
    </div>
</div>
{{-- 
@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('absensiChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($grafik,'tanggal')) !!},
        datasets: [{
            label: 'Hadir',
            data: {!! json_encode(array_column($grafik,'hadir')) !!},
            borderWidth: 2,
            fill: false
        }]
    }
});
</script>
@endpush --}}

