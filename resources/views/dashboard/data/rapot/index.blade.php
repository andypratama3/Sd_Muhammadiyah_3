<!-- resources/views/dashboard/data/rapot/index.blade.php -->

@extends('layouts.dashboard')
@section('title', 'Rapot Siswa')

@push('css')
    <link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet">
    <link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        .badge-custom {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-file {
            background: #d4edda;
            color: #155724;
        }

        .badge-catatan {
            background: #cce5ff;
            color: #004085;
        }

        .badge-both {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-empty {
            background: #f8d7da;
            color: #721c24;
        }

        .filter-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-control-sm {
            font-size: 13px;
        }

        .search-group {
            display: flex;
            gap: 10px;
        }

        .search-group input {
            flex: 1;
        }

        .search-group button {
            padding: 6px 15px;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .table-header {
            background: #007bff;
            color: white;
            padding: 15px;
            font-weight: 600;
        }

        table {
            margin-bottom: 0;
        }

        thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            text-align: center;
        }

        tbody td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .btn-action {
            padding: 5px 10px;
            font-size: 11px;
            margin: 2px;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #adb5bd;
        }

        .no-data i {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="mb-4 col-lg-12">
        <!-- Filter Card -->
        <div class="filter-card">
            <form action="{{ route('dashboard.datamaster.rapot.index') }}" method="GET" id="filterForm">
                <div class="filter-row">
                    <div>
                        <label class="mb-2 form-label small fw-bold">Cari Siswa/NISN</label>
                        <input
                            type="text"
                            name="search"
                            class="form-control form-control-sm"
                            placeholder="Nama atau NISN..."
                            value="{{ request('search') }}">
                    </div>



                    <div>
                        <label class="mb-2 form-label small fw-bold">Tahun Ajaran</label>
                        <select name="tahun" class="form-control form-control-sm">
                            <option value="">-- Semua Tahun --</option>
                            @for ($year = date('Y') - 2; $year <= date('Y'); $year++)
                                <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 form-label small fw-bold">Status File</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="with-file" {{ request('status') == 'with-file' ? 'selected' : '' }}>Ada File</option>
                            <option value="without-file" {{ request('status') == 'without-file' ? 'selected' : '' }}>Tanpa File</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 form-label small fw-bold">Kategori</label>
                        <select name="kategori" class="form-control form-control-sm">
                            <option value="">-- Semua Kategori --</option>
                            <option value="ganjil" {{ request('kategori') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="genap" {{ request('kategori') == 'genap' ? 'selected' : '' }}>Genap</option>
                            <option value="tengah" {{ request('kategori') == 'tengah' ? 'selected' : '' }}>Tengah</option>
                        </select>
                    </div>
                </div>



                <div>
                    <label class="mb-2 form-label small fw-bold">Kelas</label>
                    <select name="kelas" class="form-control form-control-sm select2">
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($kelass as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                 <div class="search-group" style="margin-top: 15px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="{{ route('dashboard.datamaster.rapot.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                    <a href="{{ route('dashboard.datamaster.rapot.create') }}" class="ml-auto text-end btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Tambah Rapot
                    </a>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="card">
            @include('layouts.flashmessage')

            <div class="table-header" style="border-radius: 10px; mx-1">
                <h6 class="m-0 text-white">
                    <i class="fas fa-file-pdf"></i> Data Rapot Siswa
                    <span class="badge badge-light" style="float: right;">
                        Total: {{ $rapots->total() }} Data
                    </span>
                </h6>
            </div>

            <div class="table-responsive">
                @if($rapots->count() > 0)
                    <table class="table text-center align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Nama Siswa</th>
                                <th width="10%">NISN</th>
                                <th width="12%">Kelas</th>
                                <th width="8%">Tahun</th>
                                <th width="15%">Kategori</th>
                                <th width="12%">Status File</th>
                                <th width="18%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rapots as $rapot)
                            <tr>
                                <td>{{ ($rapots->currentPage() - 1) * $rapots->perPage() + $loop->iteration }}</td>

                                <td class="text-left">
                                    <strong>{{ $rapot->siswa->name ?? '-' }}</strong>
                                </td>

                                <td>
                                    {{ $rapot->siswa->nisn ?? '-' }}
                                </td>

                                <td>
                                    {{ $rapot->kelas->name ?? '-' }}
                                </td>

                                <td>
                                    <span class="text-black badge badge-info">{{ $rapot->tahun }}</span>
                                </td>

                                <td class="text-left">
                                    <span class="badge bg-{{ $rapot->kategori === 'Ganjil' ? 'primary' : ($rapot->kategori === 'Genap' ? 'success' : 'warning') }}">
                                        {{ $rapot->kategori }}
                                    </span>
                                </td>

                                <td>
                                    @if($rapot->file_rapot && $rapot->catatan)
                                        <span class="badge-custom badge-both">
                                            <i class="fas fa-check-circle"></i> File & Catatan
                                        </span>
                                    @elseif($rapot->file_rapot)
                                        <span class="badge-custom badge-file">
                                            <i class="fas fa-file"></i> File
                                        </span>
                                    @elseif($rapot->catatan)
                                        <span class="badge-custom badge-catatan">
                                            <i class="fas fa-sticky-note"></i> Catatan
                                        </span>
                                    @else
                                        <span class="badge-custom badge-empty">
                                            <i class="fas fa-times"></i> Kosong
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.datamaster.rapot.show', $rapot->id) }}"
                                           class="btn btn-info btn-action btn-sm"
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('dashboard.datamaster.rapot.edit', $rapot->id) }}"
                                           class="btn btn-primary btn-action btn-sm"
                                           title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-danger btn-action btn-sm delete-btn"
                                                data-id="{{ $rapot->id }}"
                                                data-name="{{ $rapot->siswa->name }}"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $rapot->id }}"
                                              action="{{ route('dashboard.datamaster.rapot.destroy', $rapot->id) }}"
                                              method="POST"
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $rapots->links('pagination::bootstrap-4') }}
                    </div>
                @else
                    <div class="no-data">
                        <i class="fas fa-inbox"></i>
                        <p>Tidak ada data rapot</p>
                        <a href="{{ route('dashboard.datamaster.rapot.create') }}" class="mt-3 btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Rapot
                        </a>
                    </div>
                @endif
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
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Delete handler dengan SweetAlert2
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Penghapusan',
            text: `Apakah Anda yakin ingin menghapus rapot ${name}? Data yang dihapus tidak dapat dikembalikan.`,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            revserseButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $(`#delete-form-${id}`).submit();
            }
        });
    });
});
</script>
@endpush
