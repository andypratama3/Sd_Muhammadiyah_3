<!-- resources/views/dashboard/data/rapot/edit.blade.php -->

@extends('layouts.dashboard')
@section('title', 'Edit Rapot Siswa')

@push('css')
    <style>
        .required::after {
            content: " *";
            color: red;
        }

        .info-section {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .info-section h5 {
            color: #007bff;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: #212529;
            font-weight: 500;
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

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
            font-size: 13px;
        }

        .form-control {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .file-section {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .file-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }

        .file-info h6 {
            margin: 0 0 10px 0;
            color: #212529;
            font-weight: 600;
        }

        .file-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .file-name {
            color: #007bff;
            font-weight: 500;
            word-break: break-all;
            flex: 1;
        }

        .file-size {
            color: #6c757d;
            font-size: 12px;
        }

        .file-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-view-file {
            background: #17a2b8;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-view-file:hover {
            background: #138496;
            text-decoration: none;
            color: white;
        }

        .btn-delete-file {
            background: #ff4757;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }

        .btn-delete-file:hover {
            background: #ff3838;
        }

        .upload-section {
            border: 2px dashed #dee2e6;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            background: #fafbfc;
            transition: all 0.2s;
            cursor: pointer;
        }

        .upload-section:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .upload-section.dragover {
            border-color: #007bff;
            background: #e7f3ff;
        }

        .upload-icon {
            font-size: 32px;
            color: #007bff;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .upload-hint {
            color: #adb5bd;
            font-size: 12px;
            margin-top: 10px;
        }

        .textarea-custom {
            min-height: 120px;
            resize: vertical;
        }

        .btn-container {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-container a, .btn-container button {
            flex: 0 1 auto;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-with-file {
            background: #d4edda;
            color: #155724;
        }

        .status-without-file {
            background: #fff3cd;
            color: #856404;
        }

        .divider {
            border-top: 2px solid #dee2e6;
            margin: 30px 0;
        }

        #fileInput {
            display: none;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .file-details {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn-container a, .btn-container button {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')

    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Rapot Siswa</h6>
    </div>

    <div class="card-body">
        {{-- INFO SECTION --}}
        <div class="info-section">
            <h5>
                <i class="fas fa-user"></i> Informasi Rapot
            </h5>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nama Siswa</span>
                    <span class="info-value">{{ $rapot->siswa->name ?? '-' }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">NISN</span>
                    <span class="info-value">{{ $rapot->siswa->nisn ?? '-' }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Kelas</span>
                    <span class="info-value">{{ $rapot->kelas->name ?? '-' }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Tahun Ajaran</span>
                    <span class="info-value">{{ $rapot->tahun }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Jenis Kelamin</span>
                    <span class="info-value">
                        @if($rapot->siswa->jk === 'L')
                            <span class="badge bg-info">Laki-laki</span>
                        @else
                            <span class="badge bg-danger">Perempuan</span>
                        @endif
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Status File</span>
                    <span class="info-value">
                        @if($rapot->file_rapot)
                            <span class="status-badge status-with-file">
                                <i class="fas fa-check-circle"></i> Ada File
                            </span>
                        @else
                            <span class="status-badge status-without-file">
                                <i class="fas fa-times-circle"></i> Tanpa File
                            </span>
                        @endif
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Kategori</span>
                    <span class="info-value">
                        <span class="status-badge status-with-file">
                            <i class="fas fa-check-circle"></i> {{ $rapot->kategori }}
                        </span>
                    </span>
                </div>

            </div>
        </div>

        {{-- FORM SECTION --}}
        <form action="{{ route('dashboard.datamaster.rapot.update', $rapot->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
            @csrf
            @method('PUT')

            {{-- CATATAN SECTION --}}
            <div class="form-card">
                <div class="form-title">
                    <i class="fas fa-edit"></i> Catatan Rapot
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea
                        name="catatan"
                        class="form-control textarea-custom @error('catatan') is-invalid @enderror"
                        placeholder="Masukkan catatan rapot...">{{ old('catatan', $rapot->catatan) }}</textarea>
                    <small class="form-text text-muted">Masukkan catatan atau keterangan tentang rapot siswa ini</small>
                    @error('catatan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="">Kategori</label>
                    <select name="kategori" class="form-control @error('kategori') is-invalid @enderror">
                        <option value="ganjil" {{ $rapot->kategori === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="genap" {{ $rapot->kategori === 'Genap' ? 'selected' : '' }}>Genap</option>
                        <option value="tengah" {{ $rapot->kategori === 'Tengah' ? 'selected' : '' }}>Tengah</option>
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- FILE SECTION --}}
            <div class="form-card">
                <div class="form-title">
                    <i class="fas fa-file"></i> File Rapot
                </div>

                {{-- EXISTING FILE --}}
                @if($rapot->file_rapot)
                    <div class="file-section">
                        <div class="file-info">
                            <h6>File Saat Ini</h6>
                            <div class="file-details">
                                <span class="file-name">
                                    <i class="fas fa-file-pdf"></i> {{ basename($rapot->file_rapot) }}
                                </span>
                                <span class="file-size">
                                    {{ \Storage::disk('public')->exists($rapot->file_rapot) ? 'Ada di storage' : 'File tidak ditemukan' }}
                                </span>
                            </div>
                            <div class="file-actions">
                                <a
                                    href="{{ asset('storage/' . $rapot->file_rapot) }}"
                                    target="_blank"
                                    class="btn-view-file"
                                    title="Buka file di tab baru">
                                    <i class="fas fa-download"></i> Lihat File
                                </a>
                                <button
                                    type="button"
                                    class="btn-delete-file"
                                    id="btnDeleteFile"
                                    title="Hapus file yang ada">
                                    <i class="fas fa-trash"></i> Hapus File
                                </button>
                            </div>
                        </div>

                        <div id="deleteFileAlert" style="display: none;" class="mt-3 alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> File akan dihapus saat disimpan
                            <button type="button" class="float-right btn btn-sm btn-outline-warning" id="btnUndoDelete">
                                Batal Hapus
                            </button>
                        </div>
                    </div>

                    <div class="divider"></div>
                @endif

                {{-- NEW FILE UPLOAD --}}
                <div>
                    <label class="form-label">Upload File Baru
                        @if($rapot->file_rapot)
                            <small class="text-muted">(untuk mengganti file yang ada)</small>
                        @endif
                    </label>

                    <div class="upload-section" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            Drag & drop file di sini atau klik untuk memilih
                        </div>
                        <input
                            type="file"
                            name="file_rapot"
                            id="fileInput"
                            class="@error('file_rapot') is-invalid @enderror"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <div class="upload-hint">
                            Format: PDF, DOC, DOCX, JPG, PNG | Ukuran maksimal: 5MB
                        </div>
                    </div>

                    <div id="filePreview" style="display: none;" class="p-3 mt-3 rounded bg-light">
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i>
                            <span id="filePreviewName"></span> (<span id="filePreviewSize"></span>)
                        </small>
                        <br>
                        <small class="mt-2 text-muted">File ini akan menggantikan file sebelumnya</small>
                    </div>

                    @error('file_rapot')
                        <div class="mt-2 alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- BUTTONS --}}
            <div class="btn-container">
                <a href="{{ route('dashboard.datamaster.rapot.index') }}" class="btn btn-sm btn-danger">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    let fileToDelete = false;
    let fileSelected = false;

    // File upload area click handler - FIXED to prevent infinite loop
    $('#uploadArea').on('click', function(e) {
        // Only trigger file input click if we're not clicking on the file input itself
        if (e.target.id !== 'fileInput') {
            $('#fileInput').trigger('click');
        }
    });

    // Drag and drop handlers
    $('#uploadArea').on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
        return false;
    });

    $('#uploadArea').on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        return false;
    });

    $('#uploadArea').on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            // Manually set files to the input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            $('#fileInput')[0].files = dataTransfer.files;
            handleFileSelect();
        }
        return false;
    });

    // File input change handler
    $('#fileInput').on('change', function() {
        handleFileSelect();
    });

    function handleFileSelect() {
        const fileInput = $('#fileInput')[0];
        const file = fileInput.files[0];

        if (!file) {
            $('#filePreview').hide();
            fileSelected = false;
            return;
        }

        // Validate file type
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png'
        ];

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Format file tidak didukung! Gunakan: PDF, DOC, DOCX, JPG, PNG'
            });
            fileInput.value = '';
            $('#filePreview').hide();
            fileSelected = false;
            return;
        }

        // Validate file size (5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ukuran file terlalu besar! Maksimal 5MB'
            });
            fileInput.value = '';
            $('#filePreview').hide();
            fileSelected = false;
            return;
        }

        // Show preview
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        $('#filePreviewName').text(file.name);
        $('#filePreviewSize').text(sizeMB + ' MB');
        $('#filePreview').show();
        fileSelected = true;
    }

    // Delete file handler
    $('#btnDeleteFile').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menghapus file ini?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fileToDelete = true;
                $(this).closest('.file-info').hide();
                $('#deleteFileAlert').show();

                // Add hidden input to mark file for deletion
                if (!$('#editForm').find('input[name="delete_file"]').length) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'delete_file',
                        value: '1'
                    }).appendTo('#editForm');
                }
            }
        });
    });

    // Undo delete handler
    $('#btnUndoDelete').on('click', function(e) {
        e.preventDefault();

        fileToDelete = false;
        $('#deleteFileAlert').hide();
        $(this).closest('.file-info').parent().find('.file-info').show();
        $('#editForm').find('input[name="delete_file"]').remove();
    });

    // Form submit handler
    $('#editForm').on('submit', function(e) {
        // Validasi minimal ada catatan atau file
        const catatan = $('textarea[name="catatan"]').val().trim();
        const hasNewFile = fileSelected;
        const hasExistingFile = {{ $rapot->file_rapot ? 'true' : 'false' }};
        const willDeleteFile = fileToDelete;

        const finalHasFile = (hasExistingFile && !willDeleteFile) || hasNewFile;

        if (!catatan && !finalHasFile) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Minimal harus ada catatan atau file rapot!'
            });
            return false;
        }

        // Show loading
        $('#submitBtn').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Menyimpan...'
        );
    });
});
</script>
@endpush
