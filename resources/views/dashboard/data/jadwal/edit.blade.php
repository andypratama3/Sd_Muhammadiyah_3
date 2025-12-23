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

        .jadwal-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .jadwal-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .jadwal-card.has-color {
            border-left: 5px solid;
            border-left-color: var(--card-color, #007bff);
        }

        .jadwal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .jadwal-hari {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
        }

        .btn-remove-jadwal {
            background: #ff4757;
            border: none;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }

        .btn-remove-jadwal:hover {
            background: #ff3838;
        }

        .jadwal-content {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .jadwal-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .jadwal-row strong {
            width: 80px;
            color: #495057;
        }

        .jadwal-value {
            flex: 1;
            color: #212529;
            font-weight: 500;
        }

        .jadwal-icon {
            width: 24px;
            text-align: center;
            color: #007bff;
            font-size: 16px;
        }

        .color-box {
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 2px solid #ddd;
            margin-right: 8px;
            vertical-align: middle;
        }

        .form-card {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 18px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-title i {
            color: #007bff;
            font-size: 24px;
        }

        .form-group-inline {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group-inline.full {
            grid-template-columns: 1fr;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
            font-size: 13px;
        }

        .form-control, .select2-container--bootstrap4 {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .color-preview-box {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 6px;
            border: 2px solid #ddd;
            vertical-align: middle;
            background: white;
        }

        .step-indicator {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .step {
            padding: 6px 12px;
            background: #e9ecef;
            border-radius: 20px;
            color: #6c757d;
        }

        .step.active {
            background: #007bff;
            color: white;
            font-weight: 600;
        }

        .step i {
            margin-right: 4px;
        }

        .btn-container {
            display: flex;
            gap: 10px;
        }

        .btn-container button {
            flex: 1;
        }

        .summary-section {
            margin-top: 40px;
            padding: 20px;
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            border-radius: 6px;
        }

        .summary-title {
            font-weight: 700;
            color: #007bff;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #007bff;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .hari-group {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }

        .hari-group-title {
            font-size: 16px;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #adb5bd;
        }

        .empty-state i {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .jadwal-container {
                grid-template-columns: 1fr;
            }

            .form-group-inline {
                grid-template-columns: 1fr;
            }

            .summary-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')

    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Jadwal - Workflow Dinamis</h6>
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

            {{-- HEADER SECTION --}}
            <div class="mb-4 row">
                <div class="col-md-4">
                    <label class="form-label required">Kelas</label>
                    <select name="kelas" id="kelas" class="form-control select2 @error('kelas') is-invalid @enderror" required>
                        <option value="" disabled>Pilih Kelas</option>
                        @foreach ($kelass as $kelas)
                            <option value="{{ $kelas->id }}" {{ old('kelas', $jadwal->kelas_id) == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelas')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Kategori Kelas</label>
                    <select name="category_kelas" id="category_kelas" class="form-control select2 @error('category_kelas') is-invalid @enderror" required>
                        <option value="{{ $jadwal->category_kelas }}" selected>{{ $jadwal->category_kelas }}</option>
                    </select>
                    @error('category_kelas')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Tahun Ajaran</label>
                    <select name="tahun_ajaran" id="tahun_ajaran" class="form-control select2 @error('tahun_ajaran') is-invalid @enderror" required>
                        <option value="" disabled>Pilih Tahun Ajaran</option>
                        @for ($year = date('Y') - 1; $year <= date('Y'); $year++)
                            <option value="{{ $year . '/' . ( $year + 1 ) }}" {{ old('tahun_ajaran', $jadwal->tahun_ajaran) == ( $year . '/' . ( $year + 1 ) ) ? 'selected' : '' }}>
                                {{ $year . '/' . ( $year + 1 ) }}
                            </option>
                        @endfor
                    </select>
                    @error('tahun_ajaran')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>

            {{-- FILE SECTION --}}
            <div class="mb-4">
                <label class="form-label">File Jadwal (Opsional)</label>
                <input type="file" class="form-control @error('jadwal_file') is-invalid @enderror" name="jadwal_file" id="jadwal_file" accept=".pdf,.doc,.docx,.xls,.xlsx">
                <small class="form-text text-muted">Format: PDF, DOC, DOCX, XLS, XLSX (Maks. 2MB)</small>
                @if($jadwal->jadwal)
                    <small class="mt-2 form-text text-success d-block">📎 File saat ini: {{ $jadwal->jadwal }}</small>
                @endif
            </div>

            <hr>

            {{-- INPUT FORM SECTION --}}
            <div class="form-card">
                <div class="form-title">
                    <i class="fas fa-plus-circle"></i> Tambah/Edit Pelajaran
                </div>

                <div class="step-indicator">
                    <div class="step active">
                        <i class="fas fa-calendar"></i> 1. Pilih Hari
                    </div>
                    <div class="step">
                        <i class="fas fa-book"></i> 2. Pilih Pelajaran
                    </div>
                    <div class="step">
                        <i class="fas fa-user-tie"></i> 3. Guru & Warna
                    </div>
                    <div class="step">
                        <i class="fas fa-check"></i> 4. Konfirmasi
                    </div>
                </div>

                <div class="form-group-inline full">
                    <div>
                        <label class="form-label required">Pilih Hari</label>
                        <select id="inputHari" class="form-control">
                            <option value="">-- Pilih Hari --</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>
                </div>

                <div id="formPelajaran" style="display: none;">
                    <div class="form-group-inline full">
                        <div>
                            <label class="form-label required">Mata Pelajaran</label>
                            <select id="inputPelajaran" class="form-control select2"  style="width: 100%;">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach ($pelajaran as $item)
                                    <option value="{{ $item->id }}" data-time-start="{{ $item->time_start ?? '' }}" data-time-end="{{ $item->time_end ?? '' }}">
                                        {{ $item->name }}
                                        @if($item->time_start && $item->time_end)
                                            ({{ $item->time_start }} - {{ $item->time_end }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih pelajaran - waktu akan otomatis terisi</small>
                        </div>
                    </div>

                    <div class="form-group-inline">
                        <div>
                            <label class="form-label required">Waktu Mulai</label>
                            <input type="time" step="60" id="inputMulai" class="form-control" >
                        </div>
                        <div>
                            <label class="form-label required">Waktu Selesai</label>
                            <input type="time" step="60" id="inputSelesai" class="form-control">
                        </div>
                    </div>

                    <div class="form-group-inline">
                        <div>
                            <label class="form-label">Guru (Opsional)</label>
                            <select id="inputGuru" class="form-control select2" style="width: 100%;">
                                <option value="">-- Pilih Guru --</option>
                                @foreach ($guru as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Warna Background</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <select id="inputColor" class="form-control" style="flex: 1;">
                                    <option value="">-- Pilih Warna --</option>
                                    <option value="bg-blue-100" data-color="#dbeafe">Biru</option>
                                    <option value="bg-green-100" data-color="#dcfce7">Hijau</option>
                                    <option value="bg-orange-100" data-color="#fed7aa">Orange</option>
                                    <option value="bg-purple-100" data-color="#e9d5ff">Ungu</option>
                                    <option value="bg-red-100" data-color="#fee2e2">Merah</option>
                                    <option value="bg-yellow-100" data-color="#fef3c7">Kuning</option>
                                    <option value="bg-pink-100" data-color="#fbcfe8">Pink</option>
                                    <option value="bg-indigo-100" data-color="#e0e7ff">Indigo</option>
                                    <option value="bg-gray-100" data-color="#f3f4f6">Abu-abu</option>
                                </select>
                                <div class="color-preview-box" id="colorPreview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-container" style="margin-top: 25px;">
                        <button type="button" id="btnReset" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button type="button" id="btnTambah" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Pelajaran
                        </button>
                    </div>
                </div>
            </div>

            {{-- RESULT SECTION --}}
            <div id="resultSection">
                <div class="summary-section">
                    <div class="summary-title">
                        <i class="fas fa-list-check"></i> Ringkasan Jadwal
                    </div>
                    <div class="summary-stats">
                        <div class="stat-box">
                            <div class="stat-number" id="totalHari">0</div>
                            <div class="stat-label">Total Hari</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number" id="totalPelajaran">0</div>
                            <div class="stat-label">Total Pelajaran</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number" id="totalGuru">0</div>
                            <div class="stat-label">Total Guru</div>
                        </div>
                    </div>
                </div>

                <div id="jadwalGroupContainer" class="mt-4">
                    {{-- Jadwal cards akan di-render ke sini --}}
                </div>
            </div>

            {{-- BUTTONS --}}
            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('dashboard.datasekolah.jadwal.index') }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <button type="submit" class="btn btn-sm btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script>
$(document).ready(function () {
    let jadwalData = {};
    const daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const colors = {
        'bg-blue-100': '#dbeafe',
        'bg-green-100': '#dcfce7',
        'bg-orange-100': '#fed7aa',
        'bg-purple-100': '#e9d5ff',
        'bg-red-100': '#fee2e2',
        'bg-yellow-100': '#fef3c7',
        'bg-pink-100': '#fbcfe8',
        'bg-indigo-100': '#e0e7ff',
        'bg-gray-100': '#f3f4f6'
    };

    // Load existing data
    const existingData = @json($jadwal->jadwal_details->groupBy('hari')->toArray());

    daysOfWeek.forEach(day => {
        jadwalData[day] = [];
        if (existingData[day]) {
            existingData[day].forEach(entry => {
                jadwalData[day].push({
                    id: 'entry-' + entry.id,
                    pelajaranId: entry.pelajaran_id,
                    pelajaranText: entry.pelajaran?.name || 'Tidak ada',
                    mulai: entry.time_start,
                    selesai: entry.time_end,
                    guruId: entry.guru_id,
                    guruText: entry.guru?.name || 'Tanpa guru',
                    colorClass: entry.color,
                    colorHex: colors[entry.color] || '#ffffff',
                    dbId: entry.id
                });
            });
        }
    });

    function initSelect2() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    initSelect2();

    // Render initial
    renderResult();

    // Pilih hari
    $('#inputHari').on('change', function() {
        const hari = $(this).val();
        if (hari) {
            $('#formPelajaran').slideDown();
            resetForm();
        } else {
            $('#formPelajaran').slideUp();
        }
    });

    // Auto-fill waktu saat pilih pelajaran
    $('#inputPelajaran').on('change', function() {
        const timeStart = $(this).find('option:selected').data('time-start');
        const timeEnd = $(this).find('option:selected').data('time-end');

        if (timeStart && timeEnd) {
            $('#inputMulai').val(timeStart);
            $('#inputSelesai').val(timeEnd);
        } else {
            $('#inputMulai').val('');
            $('#inputSelesai').val('');
        }
    });

    // Color preview
    $('#inputColor').on('change', function() {
        const color = $(this).find('option:selected').data('color') || '#ffffff';
        $('#colorPreview').css('background-color', color);
    });

    // Reset form
    function resetForm() {
        $('#inputPelajaran').val(null).trigger('change');
        $('#inputMulai').val('');
        $('#inputSelesai').val('');
        $('#inputGuru').val(null).trigger('change');
        $('#inputColor').val('');
        $('#colorPreview').css('background-color', 'white');
    }

    $('#btnReset').on('click', resetForm);

    // Tambah pelajaran
    $('#btnTambah').on('click', function() {
        const hari = $('#inputHari').val();
        const pelajaranId = $('#inputPelajaran').val();
        const pelajaranText = $('#inputPelajaran').find('option:selected').text();
        const mulai = $('#inputMulai').val();
        const selesai = $('#inputSelesai').val();
        const guruId = $('#inputGuru').val();
        const guruText = $('#inputGuru').find('option:selected').text();
        const colorClass = $('#inputColor').val();

        // Validasi
        if (!hari || !pelajaranId || !mulai || !selesai) {
            alert('Harap lengkapi: Hari, Pelajaran, dan Waktu!');
            return;
        }

        if (mulai >= selesai) {
            alert('Waktu mulai harus lebih awal dari waktu selesai!');
            return;
        }

        // Initialize hari jika belum ada
        if (!jadwalData[hari]) {
            jadwalData[hari] = [];
        }

        // Add data
        jadwalData[hari].push({
            id: 'entry-' + Date.now(),
            pelajaranId,
            pelajaranText: pelajaranText.split('(')[0].trim(),
            mulai,
            selesai,
            guruId,
            guruText: guruText || 'Tanpa guru',
            colorClass,
            colorHex: colors[colorClass] || '#ffffff'
        });

        // Render
        renderResult();
        resetForm();
        $('#inputHari').focus();
    });

    function renderResult() {
        const totalPelajaran = Object.values(jadwalData).reduce((sum, arr) => sum + arr.length, 0);
        const totalGuru = new Set(Object.values(jadwalData).flat().map(p => p.guruId).filter(Boolean)).size;

        $('#totalHari').text(Object.keys(jadwalData).filter(day => jadwalData[day].length > 0).length);
        $('#totalPelajaran').text(totalPelajaran);
        $('#totalGuru').text(totalGuru);

        let html = '';
        daysOfWeek.forEach(hari => {
            if (jadwalData[hari] && jadwalData[hari].length > 0) {
                html += `<div class="hari-group">
                    <div class="hari-group-title">
                        <i class="fas fa-calendar-day"></i> ${hari}
                    </div>
                    <div class="jadwal-container">`;

                jadwalData[hari].forEach(entry => {
                    html += `
                        <div class="jadwal-card ${entry.colorClass ? 'has-color' : ''}" style="--card-color: ${entry.colorHex};">
                            <div class="jadwal-header">
                                <span class="jadwal-hari">${hari}</span>
                                <button type="button" class="btn-remove-jadwal btn-remove" data-hari="${hari}" data-id="${entry.id}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>

                            <div class="jadwal-content">
                                <div class="jadwal-row">
                                    <div class="jadwal-icon"><i class="fas fa-clock"></i></div>
                                    <strong>Waktu</strong>
                                    <div class="jadwal-value">${entry.mulai} - ${entry.selesai}</div>
                                </div>

                                <div class="jadwal-row">
                                    <div class="jadwal-icon"><i class="fas fa-book"></i></div>
                                    <strong>Pelajaran</strong>
                                    <div class="jadwal-value">${entry.pelajaranText}</div>
                                </div>

                                <div class="jadwal-row">
                                    <div class="jadwal-icon"><i class="fas fa-user-tie"></i></div>
                                    <strong>Guru</strong>
                                    <div class="jadwal-value">${entry.guruText}</div>
                                </div>

                                ${entry.colorClass ? `
                                <div class="jadwal-row">
                                    <div class="jadwal-icon"><i class="fas fa-palette"></i></div>
                                    <strong>Warna</strong>
                                    <div class="jadwal-value">
                                        <span class="color-box" style="background-color: ${entry.colorHex};"></span>
                                        ${entry.colorClass.replace('-', ' ')}
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });

                html += `</div></div>`;
            }
        });

        $('#jadwalGroupContainer').html(html);

        // Attach remove handlers
        $('.btn-remove').on('click', function(e) {
            e.preventDefault();
            const hari = $(this).data('hari');
            const id = $(this).data('id');

            jadwalData[hari] = jadwalData[hari].filter(entry => entry.id !== id);
            if (jadwalData[hari].length === 0) {
                delete jadwalData[hari];
            }
            renderResult();
        });
    }

    // Form submit
    $('#jadwalForm').on('submit', function(e) {
        const totalPelajaran = Object.values(jadwalData).reduce((sum, arr) => sum + arr.length, 0);

        if (totalPelajaran === 0) {
            e.preventDefault();
            alert('Harap tambahkan minimal satu pelajaran!');
            return false;
        }

        // Create hidden inputs
        let index = 0;
        daysOfWeek.forEach(hari => {
            if (jadwalData[hari]) {
                jadwalData[hari].forEach(entry => {
                    $('<input>').attr({
                        type: 'hidden',
                        name: `jadwal[${index}][hari]`,
                        value: hari
                    }).appendTo('#jadwalForm');

                    $('<input>').attr({
                        type: 'hidden',
                        name: `jadwal[${index}][mulai]`,
                        value: entry.mulai
                    }).appendTo('#jadwalForm');

                    $('<input>').attr({
                        type: 'hidden',
                        name: `jadwal[${index}][selesai]`,
                        value: entry.selesai
                    }).appendTo('#jadwalForm');

                    $('<input>').attr({
                        type: 'hidden',
                        name: `jadwal[${index}][pelajaran_id]`,
                        value: entry.pelajaranId
                    }).appendTo('#jadwalForm');

                    $('<input>').attr({
                        type: 'hidden',
                        name: `jadwal[${index}][guru_id]`,
                        value: entry.guruId || ''
                    }).appendTo('#jadwalForm');

                    $('<input>').attr({
                        type: 'hidden',
                        name: `jadwal[${index}][color]`,
                        value: entry.colorClass
                    }).appendTo('#jadwalForm');

                    index++;
                });
            }
        });
    });

    // Load kategori kelas
    $('#kelas').on('change', function() {
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
                success: function(response) {
                    if (response && response.length > 0) {
                        response.forEach(category => {
                            dropdown.append(`<option value="${category}">${category}</option>`);
                        });
                        dropdown.prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Gagal memuat kategori kelas!');
                },
                complete: function() {
                    $('#loadingOverlay').hide();
                }
            });
        }
    });

    // Auto-load kategori
    @if(old('kelas', $jadwal->kelas_id))
        $('#kelas').trigger('change');
    @endif
});
</script>
@endpush
