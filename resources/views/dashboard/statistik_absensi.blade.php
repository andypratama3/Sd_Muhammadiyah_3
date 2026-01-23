<div class="row">

    {{-- Total Karyawan --}}
    <div class="col-md-3">
        <div class="shadow card">
            <div class="card-body">
                <h6>Total Karyawan</h6>
                <h3>{{ $totalKaryawan }}</h3>
            </div>
        </div>
    </div>

    {{-- Hadir --}}
    <div class="col-md-3">
        <div class="text-white shadow card bg-success">
            <div class="card-body">
                <h6>Hadir Hari Ini</h6>
                <h3>{{ $hadirHariIni }}</h3>
            </div>
        </div>
    </div>

    {{-- Terlambat --}}
    <div class="col-md-3">
        <div class="shadow card bg-warning">
            <div class="card-body">
                <h6>Terlambat</h6>
                <h3>{{ $terlambatHariIni }}</h3>
            </div>
        </div>
    </div>

    {{-- Tidak Hadir --}}
    <div class="col-md-3">
        <div class="text-white shadow card bg-danger">
            <div class="card-body">
                <h6>Tidak Hadir</h6>
                <h3>{{ $tidakHadirHariIni }}</h3>
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
@endpush

