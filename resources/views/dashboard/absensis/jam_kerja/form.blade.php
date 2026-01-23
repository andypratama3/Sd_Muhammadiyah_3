<div class="mt-2 form-group">
    <label>Nama Shift</label>
    <input type="text"
           name="nama_shift"
           class="form-control @error('nama_shift') is-invalid @enderror"
           value="{{ old('nama_shift', $jamKerja->nama_shift ?? '') }}"
           required>
    @error('nama_shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mt-2 form-group">
    <label>Jenis Pegawai</label>
    <select name="jenis_pegawai"
            class="form-control @error('jenis_pegawai') is-invalid @enderror"
            required>
        <option value="">-- Pilih --</option>
        <option value="guru"
            {{ old('jenis_pegawai', $jamKerja->jenis_pegawai ?? '') == 'guru' ? 'selected' : '' }}>
            Guru
        </option>
        <option value="tenaga-pendidikan"
            {{ old('jenis_pegawai', $jamKerja->jenis_pegawai ?? '') == 'tenaga-pendidikan' ? 'selected' : '' }}>
            Tenaga Pendidikan
        </option>
    </select>
    @error('jenis_pegawai') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mt-2 form-group">
    <label>Hari</label>
    <select name="hari"
            class="form-control @error('hari') is-invalid @enderror"
            required>
        @foreach (['senin','selasa','rabu','kamis','jumat','sabtu','minggu'] as $hari)
            <option value="{{ $hari }}"
                {{ old('hari', $jamKerja->hari ?? '') == $hari ? 'selected' : '' }}>
                {{ ucfirst($hari) }}
            </option>
        @endforeach
    </select>
    @error('hari') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="mt-2 col-md-6">
        <label>Jam Masuk</label>
        <input type="time" name="jam_masuk"
               class="form-control"
               value="{{ old('jam_masuk', $jamKerja->jam_masuk ?? '') }}" required>
    </div>

    <div class="mt-2 col-md-6">
        <label>Batas Masuk</label>
        <input type="time" name="batas_masuk"
               class="form-control"
               value="{{ old('batas_masuk', $jamKerja->batas_masuk ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="mt-2 col-md-6">
        <label>Jam Pulang</label>
        <input type="time" name="jam_pulang"
               class="form-control"
               value="{{ old('jam_pulang', $jamKerja->jam_pulang ?? '') }}" required>
    </div>

    <div class="mt-2 col-md-6">
        <label>Batas Pulang</label>
        <input type="time" name="batas_pulang"
               class="form-control"
               value="{{ old('batas_pulang', $jamKerja->batas_pulang ?? '') }}" required>
    </div>
</div>

<div class="mt-3 form-group">
    <div class="form-check">
        <input class="form-check-input"
               type="checkbox"
               name="is_default"
               value="1"
               {{ old('is_default', $jamKerja->is_default ?? false) ? 'checked' : '' }}>
        <label class="form-check-label">
            Jadikan Jam Kerja Default
        </label>
    </div>
</div>
