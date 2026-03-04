<div class="mt-2 form-group">
    <label>Jenis Cuti</label>
    <select name="jenis"
            class="form-control @error('jenis') is-invalid @enderror"
            required>
        <option value="">-- Pilih --</option>
        @foreach (['cuti','izin','sakit'] as $jenis)
            <option value="{{ $jenis }}"
                {{ old('jenis', $pengajuanCuti->jenis ?? '') == $jenis ? 'selected' : '' }}>
                {{ ucfirst($jenis) }}
            </option>
        @endforeach
    </select>
    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="mt-2 col-md-6">
        <label>Tanggal Mulai</label>
        <input type="date"
               name="tanggal_mulai"
               class="form-control @error('tanggal_mulai') is-invalid @enderror"
               value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($pengajuanCuti->tanggal_mulai ?? '')->format('Y-m-d')) }}"
               required>
        @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-2 col-md-6">
        <label>Tanggal Selesai</label>
        <input type="date"
               name="tanggal_selesai"
               class="form-control @error('tanggal_selesai') is-invalid @enderror"
               value="{{ old('tanggal_selesai', \Carbon\Carbon::parse($pengajuanCuti->tanggal_selesai ?? '')->format('Y-m-d')) }}"
               required>
        @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-2 form-group">
    <label>Jumlah Hari</label>
    <input type="number"
           name="jumlah_hari"
           class="form-control @error('jumlah_hari') is-invalid @enderror"
           value="{{ old('jumlah_hari', $pengajuanCuti->jumlah_hari ?? '') }}"
           required>
    @error('jumlah_hari') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mt-2 form-group">
    <label>Alasan</label>
    <textarea name="alasan"
              rows="3"
              class="form-control @error('alasan') is-invalid @enderror"
              required>{{ old('alasan', $pengajuanCuti->alasan ?? '') }}</textarea>
    @error('alasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mt-2 form-group">
    <label>File Pendukung (Opsional)</label>
    <input type="file"
           name="file_pendukung"
           class="form-control @error('file_pendukung') is-invalid @enderror">
    @error('file_pendukung') <div class="invalid-feedback">{{ $message }}</div> @enderror

    @isset($pengajuanCuti)
        @if ($pengajuanCuti->file_pendukung)
            <small class="text-muted">
                File saat ini:
                <a href="{{ asset('storage/'.$pengajuanCuti->file_pendukung) }}"
                   target="_blank">Lihat</a>
            </small>
        @endif
    @endisset
</div>
<div class="mt-2 form-group">
    <label>Catatan Admin (Opsional)</label>
    <textarea name="catatan_admin"
              rows="3"
              class="form-control @error('catatan_admin') is-invalid @enderror">{{ old('catatan_admin', $pengajuanCuti->catatan_admin ?? '') }}</textarea>
    @error('catatan_admin') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>


@role('admin|superadmin')
<div class="mt-2 form-group">
    <label>Status Pengajuan</label>
    <select name="status"
            class="form-control @error('status') is-invalid @enderror"
            required>
        <option value="menunggu"
            {{ old('status', $pengajuanCuti->status ?? 'menunggu') == 'menunggu' ? 'selected' : '' }}>
            Menunggu
        </option>
        <option value="disetujui"
            {{ old('status', $pengajuanCuti->status ?? '') == 'disetujui' ? 'selected' : '' }}>
            Disetujui
        </option>
        <option value="ditolak"
            {{ old('status', $pengajuanCuti->status ?? '') == 'ditolak' ? 'selected' : '' }}>
            Ditolak
        </option>
    </select>
    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
@endrole
