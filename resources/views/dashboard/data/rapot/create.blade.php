@extends('layouts.dashboard')
@section('title', 'Tambah Rapot Siswa')

@push('css')
    <link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet">
    <link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        .required::after {
            content: " *";
            color: red;
        }

        .filter-section {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .filter-title {
            font-size: 16px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-title i {
            color: #007bff;
            font-size: 20px;
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

        .filter-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 30px;
            color: #007bff;
        }

        .loading-spinner.active {
            display: block;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .table-header {
            background: #007bff;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header i {
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        thead th {
            padding: 12px 15px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #495057;
        }

        tbody tr {
            border-bottom: 1px solid #dee2e6;
            transition: all 0.2s;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        tbody td {
            padding: 12px 15px;
            font-size: 13px;
        }

        .siswa-no {
            color: #6c757d;
            font-weight: 500;
        }

        .siswa-name {
            font-weight: 500;
            color: #212529;
        }

        .nisn-small {
            font-size: 11px;
            color: #adb5bd;
        }

        .form-control-sm {
            padding: 6px 10px;
            font-size: 12px;
            height: auto;
        }

        .btn-remove-row {
            background: #ff4757;
            border: none;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            transition: all 0.2s;
        }

        .btn-remove-row:hover {
            background: #ff3838;
        }

        .summary-info {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .info-text {
            color: #007bff;
            font-weight: 600;
            font-size: 16px;
        }

        .info-detail {
            font-size: 12px;
            color: #6c757d;
            margin-top: 3px;
        }

        .stats-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 700;
            color: #007bff;
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

        .btn-container {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn-container a, .btn-container button {
            flex: 0 1 auto;
        }

        .alert-info {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            color: #004085;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .alert-info i {
            margin-right: 8px;
        }

        .spinner-border {
            width: 20px;
            height: 20px;
            border-width: 3px;
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }

            .summary-row {
                flex-direction: column;
                gap: 10px;
            }

            .stats-group {
                flex-direction: column;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }
        }
    </style>
@endpush

@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')

    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Input Rapot Siswa - Batch Processing</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.rapot.store') }}" method="POST" enctype="multipart/form-data" id="rapotForm">
            @csrf

            {{-- FILTER SECTION --}}
            <div class="filter-section">
                <div class="filter-title">
                    <i class="fas fa-filter"></i> Filter Data Siswa
                </div>

                <div class="filter-row">
                    <div>
                        <label class="form-label required">Kelas</label>
                        <select name="kelas" id="kelas" class="form-control select2 @error('kelas') is-invalid @enderror" required>
                            <option value="" selected disabled>Pilih Kelas</option>
                            @foreach ($kelass as $kelas)
                                <option value="{{ $kelas->id }}" {{ old('kelas') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label required">Tahun Ajaran</label>
                        <select name="tahun" id="tahun" class="form-control select2 @error('tahun') is-invalid @enderror" required>
                            <option value="" selected disabled>Pilih Tahun</option>
                            @for ($year = date('Y') - 2; $year <= date('Y'); $year++)
                                <option value="{{ $year }}" {{ old('tahun') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                        @error('tahun')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div>
                    <label class="form-label required">Kategori</label>
                    <select name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" required>
                        <option value="" selected disabled>Pilih kategori</option>
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                        <option value="tengah">Tengah</option>
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- LOADING SPINNER --}}
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p style="margin-top: 10px;">Memuat data siswa...</p>
            </div>

            {{-- TABLE SECTION --}}
            <div id="tableSection" style="display: none;">
                <div class="alert-info">
                    <i class="fas fa-info-circle"></i>
                    Isi catatan atau upload file rapot untuk setiap siswa. Minimal salah satu harus diisi.
                </div>

                <div class="summary-info">
                    <div class="summary-row">
                        <div>
                            <div class="info-text">
                                <i class="fas fa-users"></i> Data Siswa
                            </div>
                            <div class="info-detail">Total: <strong id="totalSiswa">0</strong> siswa | Kelas: <strong id="infoKelas">-</strong></div>
                        </div>
                        <div class="stats-group">
                            <div class="stat-item">
                                <div class="stat-label">Dengan Catatan</div>
                                <div class="stat-value" id="catatanCount">0</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Dengan File</div>
                                <div class="stat-value" id="fileCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <i class="fas fa-table"></i> Data Rapot Siswa
                    </div>
                    <table id="siswaTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Siswa</th>
                                <th width="30%">Catatan</th>
                                <th width="25%">File Rapot</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="siswaTableBody">
                        </tbody>
                    </table>
                </div>

                <div id="emptyState" class="empty-state" style="display: none;">
                    <i class="fas fa-inbox"></i>
                    <p>Tidak ada siswa untuk kelas yang dipilih</p>
                </div>
            </div>

            {{-- BUTTONS --}}
            <div class="btn-container">
                <a href="{{ route('dashboard.datamaster.rapot.index') }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <button type="submit" class="btn btn-sm btn-primary" id="submitBtn" style="display: none;">
                    <i class="fas fa-save"></i> Simpan Rapot (Batch)
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="text-white modal-header bg-primary">
                <h5 class="text-white modal-title"><i class="fas fa-check-circle"></i> Konfirmasi Penyimpanan</h5>
            </div>
            <div class="modal-body">
                <p>Anda akan menyimpan rapot untuk:</p>
                <ul id="confirmList" style="margin: 15px 0;" class="text-black">
                </ul>
                <div class="alert alert-info">
                    <strong>Total:</strong> <span id="confirmTotal">0</span> siswa akan diproses
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="confirmSubmit">
                    <i class="fas fa-check"></i> Ya, Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    let loadingSiswa = false;

    function initSelect2() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    initSelect2();

    // Load siswa data saat filter berubah
    $('#kelas, #tahun').on('change', function() {
        const kelasId = $('#kelas').val();
        const tahun = $('#tahun').val();

        if (kelasId && tahun && !loadingSiswa) {
            loadSiswaData(kelasId, tahun);
        }
    });

    function loadSiswaData(kelasId, tahun) {
        loadingSiswa = true;
        $('#loadingSpinner').addClass('active');
        $('#tableSection').slideUp();

        $.ajax({
            url: '{{ route("dashboard.datamaster.rapot.get_siswa") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                kelas_id: kelasId,
                tahun: tahun
            },
            success: function(response) {
                renderSiswaTable(response.siswa, response.kelas_name);
            },
            error: function(xhr) {
                $('#loadingSpinner').removeClass('active');
                const errorMsg = xhr.responseJSON?.error || 'Gagal memuat data siswa!';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            },
            complete: function() {
                $('#loadingSpinner').removeClass('active');
                loadingSiswa = false;
            }
        });
    }

    function renderSiswaTable(siswas, kelasName) {
        const tbody = $('#siswaTableBody');
        tbody.empty();

        if (siswas.length === 0) {
            $('#emptyState').show();
            $('#siswaTable').hide();
            $('#tableSection').slideDown();
            $('#submitBtn').hide();
            return;
        }

        $('#emptyState').hide();
        $('#siswaTable').show();
        $('#totalSiswa').text(siswas.length);
        $('#infoKelas').text(kelasName);

        siswas.forEach((siswa, index) => {
            const row = `
                <tr>
                    <td class="siswa-no">${index + 1}</td>
                    <td class="siswa-name">
                        ${siswa.name}
                        <br><span class="nisn-small">NISN: ${siswa.nisn || '-'}</span>
                        <br><span class="nisn-small" style="font-size: 10px; color: #adb5bd;">Jk: ${siswa.jk === 'L' ? 'Laki-laki' : 'Perempuan'}</span>
                    </td>
                    <td>
                        <textarea name="rapot[${index}][catatan]" class="form-control form-control-sm catatan-input" placeholder="Catatan rapot..." rows="1"></textarea>
                    </td>
                    <td>
                        <input type="file" name="rapot[${index}][file_rapot]" class="form-control form-control-sm file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted">Max 5MB</small>
                    </td>
                    <td>
                        <button type="button" class="btn-remove-row" data-index="${index}" title="Hapus baris">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);

            // Add hidden inputs for form data
            tbody.before(`
                <input type="hidden" name="rapot[${index}][siswa_id]" value="${siswa.id}">
                <input type="hidden" name="rapot[${index}][tahun_ajaran]" value="${$('#tahun').val()}">
                <input type="hidden" name="rapot[${index}][kelas_id]" value="${siswa.kelas_id}">
            `);
        });

        // Attach remove handlers
        $('.btn-remove-row').on('click', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            updateStats();
        });

        // Attach change handlers for stats
        $('#siswaTableBody').on('input', '.catatan-input, .file-input', function() {
            updateStats();
        });

        $('#tableSection').slideDown();
        $('#submitBtn').show();
        updateStats();
    }

    function updateStats() {
        let catatanCount = 0;
        let fileCount = 0;

        $('#siswaTableBody tr').each(function() {
            const catatan = $(this).find('.catatan-input').val().trim();
            const fileInput = $(this).find('.file-input');
            const hasFile = fileInput[0].files && fileInput[0].files.length > 0;

            if (catatan) catatanCount++;
            if (hasFile) fileCount++;
        });

        $('#catatanCount').text(catatanCount);
        $('#fileCount').text(fileCount);
    }

    // Form submit with confirmation
    $('#rapotForm').on('submit', function(e) {
        e.preventDefault();

        const rows = $('#siswaTableBody tr').length;

        if (rows === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Harap pilih siswa untuk diinput rapot!'
            });
            return false;
        }

        // Build confirmation list
        let confirmList = '';
        let totalValid = 0;

        $('#siswaTableBody tr').each(function() {
            const name = $(this).find('.siswa-name').text().split('\n')[0].trim();
            const catatan = $(this).find('.catatan-input').val().trim();
            const fileInput = $(this).find('.file-input');
            const hasFile = fileInput[0].files && fileInput[0].files.length > 0;

            if (catatan || hasFile) {
                confirmList += `<li class="text-black">${name} ${hasFile ? '<i class="fas fa-file text-success"></i>' : '<i class="fas fa-sticky-note text-info"></i>'}</li>`;
                totalValid++;
            }
        });

        if (totalValid === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Minimal ada satu siswa dengan catatan atau file rapot!'
            });
            return false;
        }

        $('#confirmList').html(confirmList);
        $('#confirmTotal').text(totalValid);
        $('#confirmModal').modal('show');
    });

    // Confirm submit
    $('#confirmSubmit').on('click', function() {
        $('#confirmModal').modal('hide');

        // Ensure hidden inputs are properly set
        const kelasId = $('#kelas').val();
        const tahun = $('#tahun').val();

        // Add or update hidden inputs
        $('#rapotForm').find('input[name="kelas"]').remove();
        $('#rapotForm').find('input[name="tahun"]').remove();

        $('<input>').attr({
            type: 'hidden',
            name: 'kelas',
            value: kelasId
        }).appendTo('#rapotForm');

        $('<input>').attr({
            type: 'hidden',
            name: 'tahun',
            value: tahun
        }).appendTo('#rapotForm');

        document.getElementById('rapotForm').submit();
    });
});
</script>
@endpush
