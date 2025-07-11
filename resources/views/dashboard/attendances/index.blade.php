
@extends('layouts.dashboard')

@section('title', 'Absensi Siswa')

@push('css')
<style>
  .student-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    border: 1px solid transparent;
}

.student-card:hover {
    transform: translateY(-10px);
    border-color: #2E8B57;
    box-shadow: 0 15px 30px rgba(102, 126, 234, 0.25);
}

.student-card i {
    transition: transform 0.3s ease, color 0.3s ease;
}

.student-card:hover i {
    transform: scale(1.2);
    color: #2E8B57 !important;
}

.animate-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%   { transform: scale(1);   color: inherit; }
    50%  { transform: scale(1.1); color: #07ffc1; }
    100% { transform: scale(1);   color: inherit; }
}

</style>
@endpush

@section('content')
<div class="container mt-4 container_wrapper">
    <h3 class="mb-4">Absensi Siswa</h3>

    <!-- Filter Panel -->
    <div class="mb-4 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 fw-bold">Filter Data</h5>
            <div class="row">
                <div class="mb-2 col-md-12">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelas</label>
                    <select id="kelas" class="form-control">
                        <option value="">Pilih Kelas</option>
                        @foreach ($kelas as $item)
                            <option value="{{ $item->id }}" {{ request('kelas_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori Kelas</label>
                    <select id="category_kelas" class="form-control">
                        <option value="">Pilih Kategori Kelas</option>
                    </select>
                </div>
                <div class="mt-2 col-md-12">
                    <label class="form-label">Nama Siswa</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="nama" value="{{ request('nama') }}">
                        <button class="btn btn-primary" type="submit" onclick="triggerFilter()"><i class="fa-solid fa-magnifying-glass animate-icon"></i> Cari</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Siswa -->
    <div class="row" id="row_data">
        @foreach($siswas as $student)
        @php
            $attendance = $attendances[$student->id] ?? null;
            $status = $attendance->status ?? null;
            $pulang = $attendance->pulang ?? false;
            $tanggal = request('tanggal', date('Y-m-d'));
        @endphp

        <div class="mb-4 col-md-3 col-sm-6 animation">
            <div class="text-center shadow-sm card h-100 student-card">
                <img src="{{ asset('asset_dashboard/img/girl.png') }}" class="mx-auto mt-3 card-img-top rounded-circle"
                    style="width: 80px; height: 80px; object-fit: cover;" alt="Foto Siswa">

                <div class="card-body">
                    <h5 class="mb-1 card-title">{{ $student->name }}</h5>
                    <p class="card-text text-muted">
                        {{ $student->kelas->first()->name ?? '-' }}<br>
                        {{ $student->kelas->first()->pivot->category_kelas ?? '-' }}
                    </p>

                    @if($status)
                        <span class="badge bg-{{ \App\Helpers\AttendanceHelper::badgeColor($status) }}">
                            {{ ucfirst($status) }}
                        </span>
                    @endif

                    @if($status === 'hadir' && $pulang)
                        <span class="mx-1">/</span>
                        <span class="badge bg-secondary">Pulang</span>
                    @endif

                   @if(!$status || $status !== 'hadir')
                        <select id="status-{{ $student->id }}" class="mt-3 mb-2 form-select status form-select-sm" data-id="{{ $student->id }}">
                            <option value="">-- Pilih Status --</option>
                            <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="izin"  {{ $status == 'izin'  ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="alpa"  {{ $status == 'alpa'  ? 'selected' : '' }}>Alpa</option>
                        </select>

                       @if($status === 'izin' && $attendance->keterangan)
                            <div class="mt-2">
                                <div class="px-2 py-1 mb-2 alert alert-warning small text-start">
                                    <strong>Alasan:</strong> {{ $attendance->keterangan }}
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-edit-izin"
                                    data-id="{{ $student->id }}">
                                    Edit Keterangan
                                </button>
                            </div>

                            <!-- Textarea hidden by default, shown when "Edit" diklik -->
                            <div class="mt-2 form-izin" id="izin-text-{{ $student->id }}" style="display: none;">
                                <textarea class="form-control keterangan" rows="2"
                                    placeholder="Tulis keterangan izin..." id="keterangan-{{ $student->id }}" required>{{ $attendance->keterangan }}</textarea>
                            </div>
                        @elseif(!$status || $status !== 'hadir')
                            <div class="mt-2 form-izin" id="izin-text-{{ $student->id }}" style="display: none;">
                                <textarea class="form-control keterangan" rows="2"
                                    placeholder="Tulis keterangan izin..." id="keterangan-{{ $student->id }}"></textarea>
                            </div>
                        @endif


                        <button type="button" class="mt-2 btn btn-sm btn-success w-100 btn-attendance"
                            data-id="{{ $student->id }}"
                            data-kelas="{{ $student->kelas->first()->id ?? '' }}"
                            data-kategori_kelas="{{ $student->kelas->first()->pivot->category_kelas ?? '' }}"
                            data-tanggal="{{ $tanggal }}">
                            Simpan
                        </button>
                    @endif


                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('js')
<script>
    const triggerFilter = () => {
        const kelasId = $('#kelas').val();
        const tanggal = $('#tanggal').val();
        const category_kelas = $('#category_kelas').val();
        const nama = $('#nama').val();
        const url = `{{ route('dashboard.attendances.index') }}?kelas_id=${kelasId}&tanggal=${tanggal}&category_kelas=${category_kelas}&nama=${nama}`;
        window.location.href = url;
    }

    const loadCategoryClass = (kelasId) => {
        let dropdown = $('#category_kelas');
        dropdown.empty().append('<option selected disabled>Pilih Kategori Kelas</option>');

        $.ajax({
            url: '{{ route("dashboard.datasekolah.jadwal.kelas_category") }}',
            method: 'POST',
            data: { id: kelasId },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $.each(response, function (index, category) {
                    const selected = category === '{{ request("category_kelas") }}' ? 'selected' : '';
                    dropdown.append(`<option value="${category}" ${selected}>${category}</option>`);
                });
            },
            error: function (err) {
                console.log('Gagal memuat kategori kelas', err);
            }
        });
    }

    $(document).ready(function () {
        const kelasIdFromUrl = '{{ request("kelas_id") }}';
        if (kelasIdFromUrl) loadCategoryClass(kelasIdFromUrl);

        $('#kelas').on('change', function () {
            loadCategoryClass($(this).val());
            triggerFilter();
        });

        $('#tanggal, #nama, #category_kelas').on('change', triggerFilter);

        // when status izin show textarea
        $('.status').on('change', function () {
            const siswaId = $(this).data('id');
            const selected = $(this).val();

            if (selected === 'izin') {
                $(`#izin-text-${siswaId}`).slideDown();
            } else {
                $(`#izin-text-${siswaId}`).slideUp();
            }
        });

        $('.btn-edit-izin').on('click', function () {
            const id = $(this).data('id');
            $(`#izin-text-${id}`).slideDown();
        });

        $('.btn-attendance').on('click', function () {
            const btn = $(this);
            const siswa_id = btn.data('id');
            const kelas_id = btn.data('kelas');
            const category_kelas = btn.data('kategori_kelas');
            const tanggal = btn.data('tanggal');
            const status = $(`#status-${siswa_id}`).val();
            const keterangan = $(`#keterangan-${siswa_id}`).val(); // Tambahan

             if (!status) {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Silakan pilih status kehadiran terlebih dahulu.',
                    icon: 'warning',
                });
                return;
            }

            if (status === 'izin' && (!keterangan || keterangan.trim() === '')) {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Silakan isi keterangan untuk izin.',
                    icon: 'warning',
                });
                return;
            }


            $.ajax({
                url: '{{ route("dashboard.attendances.store") }}',
                method: 'POST',
                data: {
                    siswa_id,
                    kelas_id,
                    kategori_kelas: category_kelas,
                    tanggal,
                    status,
                    keterangan
                },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    window.location.reload();
                },
                error: function (err) {
                    // console.error('Gagal menyimpan absensi:', err);
                    // alert('Gagal menyimpan absensi.');
                    Swal.fire({
                        title: 'Gagal',
                        text: 'Gagal menyimpan absensi. Silahkan coba lagi, Pilih Status Atau Keterangan Izin Di Isi.',

                        icon: 'error',
                        buttons: false
                    });
                }
            });
        });

    });
</script>
@endpush
