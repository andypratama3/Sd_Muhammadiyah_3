@extends('layouts.dashboard')
@section('title', 'Dashboard')
@push('css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .chart-container {
            width: 500px;
            height: 400px;
        }
        #chartjs-dashboard-bar {
            width: 100%;
            height: 400px;
        }

        @media (max-width: 768px) {
            .chart-container {
                width: 100%;
                height: auto;
            }
            #chartjs-dashboard-bar {
                width: 100%;
                height: auto;
            }
        }
    </style>

@endpush
@section('content')
@if(Auth::user()->hasRole ('user'))
@include('dashboard.user.index')
@else
<div class="row mb-3">
    <!-- Siswa -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Siswa</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $siswa }}</div>
                        {{-- <div class="mt-2 mb-0 text-muted text-xs">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
                    <span>Since last month</span>
                </div> --}}
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Guru -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Guru</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $guru }}</div>
                        {{-- <div class="mt-2 mb-0 text-muted text-xs">
                    <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> 12%</span>
                    <span>Since last years</span>
                </div> --}}
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-person-chalkboard fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tenaga Pendidikan -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Tenaga Kependidikan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $tenagakependidikan }}</div>
                        {{-- <div class="mt-2 mb-0 text-muted text-xs">
                    <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> 1.10%</span>
                    <span>Since yesterday</span>
                </div> --}}
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-chalkboard-user fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Prestasi -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Prestasi</div>
                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $prestasi }}</div>
                        {{-- <div class="mt-2 mb-0 text-muted text-xs">
                    <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> 20.4%</span>
                    <span>Since last month</span>
                    </div> --}}
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-trophy fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Area Chart -->
    <div class="col-md-12 d-flex justify-content-center align-items-center mb-2">
        <div class="card flex-fill w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Total Pembayaran</h5>
            </div>
            <div class="row mx-2">
                <div class="col-md-12">
                    <label for="">Sortir Waktu</label>
                    <input type="month" id="range-pie" class="form-control" value="{{ request()->query('chargeCountMount_date') }}" name="range-pie" placeholder="Pilih Bulan dan Tahun">
                </div>
                <div class="col-md-12">
                    <label for="">Kategori Pembayaran</label>
                    <select name="category_payment" id="category_payment" class="form-control">
                        <option value="">Pilih Kategori Pembayaran</option>
                        @foreach ($category_payments as $category_payment)
                            <option value="{{ $category_payment->id }}"
                                {{ request()->query('category_payment') == $category_payment->id ? 'selected' : '' }}>
                                {{ $category_payment->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center w-100">
                <div class="chart-container">
                    <canvas id="chartjs-dashboard-pie-mount"></canvas>

                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12 col-lg-7">
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <form id="yearForm" method="GET" action="{{ route('dashboard') }}">
                    <div class="row">
                        <h5>Sortir Tahun</h5>
                        <div class="col-md-6">
                            <select name="year" id="year_select" class="form-control">
                                <option value="" {{ !request()->query('year') ? 'selected' : '' }}>Pilih Tahun</option>
                                @for ($i = 2020; $i <= \Carbon\Carbon::now()->year; $i++)
                                    <option value="{{ $i }}" {{ $i == request()->query('year') ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select name="month" id="month_select" class="form-control">
                                <option value="" selected>Pilih Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ (request()->query('month') == $i) || (!$i && !request()->query('month')) ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                    </div>
                </form>
            </div>
            <div class="card-body">
                <div id="chargeChartContainer">
                    {!! $chargeChart->container() !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 ">
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle float-end" href="#" role="button" id="dropdownMenuLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Dropdown Header:</div>
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{-- {!! $ArtikelChart->container() !!} --}}
                {!! $siswaChart->container() !!}
            </div>
        </div>
    </div>
    <div class="col-md-12 d-flex mb-2">
        <div class="card flex-fill w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Pengunjung</h5>
                <select id="dataRange" class="form-select w-auto">
                    <option value="day">Harian</option>
                    <option value="month" selected>Bulanan</option>
                    <option value="year">Tahunan</option>
                </select>
            </div>
            <div class="card-body d-flex w-100">
                <div class="align-self-center chart chart-lg">
                    <canvas id="chartjs-dashboard-bar"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card ">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary text-center">5 Top Pengunjung Artikel / Click</h6>
            </div>
            <div class="card-body">
                @foreach ($artikels as $artikel)
                <div class="mb-3">
                    <div class="small text-gray-500">{{ $artikel->name }}
                        <div class="small float-right"><b>{{ $artikel->jumlah_klik }} of
                                {{ $artikel_sum_total_klik }}</b></div>
                    </div>
                </div>
                @endforeach
                <div class="card-footer text-center">
                    <a class="m-0 small text-primary card-link" href="#">View More <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>

</div>
@endif
<!--Row-->
@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js" defer></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="{{ $siswaChart->cdn() }}"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="{{ $chargeChart->cdn() }}"></script>
{!! $chargeChart->script() !!}
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        const chartChargeCanvas = document.getElementById("chartjs-dashboard-pie-mount").getContext("2d");

        let chartPie = new Chart(chartChargeCanvas, {
            type: "pie",
            data: {
                labels: ["Tidak Ada Data"],
                datasets: [{
                    // label: "Total Pembayaran",
                    backgroundColor: ["#008FFB", "#00E396", "#FEB019", "#FF455F"],
                    borderColor: "#fff",
                    hoverBackgroundColor: ["#0057ff", "#00a9f4", "#2ccdc9", "#ff6384"],
                    hoverBorderColor: "#fff",
                    data: [100],
                    hoverOffset: 4

                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: "right",
                        labels: {
                            padding: 10, // Spasi antara label
                            boxWidth: 15, // Ukuran kotak warna
                            font: {
                                size: 14 // Ukuran teks
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return "Rp : " + new Intl.NumberFormat().format(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // Fungsi untuk memperbarui chart dengan data baru
        function updateChart(response) {
            let seriesData = [
                response.settlement_amount || 0,
                response.pay_offline_amount || 0,
                response.pending_amount || 0,
                response.deny_failed_amount || 0
            ];
            // settlement_amount + capture amount
            const settlementAmount = response.settlement_amount + response.capture_amount;

            let labelsData = [
                `Settlement: Rp ${new Intl.NumberFormat('id-ID').format(settlementAmount || 0)}`,
                `Pay Offline: Rp ${new Intl.NumberFormat('id-ID').format(response.pay_offline_amount || 0)}`,
                `Pending: Rp ${new Intl.NumberFormat('id-ID').format(response.pending_amount || 0)}`,
                `Denied/Failed: Rp ${new Intl.NumberFormat('id-ID').format(response.deny_failed_amount || 0)}`
            ];

            chartPie.data.labels = labelsData;
            chartPie.data.datasets[0].data = seriesData;
            chartPie.update();
        }

        // Fungsi untuk mengambil data dari backend
        function fetchChartData() {
            let range = $('#range-pie').val();
            let category_payment = $('#category_payment').val();

            $.ajax({
                type: "GET",
                url: "{{ route('chart.charge.count') }}",
                data: {
                    chargeCountMount_date: range,
                    category: category_payment
                },
                cache: false,
                success: function (response) {
                    updateChart(response);
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching chart data: ", error);
                }
            });
        }

        // Panggil data pertama kali
        fetchChartData();

        // Update data saat filter berubah
        $('#range-pie, #category_payment').on('change', fetchChartData);
    });
</script>



<script>
    $(document).ready(function () {
        $('#year_select').on('change', function () {
            let year = $(this).val();
            $('#yearForm').submit();
        });

        $('#month_select').on('change', function () {
            let month = $(this).val();
            $('#yearForm').submit();
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const ctx = document.getElementById("chartjs-dashboard-bar").getContext("2d");
        const chart = new Chart(ctx, {
            type: "bar",
            data: {
                // labels: ["Data"], // Empty initially, will be filled dynamically
                datasets: [{
                    label: "Data",
                    // backgroundColor: window.theme.primary,
                    // borderColor: window.theme.primary,
                    // hoverBackgroundColor: window.theme.primary,
                    // hoverBorderColor: window.theme.primary,
                    data: [], // Empty initially
                    barPercentage: 0.75,
                    categoryPercentage: 0.5,
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                // Format tooltip value to integer
                                return context.dataset.label + ": " + Math.round(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            beginAtZero: true,
                            callback: function (value) {

                                return Math.round(value);
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                    }
                }
            }
        });


        const baseUrl = "{{ route('visitors.data') }}";
            function updateChart(range) {
                fetch(`${baseUrl}?range=${range}`)
                    .then(response => response.json())
                    .then(data => {
                        const labels = [];
                        const values = [];

                        if (range === "day") {
                            labels.push("Hari Ini");
                            values.push(data);
                        } else if (range === "month") {
                            data.forEach(item => {
                                labels.push(`Tanggal ${item.day}`);
                                values.push(item.total);
                            });
                        } else if (range === "year") {
                            data.forEach(item => {
                                labels.push(["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][item.month - 1]);
                                values.push(item.total);
                            });
                        }

                        chart.data.labels = labels;
                        chart.data.datasets[0].data = values;
                        chart.update();
                    })
                    .catch(err => console.error("Error fetching data:", err));
            }

            updateChart("month");

        // Listen for dropdown change
        document.getElementById("dataRange").addEventListener("change", function () {
            updateChart(this.value);
        });
    });
</script>
@endpush


@endsection
