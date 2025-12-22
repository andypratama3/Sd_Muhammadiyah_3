@extends('layouts.dashboard')
@section('title', 'Detail Jadwal')

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
        <h6 class="m-0 font-weight-bold text-primary">Detail Jadwal {{ $jadwal->tahun_ajaran }}</h6>
    </div>

    <div class="card-body position-relative">
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
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
                            <th width="15%">Hari</th>
                            <th width="20%">Waktu</th>
                            <th width="50%">Mata Pelajaran & Guru</th>
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
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
@endpush
