@extends('layouts.dashboard')
@section('title', 'Dashboard')
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
    <div class="col-12 d-flex mb-2">
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
    <div class="col-xl-12 col-lg-7">
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <div class="col-md-12">
                    <h5>Sortir Tahun</h5>
                    <form id="yearForm" method="GET" action="{{ route('dashboard') }}">
                        <select name="year" id="year_select" class="form-control">
                            <option disabled selected>Pilih Tahun</option>
                            @for ($i = 2020; $i <= date('Y'); $i++)
                            <option value="{{ $i }}" {{ $i == request()->query('year') ?? date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div id="chargeChartContainer">
                    {!! $chagreChart->container() !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8 col-lg-7">
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

    <!-- Invoice Example -->
    <div class="col-xl-8 col-lg-7 ">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Invoice</h6>
                <a class="m-0 float-right btn btn-danger btn-sm"
                    href="{{ route('dashboard.datamaster.pembayaran.index') }}">View More <i
                        class="fas fa-chevron-right"></i></a>
            </div>
            <div class="table-responsive text-center">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Nama Pembayaran</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice_list as $invoice)
                        <tr>
                            <td>{{ $invoice->order_id}}</td>
                            <td>{{ $invoice->judul->name }}</td>
                            <td>{{ $invoice->kelas->name }}</td>
                            <td>
                                @if($invoice->status === 'Pending')
                                <span class="badge badge-warning"><i class="fas fa-solid fa-pending"></i>
                                    {{ $invoice->status }}</span>
                                @elseif($invoice->status === 'Berhasil')
                                <span class="badge badge-success"><i class="fas fa-solid fa-circle-check"></i>
                                    {{ $invoice->status }}</span>
                                @else
                                <span class="badge badge-danger"><i class="fas fa-xmark"></i> {{ $invoice->status }}</span>
                                @endif
                            </td>
                            <td><a href="{{ route('dashboard.datamaster.pembayaran.show', $invoice->order_id) }}"
                                    class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer"></div>
        </div>
    </div>
    <!-- Message From Customer-->
    <div class="col-xl-4 col-lg-5 ">
        <div class="card">
            <div class="card-header py-4 d-flex flex-row align-items-center justify-content-between">
                <h6 class="text-dark">Kritik Saran</h6>
            </div>
            <div>
                @foreach ($kritis as $kritik)
                <div class="customer-message align-items-center">
                    <a class="font-weight-bold" href="{{ route('dashboard.kritik.saran.show', $kritik->slug) }}">
                        <div class="text-truncate message-title">{{ $kritik->name }}</div>
                        <div class="small text-gray-500 message-time font-weight-bold">{{ $kritik->subject }}</div>
                    </a>
                </div>
                @endforeach
                <div class="card-footer text-center">
                    <a class="m-0 small text-primary card-link" href="#">View More <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
        </div>
</div>
@endif
<!--Row-->
@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script src="{{ $siswaChart->cdn() }}"></script>
{{ $siswaChart->script() }}
{{ $chagreChart->script() }}
<script>
    $(document).ready(function () {
        $('#year_select').on('change', function () {
            let year = $(this).val();
            $('#yearForm').submit();
        })
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
                                // Format Y-axis labels to integers
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
            // Initial load
            updateChart("month");

        // Listen for dropdown change
        document.getElementById("dataRange").addEventListener("change", function () {
            updateChart(this.value);
        });
    });
</script>
@endpush


@endsection
