@extends('layouts.dashboard')

@section('title', 'Absensi Siswa')

@push('css')
<style>
    .student-card {
        transition: all 0.3s ease-in-out;
        cursor: pointer;
    }

    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@section('content')
<div class="container container_wrapper mt-4">
    <h3 class="mb-4">Absensi Siswa</h3>

    <!-- Filter Panel -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Filter Data</h5>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <label for=k"" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label for="" class="form-label">Kelas</label>
                    <select name="kelas" id="kelas" class="form-control">
                        <option value="" selected disabled>Pilih Kelas</option>
                        @foreach ($kelas as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="" class="form-label">Kategori Kelas</label>
                    <select name="kelas" id="kelas" class="form-control">
                        <option value="" selected disabled>Pilih Kelas</option>
                        @foreach ($kelas as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12 mt-2">
                    <label for="" class="form-label">Nama Siswa</label>
                    <input type="text" class="form-control" id="tanggal" name="tanggal">
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Siswa -->
    <div class="row m-0 p-0" id="row_data">
        @php
            $students = [
                ['name' => 'Ahmad Fadli', 'class' => 'Kelas 5A', 'status' => 'hadir', 'pulang' => true],
                ['name' => 'Nur Aini', 'class' => 'Kelas 5A', 'status' => 'izin', 'pulang' => false],
                ['name' => 'Rizki Hidayat', 'class' => 'Kelas 5A', 'status' => 'alpa', 'pulang' => false],
                ['name' => 'Dinda Amelia', 'class' => 'Kelas 5B', 'status' => 'hadir', 'pulang' => true],
                ['name' => 'Aisyah Lestari', 'class' => 'Kelas 5B', 'status' => 'sakit', 'pulang' => false],
                ['name' => 'Fajar Nugraha', 'class' => 'Kelas 5B', 'status' => 'hadir', 'pulang' => false],
                ['name' => 'Bayu Saputra', 'class' => 'Kelas 5C', 'status' => 'izin', 'pulang' => false],
                ['name' => 'Lina Marlina', 'class' => 'Kelas 5C', 'status' => 'hadir', 'pulang' => true],
                ['name' => 'Joko Prasetyo', 'class' => 'Kelas 5C', 'status' => 'alpa', 'pulang' => false],
                ['name' => 'Siti Nurhaliza', 'class' => 'Kelas 5C', 'status' => 'sakit', 'pulang' => false],
            ];

            function badgeColor($status) {
                return match($status) {
                    'hadir' => 'success',
                    'izin' => 'warning text-dark',
                    'sakit' => 'danger',
                    'alpa' => 'danger',
                    default => 'secondary',
                };
            }
        @endphp

        @foreach($students as $student)
        <div class="col-md-3 col-sm-6 mb-4" id="student-card">
            <div class="card text-center shadow-sm h-100 student-card">
                <img src="{{ asset('asset_dashboard/img/girl.png') }}" class="card-img-top rounded-circle mx-auto mt-3"
                    style="width: 80px; height: 80px; object-fit: cover;" alt="Foto Siswa">
                <div class="card-body">
                    <h5 class="card-title mb-1">{{ $student['name'] }}</h5>
                    <p class="card-text text-muted">{{ $student['class'] }}</p>

                    <!-- Status Awal -->
                    <span class="badge bg-{{ badgeColor($student['status']) }}">{{ ucfirst($student['status']) }}</span>

                    <!-- Tampilkan badge pulang jika sudah pulang -->
                    @if($student['status'] === 'hadir' && $student['pulang'])
                        <span class="mx-1">/</span>
                        <span class="badge bg-secondary">Pulang</span>
                    @endif

                    {{-- button when nothing data in charge for selected sakit and izin --}}

                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('js')
    <script>
        $(document).ready(function () {

            $('#kelas').on('change', function () {
                const kelasId = $(this).val();
                filterData(kelasId, null, null);
            });

            $('#tanggal').on('change', function () {
                filterData(null, $(this).val());
            });

            $('#status').on('change', function () {
                filterData(null, null, $(this).val());
            });

            function filterData(kelasId = null, tanggal = null, status = null) {
                $.ajax({
                    url: '{{ route('dashboard.attendances.index') }}',
                    method: 'GET',
                    data: {
                        kelas_id: kelasId,
                        tanggal: tanggal,
                        status: status
                    },
                    success: function (response) {
                        $('#row_data').load(location.href + " #row_data");
                    }
                });
            }
        });
    </script>
@endpush
@endsection
