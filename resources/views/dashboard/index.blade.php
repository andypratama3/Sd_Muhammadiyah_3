@extends('layouts.dashboard')
@section('title', 'Dashboard')
@push('css')
<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
@endpush
@section('content')
    {{-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                {{ Breadcrumbs::render() }}
            </ol>
        </nav>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <!-- Bar Chart -->
                {!! $ArtikelChart->container() !!}
                <!-- End Bar CHart -->
            </div>
        </div>
    </div> --}}
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
        <div class="col-xl-8 col-lg-7">
          <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <div class="dropdown no-arrow">
                <a class="dropdown-toggle float-end" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
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
                    <div class="small float-right"><b>{{ $artikel->jumlah_klik }} of {{ $artikel_sum_total_klik }}</b></div>
                </div>
                <div class="progress" style="height: 12px;">
                    @if ($percent_artikel > 75)
                    <div class="progress-bar bg-success" role="progressbar"
                        style="width: {{ $percent_artikel }}%"
                        aria-valuenow="{{ $percent_artikel }}"
                        aria-valuemin="0" aria-valuemax="100">
                    </div>
                @elseif ($percent_artikel > 50)
                    <div class="progress-bar bg-info" role="progressbar"
                        style="width: {{ $percent_artikel }}%"
                        aria-valuenow="{{ $percent_artikel }}"
                        aria-valuemin="0" aria-valuemax="100">
                    </div>
                @elseif ($percent_artikel > 25)
                    <div class="progress-bar bg-warning" role="progressbar"
                        style="width: {{ $percent_artikel }}%"
                        aria-valuenow="{{ $percent_artikel }}"
                        aria-valuemin="0" aria-valuemax="100">
                    </div>
                @else
                    <div class="progress-bar bg-danger" role="progressbar"
                        style="width: {{ $percent_artikel }}%"
                        aria-valuenow="{{ $percent_artikel }}"
                        aria-valuemin="0" aria-valuemax="100">
                    </div>
                @endif
                </div>
            </div>
            @endforeach
        </div>
            <div class="card-footer text-center">
              <a class="m-0 small text-primary card-link" href="#">View More <i
                  class="fas fa-chevron-right"></i></a>
            </div>
          </div>
        </div>
        <!-- Invoice Example -->
        <div class="col-xl-8 col-lg-7 ">
          <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">Invoice</h6>
              <a class="m-0 float-right btn btn-danger btn-sm" href="{{ route('dashboard.datamaster.pembayaran.index') }}">View More <i
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
                        <span class="badge badge-warning"><i class="fas fa-solid fa-pending"></i> {{ $invoice->status }}</span>
                        @elseif($invoice->status === 'Berhasil')
                        <span class="badge badge-success"><i class="fas fa-solid fa-circle-check"></i> {{ $invoice->status }}</span>
                        @else
                        <span class="badge badge-danger"><i class="fas fa-xmark"></i> {{ $invoice->status }}</span>
                        @endif
                    </td>
                    <td><a href="{{ route('dashboard.datamaster.pembayaran.show', $invoice->order_id) }}" class="btn btn-sm btn-primary">Detail</a></td>
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
            <div class="card-header py-4 bg-primary d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-light">Message From Customer</h6>
            </div>
            <div>
              <div class="customer-message align-items-center">
                <a class="font-weight-bold" href="#">
                  <div class="text-truncate message-title">Hi there! I am wondering if you can help me with a
                    problem I've been having.</div>
                  <div class="small text-gray-500 message-time font-weight-bold">Udin Cilok · 58m</div>
                </a>
              </div>
              <div class="customer-message align-items-center">
                <a href="#">
                  <div class="text-truncate message-title">But I must explain to you how all this mistaken idea
                  </div>
                  <div class="small text-gray-500 message-time">Nana Haminah · 58m</div>
                </a>
              </div>
              <div class="customer-message align-items-center">
                <a class="font-weight-bold" href="#">
                  <div class="text-truncate message-title">Lorem ipsum dolor sit amet, consectetur adipiscing elit
                  </div>
                  <div class="small text-gray-500 message-time font-weight-bold">Jajang Cincau · 25m</div>
                </a>
              </div>
              <div class="customer-message align-items-center">
                <a class="font-weight-bold" href="#">
                  <div class="text-truncate message-title">At vero eos et accusamus et iusto odio dignissimos
                    ducimus qui blanditiis
                  </div>
                  <div class="small text-gray-500 message-time font-weight-bold">Udin Wayang · 54m</div>
                </a>
              </div>
              <div class="card-footer text-center">
                <a class="m-0 small text-primary card-link" href="#">View More <i
                    class="fas fa-chevron-right"></i></a>
              </div>
            </div>
          </div>
        </div>
    </div>

      <!--Row-->
@push('js')
<script src="{{ $siswaChart->cdn() }}"></script>
{{ $siswaChart->script() }}
@endpush
@endsection
