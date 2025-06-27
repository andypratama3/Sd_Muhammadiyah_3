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
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelas</label>
                    <select name="kelas" id="kelas" class="form-control">
                        <option value="" selected disabled>Pilih Kelas</option>
                        @foreach ($kelas as $item)
                            <option value="{{ $item->id }}"
                                {{ (request()->query('kelas_id') == $item->id || old('kelas_id') == $item->id) ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori Kelas</label>
                    <select name="category_kelas" id="category_kelas" class="form-control">
                        <option value="" selected disabled>Pilih Kategori Kelas</option>
                    </select>
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" class="form-control" id="nama" name="nama">
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Siswa -->
    <div class="row m-0 p-0" id="row_data">
        @foreach($siswas as $student)
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card text-center shadow-sm h-100 student-card">
                <img src="{{ asset('asset_dashboard/img/girl.png') }}" class="card-img-top rounded-circle mx-auto mt-3"
                    style="width: 80px; height: 80px; object-fit: cover;" alt="Foto Siswa">

                <div class="card-body">
                    <h5 class="card-title mb-1">{{ $student->name }}</h5>
                    <p class="card-text text-muted">{{ $student->kelas->first()->name ?? '-' }}</p>
                    {{-- Status atau badge dapat ditampilkan di sini jika tersedia --}}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function () {
        $('#kelas').on('change', function () {
            const kelasId = $(this).val();
            loadCategoryClass(kelasId);
        });

        $('#tanggal').on('change', function () {
            triggerFilter();
        });

        $('#nama').on('keyup', function () {
            triggerFilter();
        });

        function triggerFilter() {
            const kelasId = $('#kelas').val();
            const tanggal = $('#tanggal').val();
            const category_kelas = $('#category_kelas').val();
            const nama = $('#nama').val();

            const url = `{{ route('dashboard.attendances.index') }}?kelas_id=${kelasId}&tanggal=${tanggal}&category_kelas=${category_kelas}&nama=${nama}`;
            window.location.href = url;
        }

        function loadCategoryClass(kelasId) {
            let categoryKelasDropdown = $('#category_kelas');
            categoryKelasDropdown.empty();
            categoryKelasDropdown.append('<option selected disabled>Pilih Kategori Kelas</option>');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '{{ route("dashboard.datasekolah.jadwal.kelas_category") }}',
                method: 'POST',
                data: { id: kelasId },
                success: function (response) {
                    let data_category = response.categoryKelas || [];
                    data_category.forEach(category => {
                        categoryKelasDropdown.append(`<option value="${category}">${category}</option>`);
                    });
                },
                error: function (error) {
                    console.log('Gagal memuat kategori kelas:', error);
                }
            });
        }
    });
</script>
@endpush
