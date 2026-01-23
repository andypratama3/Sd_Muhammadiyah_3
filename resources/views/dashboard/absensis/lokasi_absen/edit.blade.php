@extends('layouts.dashboard')

@section('title', 'Edit Lokasi Absensi')

@section('content')
<div class="mb-4 card">
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            Edit Lokasi Absensi
        </h6>
    </div>

    <div class="card-body">
        <form action="{{ route('dashboard.lokasi.absen.update', $lokasiAbsensi->id) }}"
              method="POST">
            @csrf
            @method('PUT')

            {{-- Nama Lokasi --}}
            <div class="mt-2 form-group">
                <label class="form-label">Nama Lokasi</label>
                <input type="text"
                       name="nama_lokasi"
                       class="form-control @error('nama_lokasi') is-invalid @enderror"
                       placeholder="Contoh: Kantor Utama"
                       value="{{ old('nama_lokasi', $lokasiAbsensi->nama_lokasi) }}"
                       required>

                @error('nama_lokasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Latitude --}}
            <div class="mt-2 form-group">
                <label class="form-label">Latitude</label>
                <input type="text"
                       name="latitude"
                       class="form-control @error('latitude') is-invalid @enderror"
                       placeholder="-0.502183"
                       value="{{ old('latitude', $lokasiAbsensi->latitude) }}"
                       required>

                @error('latitude')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Longitude --}}
            <div class="mt-2 form-group">
                <label class="form-label">Longitude</label>
                <input type="text"
                       name="longitude"
                       class="form-control @error('longitude') is-invalid @enderror"
                       placeholder="117.153801"
                       value="{{ old('longitude', $lokasiAbsensi->longitude) }}"
                       required>

                @error('longitude')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Radius --}}
            <div class="mt-2 form-group">
                <label class="form-label">Radius (Meter)</label>
                <input type="number"
                       name="radius"
                       class="form-control @error('radius') is-invalid @enderror"
                       placeholder="Contoh: 150"
                       value="{{ old('radius', $lokasiAbsensi->radius) }}"
                       required>

                @error('radius')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Alamat --}}
            <div class="mt-2 form-group">
                <label class="form-label">Alamat</label>
                <textarea name="alamat"
                          class="form-control @error('alamat') is-invalid @enderror"
                          rows="3"
                          placeholder="Alamat lengkap lokasi"
                          required>{{ old('alamat', $lokasiAbsensi->alamat) }}</textarea>

                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Status --}}
            <div class="mt-2 form-group">
                <label class="form-label">Status</label>
                <select name="status"
                        class="form-control @error('status') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Status --</option>
                    <option value="aktif"
                        {{ old('status', $lokasiAbsensi->status) == 'aktif' ? 'selected' : '' }}>
                        Aktif
                    </option>
                    <option value="nonaktif"
                        {{ old('status', $lokasiAbsensi->status) == 'nonaktif' ? 'selected' : '' }}>
                        Nonaktif
                    </option>
                </select>

                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Action --}}
            <div class="mt-4 form-group">
                <a href="{{ route('dashboard.lokasi.absen.index') }}"
                   class="btn btn-danger float-start">
                    Kembali
                </a>

                <button type="submit" class="btn btn-primary float-end">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
