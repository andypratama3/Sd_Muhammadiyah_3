@extends('layouts.dashboard')
@section('title', 'Detail Rapot Siswa')

@push('css')
    <style>
        .detail-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header-custom {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header-custom h5 {
            margin: 0;
            font-weight: 700;
        }

        .card-body-custom {
            padding: 30px;
        }

        .info-section {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .info-section h6 {
            color: #007bff;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
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
            color: #212529 !important;
            font-weight: 500;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #007bff;
            font-size: 20px;
        }

        .catatan-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            min-height: 100px;
        }

        .catatan-text {
            color: #212529;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .file-section {
            background: white;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
        }

        .file-section.has-file {
            border: 2px solid #28a745;
            background: #f0fff4;
        }

        .file-icon {
            font-size: 48px;
            color: #007bff;
            margin-bottom: 15px;
            display: block;
        }

        .file-section.has-file .file-icon {
            color: #28a745;
        }

        .file-name {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 10px;
            word-break: break-all;
        }

        .file-size {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .file-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-file {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-download {
            background: #28a745;
            color: white;
        }

        .btn-download:hover {
            background: #218838;
            text-decoration: none;
            color: white;
        }

        .empty-file {
            color: #adb5bd;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-complete {
            background: #d4edda;
            color: #155724;
        }

        .status-partial {
            background: #fff3cd;
            color: #856404;
        }

        .status-empty {
            background: #f8d7da;
            color: #721c24;
        }

        .divider {
            border-top: 2px solid #dee2e6;
            margin: 30px 0;
        }

        .btn-container {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-container a,
        .btn-container button {
            flex: 0 1 auto;
        }

        .created-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .timeline {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .timeline-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-icon {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .timeline-content h6 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .timeline-content small {
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn-container a,
            .btn-container button {
                width: 100%;
                text-align: center;
            }

            .file-actions {
                flex-direction: column;
            }

            .btn-file {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.flashmessage')

        {{-- HEADER SECTION --}}
        <div class="detail-card">
            <div class="card-header-custom">
                <i class="fas fa-file-pdf"></i>
                <h5 class="text-white">Detail Rapot Siswa</h5>
            </div>

            <div class="card-body-custom">
                {{-- SISWA INFO --}}
                <div class="info-section">
                    <h6>
                        <i class="fas fa-user-circle"></i> Informasi Siswa
                    </h6>

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
                            <span class="info-label">Jenis Kelamin</span>
                            <span class="info-value">
                                @if($rapot->siswa->jk === 'L')
                                    <span class="badge bg-success">Laki-laki</span>
                                @else
                                    <span class="badge bg-warning">Perempuan</span>
                                @endif
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Tempat/Tanggal Lahir</span>
                            <span class="info-value">
                                {{ $rapot->siswa->tmpt_lahir ?? '-' }} /
                                {{ $rapot->siswa->tgl_lahir ? \Carbon\Carbon::parse($rapot->siswa->tgl_lahir)->format('d-m-Y') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- RAPOT INFO --}}
                <div class="info-section">
                    <h6>
                        <i class="fas fa-graduation-cap"></i> Informasi Rapot
                    </h6>

                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Kelas</span>
                            <span class="info-value">{{ $rapot->kelas->name ?? '-' }}</span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Tahun Ajaran</span>
                            <span class="info-value">
                                <span class="badge bg-info">{{ $rapot->tahun }}</span>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                @php
                                    $hasFile = !empty($rapot->file_rapot);
                                    $hasCatatan = !empty($rapot->catatan);
                                @endphp

                                @if($hasFile && $hasCatatan)
                                    <span class="status-badge status-complete">
                                        <i class="fas fa-check-circle"></i> Lengkap
                                    </span>
                                @elseif($hasFile || $hasCatatan)
                                    <span class="status-badge status-partial">
                                        <i class="fas fa-exclamation-circle"></i> Sebagian
                                    </span>
                                @else
                                    <span class="status-badge status-empty">
                                        <i class="fas fa-times-circle"></i> Kosong
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


                        <div class="info-item">
                            <span class="info-label">Terakhir Diubah</span>
                            <span class="info-value">
                                {{ $rapot->updated_at->format('d-m-Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CATATAN SECTION --}}
        <div class="detail-card">
            <div class="card-body-custom">
                <div class="section-title">
                    <i class="fas fa-sticky-note"></i> Catatan Rapot
                </div>

                @if($rapot->catatan)
                    <div class="catatan-box">
                        <div class="catatan-text">{{ $rapot->catatan }}</div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada catatan rapot
                    </div>
                @endif
            </div>
        </div>

        {{-- FILE SECTION --}}
        <div class="detail-card">
            <div class="card-body-custom">
                <div class="section-title">
                    <i class="fas fa-file"></i> File Rapot
                </div>

                @if($rapot->file_rapot && \Storage::disk('public')->exists($rapot->file_rapot))
                    <div class="file-section has-file">
                        <i class="fas fa-file-pdf file-icon"></i>
                        <div class="file-name">{{ basename($rapot->file_rapot) }}</div>
                        <div class="file-size">
                            {{ \Storage::disk('public')->size($rapot->file_rapot) ? number_format(\Storage::disk('public')->size($rapot->file_rapot) / 1024, 2) . ' KB' : 'Size unknown' }}
                        </div>
                        <div class="file-actions">
                            <a href="{{ asset('storage/' . $rapot->file_rapot) }}"
                               target="_blank"
                               class="btn-file btn-download">
                                <i class="fas fa-download"></i> Download File
                            </a>
                            <a href="{{ asset('storage/' . $rapot->file_rapot) }}"
                               target="_blank"
                               class="btn-file"
                               style="background: #17a2b8; color: white;">
                                <i class="fas fa-eye"></i> Lihat File
                            </a>
                        </div>
                    </div>
                @else
                    <div class="file-section">
                        <i class="fas fa-file-slash file-icon" style="color: #adb5bd;"></i>
                        <div class="empty-file">Tidak ada file rapot</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="btn-container">
            <a href="{{ route('dashboard.datamaster.rapot.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <div style="display: flex; gap: 10px;">
                <a href="{{ route('dashboard.datamaster.rapot.edit', $rapot->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Edit Rapot
                </a>

                <button type="button"
                        class="btn btn-danger btn-sm"
                        id="btnDelete"
                        data-id="{{ $rapot->id }}"
                        data-name="{{ $rapot->siswa->name }}">
                    <i class="fas fa-trash"></i> Hapus Rapot
                </button>

                <form id="deleteForm"
                      action="{{ route('dashboard.datamaster.rapot.destroy', $rapot->id) }}"
                      method="POST"
                      style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>

        {{-- METADATA --}}
        <div class="created-info">
            <div style="margin-bottom: 10px;">
                <strong>Informasi Sistem:</strong>
            </div>
            <ul class="timeline">
                <li class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Dibuat</h6>
                        <small>{{ $rapot->created_at->format('d-m-Y H:i:s') }} ({{ $rapot->created_at->diffForHumans() }})</small>
                    </div>
                </li>

                @if($rapot->updated_at != $rapot->created_at)
                    <li class="timeline-item">
                        <div class="timeline-icon" style="background: #17a2b8;">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Terakhir Diubah</h6>
                            <small>{{ $rapot->updated_at->format('d-m-Y H:i:s') }} ({{ $rapot->updated_at->diffForHumans() }})</small>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    // Delete handler
    $('#btnDelete').on('click', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');

        if (confirm(`Apakah Anda yakin ingin menghapus rapot ${name}? Tindakan ini tidak dapat dibatalkan.`)) {
            $('#deleteForm').submit();
        }
    });
});
</script>
@endpush
