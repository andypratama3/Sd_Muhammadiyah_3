@extends('layouts.dashboard')
@section('title', 'Edit Jadwal')

@push('css')
    <link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet">
    <link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        .required::after {
            content: " *";
            color: red;
        }
        .loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 1000;
            text-align: center;
            padding-top: 20%;
        }
    </style>
@endpush

@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')

    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Jadwal</h6>
    </div>

    <div class="card-body position-relative">
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <form action="{{ route('dashboard.datasekolah.jadwal.update', $jadwal->id) }}" method="POST" enctype="multipart/form-data" id="jadwalForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $jadwal->id }}">

            {{-- KELAS --}}
            <div class="mt-2 form-group">
                <label class="form-label required" for="kelas">Kelas</label>
                <select name="kelas" id="kelas" class="form-control select2 @error('kelas') is-invalid @enderror" required>
                    <option value="" selected disabled>Pilih Kelas</option>
                    @foreach ($kelass as $kelas)
                        <option value="{{ $kelas->id }}" {{ old('kelas', $jadwal->kelas_id) == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->name }}
                        </option>
                    @endforeach
                </select>
                @error('kelas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- KATEGORI KELAS --}}
            <div class="mt-2 form-group">
                <label class="form-label required" for="category_kelas">Kategori Kelas</label>
                <select name="category_kelas" id="category_kelas" class="form-control select2 @error('category_kelas') is-invalid @enderror" required>
                    <option value="{{ $jadwal->category_kelas }}" selected>{{ $jadwal->category_kelas }}</option>
                </select>
                @error('category_kelas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- TAHUN AJARAN --}}
            <div class="mt-2 form-group">
                <label class="form-label required" for="tahun_ajaran">Tahun Ajaran</label>
                <select name="tahun_ajaran" id="tahun_ajaran" class="form-control select2 @error('tahun_ajaran') is-invalid @enderror" required>
                    <option value="" selected disabled>Pilih Tahun Ajaran</option>
                    @for ($year = date('Y') - 1; $year <= date('Y'); $year++)
                        <option value="{{ $year . '/' . ( $year + 1 ) }}" {{ old('tahun_ajaran', $jadwal->tahun_ajaran) == ( $year . '/' . ( $year + 1 ) ) ? 'selected' : '' }}>
                            {{ $year . '/' . ( $year + 1 ) }}
                        </option>
                    @endfor
                </select>
                @error('tahun_ajaran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- FILE --}}
            <div class="mt-2 mb-4 form-group">
                <label class="form-label" for="jadwal_file">File Jadwal</label>
                <input type="file" class="form-control @error('jadwal_file') is-invalid @enderror" name="jadwal_file" id="jadwal_file" accept=".pdf,.doc,.docx,.xls,.xlsx">
                <small class="form-text text-muted">Format: PDF, DOC, DOCX, XLS, XLSX (Maks. 2MB)</small>
                @error('jadwal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- TABLE JADWAL --}}
            <div class="mt-4 mb-2 form-group">
                <label class="form-label required">Detail Jadwal</label>

                <div class="table-responsive">
                    <table class="table table-bordered" id="dynamicJadwal">
                        <thead class="text-center bg-light">
                            <tr>
                                <th>Hari</th>
                                <th>Waktu</th>
                                <th>Mata Pelajaran & Guru</th>
                                <th>Warna</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jadwal->jadwal_details as $index => $oldJadwal)
                            <tr>
                                <td>
                                    <select name="jadwal[{{ $index }}][hari]" class="form-control" required>
                                        <option value="">Pilih Hari</option>
                                        <option value="Senin" {{ isset($oldJadwal['hari']) && $oldJadwal['hari'] == 'Senin' ? 'selected' : '' }}>Senin</option>
                                        <option value="Selasa" {{ isset($oldJadwal['hari']) && $oldJadwal['hari'] == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                                        <option value="Rabu" {{ isset($oldJadwal['hari']) && $oldJadwal['hari'] == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                                        <option value="Kamis" {{ isset($oldJadwal['hari']) && $oldJadwal['hari'] == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                                        <option value="Jumat" {{ isset($oldJadwal['hari']) && $oldJadwal['hari'] == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                                        <option value="Sabtu" {{ isset($oldJadwal['hari']) && $oldJadwal['hari'] == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                    </select>
                                </td>

                                <td>
                                    <input type="time" name="jadwal[{{ $index }}][mulai]" class="mb-2 form-control" placeholder="Mulai" value="{{ $oldJadwal['time_start'] ?? '' }}" required>
                                    <input type="time" name="jadwal[{{ $index }}][selesai]" class="form-control" placeholder="Selesai" value="{{ $oldJadwal['time_end'] ?? '' }}" required>
                                </td>

                                <td>
                                    <label class="mb-1 form-label">Mata Pelajaran</label>
                                    <select name="jadwal[{{ $index }}][pelajaran_id]" class="mb-2 form-control select2" style="width: 100%;">
                                        <option value="">Pilih Mata Pelajaran</option>
                                        @foreach ($pelajaran as $item)
                                            <option value="{{ $item->id }}" {{ isset($oldJadwal['pelajaran_id']) && $oldJadwal['pelajaran_id'] == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>

                                    <label class="mt-2 mb-1 form-label">Guru</label>
                                    <select name="jadwal[{{ $index }}][guru_id]" class="form-control select2" style="width: 100%;">
                                        <option value="">Pilih Guru</option>
                                        @foreach ($guru as $g)
                                            <option value="{{ $g->id }}" {{ isset($oldJadwal['guru_id']) && $oldJadwal['guru_id'] == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <label for="jadwal[{{ $index }}][color]">Warna Background</label>

                                    @php
                                        $colors = [
                                            'bg-blue-100'   => '#dbeafe',
                                            'bg-green-100'  => '#dcfce7',
                                            'bg-orange-100' => '#fed7aa',
                                            'bg-purple-100' => '#e9d5ff',
                                            'bg-red-100'    => '#fee2e2',
                                            'bg-yellow-100' => '#fef3c7',
                                            'bg-pink-100'   => '#fbcfe8',
                                            'bg-indigo-100' => '#e0e7ff',
                                            'bg-gray-100'   => '#f3f4f6',
                                        ];

                                        $selectedColor = $oldJadwal['color'] ?? '';
                                    @endphp

                                    <select
                                        class="form-control color-select"
                                        name="jadwal[{{ $index }}][color]"
                                        id="jadwal[{{ $index }}][color]"
                                        data-preview="preview-{{ $index }}"
                                    >
                                        <option value="">-- Pilih Warna --</option>

                                        @foreach ($colors as $class => $hex)
                                            <option
                                                value="{{ $class }}"
                                                data-color="{{ $hex }}"
                                                {{ $selectedColor === $class ? 'selected' : '' }}
                                            >
                                                {{ $class }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small class="mt-1 text-muted d-block">Warna yang dipilih:</small>

                                    <div
                                        id="preview-{{ $index }}"
                                        style="
                                            width: 100%;
                                            height: 32px;
                                            border-radius: 6px;
                                            border: 1px solid #ddd;
                                            background-color: {{ $selectedColor ? $colors[$selectedColor] : '#ffffff' }};
                                        "
                                    ></div>
                                </td>

                                <td class="text-center align-middle">
                                    @if($index == 0)
                                        <button type="button" class="btn btn-sm btn-primary btn-add" title="Tambah Baris">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" title="Hapus Baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary btn-add" title="Tambah Baris">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('dashboard.datasekolah.jadwal.index') }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-save"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script>
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('color-select')) {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const color = selectedOption.dataset.color || '#ffffff';
        const previewId = e.target.dataset.preview;

        document.getElementById(previewId).style.backgroundColor = color;
    }
});
</script>

<script>
$(document).ready(function () {
    let i = {{ count(old('jadwal', [[]])) - 1 }};

    // Initialize select2
    function initializeSelect2() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    initializeSelect2();

    // Initialize on page load
    updatePelajaranOptions();

    // Add new jadwal row
    $('#dynamicJadwal').on('click', '.btn-add', function () {
        i++;

        const newRow = `
                <tr>
                    <td>
                        <select name="jadwal[${i}][hari]" class="form-control" required>
                            <option value="">Pilih Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </td>

                    <td>
                        <input type="time" name="jadwal[${i}][mulai]" class="mb-2 form-control" required>
                        <input type="time" name="jadwal[${i}][selesai]" class="form-control" required>
                    </td>

                    <td>
                        <label class="mb-1 form-label">Mata Pelajaran</label>
                        <select name="jadwal[${i}][pelajaran_id]" class="mb-2 form-control select2">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach ($pelajaran as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>

                        <label class="mt-2 mb-1 form-label">Guru</label>
                        <select name="jadwal[${i}][guru_id]" class="form-control select2">
                            <option value="">Pilih Guru</option>
                            @foreach ($guru as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </td>

                    <td>
                        <label>Warna Background</label>
                        <select
                            class="form-control color-select"
                            name="jadwal[${i}][color]"
                            data-preview="preview-${i}"
                        >
                            <option value="">-- Pilih Warna --</option>
                            @foreach ($colors as $class => $hex)
                                <option value="{{ $class }}" data-color="{{ $hex }}">
                                    {{ $class }}
                                </option>
                            @endforeach
                        </select>

                        <small class="mt-1 text-muted d-block">Warna yang dipilih:</small>
                        <div
                            id="preview-${i}"
                            style="
                                width:100%;
                                height:32px;
                                border-radius:6px;
                                border:1px solid #ddd;
                                background:#fff;
                            ">
                        </div>
                    </td>

                    <!-- AKSI -->
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary btn-add">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                </tr>
                `;


        $('#dynamicJadwal tbody').append(newRow);

        // Re-initialize select2 for all elements
        initializeSelect2();
        updatePelajaranOptions();
    });

    // Remove jadwal row with confirmation
    $('#dynamicJadwal').on('click', '.btn-delete', function () {
        if (confirm('Apakah Anda yakin ingin menghapus baris ini?')) {
            $(this).closest('tr').remove();
            updatePelajaranOptions();
        }
    });

    // Update mata pelajaran options to disable already selected ones
    function updatePelajaranOptions() {
        let selectedPelajaran = $('select[name*="[pelajaran_id]"]').map(function() {
            return $(this).val();
        }).get();

        // For each select, update options by excluding already selected ones
        $('select[name*="[pelajaran_id]"]').each(function() {
            let currentSelect = $(this);
            let currentValue = currentSelect.val();

            currentSelect.find('option').each(function() {
                let optionValue = $(this).val();
                // if istirahat don't disabled

                // if(optionValue === "istirahat"){
                //     $(this).prop('disabled', false);
                // }

                if (optionValue && selectedPelajaran.includes(optionValue) && currentValue != optionValue) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
        });
    }


    // Update options when pelajaran changes
    $('#dynamicJadwal').on('change', 'select[name*="[pelajaran_id]"]', function() {
        updatePelajaranOptions();
    });

    // Load kategori kelas based on selected kelas
    $('#kelas').on('change', function () {
        const kelasId = $(this).val();
        const dropdown = $('#category_kelas');

        dropdown.empty().append('<option value="" selected disabled>Pilih Kategori Kelas</option>');
        dropdown.prop('disabled', true);

        if (kelasId) {
            $('#loadingOverlay').show();

            $.ajax({
                url: '{{ route("dashboard.datasekolah.jadwal.kelas_category") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: kelasId
                },
                success: function (response) {
                    if (response && response.length > 0) {
                        response.forEach(category => {
                            dropdown.append(`<option value="${category}">${category}</option>`);
                        });
                        dropdown.prop('disabled', false);
                    } else {
                        dropdown.append('<option value="" disabled>Tidak ada kategori</option>');
                    }
                },
                error: function (xhr) {
                    console.error('Error loading kategori kelas:', xhr);
                    alert('Gagal memuat kategori kelas. Silakan coba lagi.');
                },
                complete: function () {
                    $('#loadingOverlay').hide();
                }
            });
        }
    });

    // Form validation before submit
    // $('#jadwalForm').on('submit', function (e) {
    //     const rows = $('#dynamicJadwal tbody tr').length;

    //     if (rows === 0) {
    //         e.preventDefault();
    //         alert('Harap tambahkan minimal satu detail jadwal!');
    //         return false;
    //     }

    //     // Validate time inputs
    //     let isValid = true;
    //     $('#dynamicJadwal tbody tr').each(function () {
    //         const mulai = $(this).find('input[type="time"]').eq(0).val();
    //         const selesai = $(this).find('input[type="time"]').eq(1).val();

    //         if (mulai && selesai && mulai >= selesai) {
    //             isValid = false;
    //             alert('Waktu mulai harus lebih awal dari waktu selesai!');
    //             return false;
    //         }
    //     });

    //     if (!isValid) {
    //         e.preventDefault();
    //         return false;
    //     }

    //     // Show loading
    //     $('#loadingOverlay').show();
    // });

    // Auto-select old values for category_kelas if validation fails
    @if(old('kelas'))
        $('#kelas').trigger('change');
        setTimeout(function() {
            $('#category_kelas').val('{{ old("category_kelas") }}').trigger('change');
        }, 500);
    @endif
});
</script>
@endpush
