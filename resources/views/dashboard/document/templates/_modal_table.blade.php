{{-- resources/views/dashboard/document/templates/_modal_table.blade.php --}}
<div class="modal fade" id="modalTable" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title fs-6">
                    <i class="bi bi-table text-success me-2"></i>Sisipkan Tabel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="alert alert-light border py-2 mb-3 small">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    <strong>Referensi lebar:</strong>
                    Canvas A4 = <code>794px</code> &nbsp;|&nbsp;
                    Margin 20mm × 2 = <code>152px</code> &nbsp;|&nbsp;
                    <strong>Konten bersih = <code>642px</code></strong>
                </div>

                <ul class="nav nav-tabs mb-3 small" id="tableTabs">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabCustom">Kustom</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabKelasMapel">Kelas &amp; Mapel</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabRaport">Raport</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabProgramUnggulan">Unggulan</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabEkskul">Ekskul</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAbsensi">Absensi</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabTTD">Area TTD</a></li>
                </ul>

                <div class="tab-content">

                    {{-- TABEL KUSTOM --}}
                    <div class="tab-pane fade show active" id="tabCustom">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Jumlah Baris</label>
                                <input type="number" id="tableRows" class="form-control form-control-sm" value="5" min="1" max="50">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Jumlah Kolom</label>
                                <input type="number" id="tableCols" class="form-control form-control-sm" value="4" min="1" max="12">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Lebar Tabel (px)</label>
                                <input type="number" id="tableWidth" class="form-control form-control-sm" value="642" min="200" max="794">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Tinggi Baris (px)</label>
                                <input type="number" id="tableRowHeight" class="form-control form-control-sm" value="24" min="14" max="80">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Warna Header</label>
                                <input type="color" id="tableHeaderColor" class="form-control form-control-color form-control-sm" value="#1a5276">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Warna Baris Genap</label>
                                <input type="color" id="tableStripeColor" class="form-control form-control-color form-control-sm" value="#eaf2ff">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Warna Border</label>
                                <input type="color" id="tableBorderColor" class="form-control form-control-color form-control-sm" value="#adb5bd">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Header Kolom <small class="text-muted fw-normal">(pisahkan koma)</small></label>
                                <input type="text" id="tableHeaders" class="form-control form-control-sm"
                                    placeholder="No, Nama Siswa, Nilai, Keterangan">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tableHasNo" checked>
                                    <label class="form-check-label small">Otomatis isi nomor urut di kolom pertama</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KELAS & MAPEL --}}
                    <div class="tab-pane fade" id="tabKelasMapel">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>Tabel dinamis dari database kelas dan mapel.
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Tipe Tabel</label>
                                <select id="kelasMapelType" class="form-select form-select-sm">
                                    <option value="daftar_kelas">Daftar Kelas</option>
                                    <option value="daftar_mapel">Daftar Mata Pelajaran</option>
                                    <option value="jadwal_kelas">Jadwal Per Kelas</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Pilih Kelas (opsional)</label>
                                <select id="kelasMapelKelas" class="form-select form-select-sm">
                                    <option value="">— Semua Kelas —</option>
                                    @foreach($kelasList ?? [] as $kls)
                                        <option value="{{ $kls->id }}">{{ $kls->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Lebar (px)</label>
                                <input type="number" id="kelasMapelWidth" class="form-control form-control-sm" value="642">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Tinggi Baris (px)</label>
                                <input type="number" id="kelasMapelRowH" class="form-control form-control-sm" value="24">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Warna Header</label>
                                <input type="color" id="kelasMapelHeaderColor" class="form-control form-control-color form-control-sm" value="#1a5276">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Kolom Tambahan <small class="text-muted fw-normal">(pisahkan koma)</small></label>
                                <input type="text" id="kelasMapelKolom" class="form-control form-control-sm" value="Keterangan">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="kelasMapelAutoVar" checked>
                                    <label class="form-check-label small">Auto-generate variabel untuk setiap baris</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RAPORT --}}
                    <div class="tab-pane fade" id="tabRaport">
                        <div class="row g-2">
                            <div class="col-lg-7">
                                <div class="d-flex gap-2 mb-2 align-items-end">
                                    <div class="flex-fill">
                                        <label class="form-label fw-semibold small mb-1">Pilih Tingkat Kelas</label>
                                        <select id="raportTingkatKelas" class="form-select form-select-sm">
                                            <option value="">— Pilih Kelas —</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnLoadMapel" disabled>
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <a href="/dashboard/kurikulum-mapel" target="_blank"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                </div>
                                <div id="raportMapelPreview">
                                    <div class="text-center text-muted py-4 small border rounded bg-light">
                                        <i class="bi bi-table fs-3 d-block mb-2"></i>
                                        Pilih kelas untuk melihat daftar mapel
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <label class="form-label fw-semibold small">Lebar (px)</label>
                                <input type="number" id="raportWidth" class="form-control form-control-sm mb-2" value="642">
                                <label class="form-label fw-semibold small">Warna Header</label>
                                <input type="color" id="raportHeaderColor" class="form-control form-control-color form-control-sm mb-2" value="#1a5276">
                                <label class="form-label fw-semibold small">Tinggi Baris Data (px)</label>
                                <input type="number" id="raportRowHeight" class="form-control form-control-sm mb-2" value="40" min="20" max="100">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="raportAutoVar" checked>
                                    <label class="form-check-label small">
                                        Auto-generate variabel <code>@{{nilai_xxx}}</code>, <code>@{{capaian_xxx}}</code>
                                    </label>
                                </div>
                                <div class="small fw-semibold text-muted mb-1">Kelompok aktif:</div>
                                <div id="raportKelompokToggle" class="d-flex flex-wrap gap-1"></div>
                                <div class="alert alert-warning py-2 small mt-2 mb-0"
                                     id="raportNoKelasAlert" style="display:none">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Pilih kelas terlebih dahulu.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PROGRAM UNGGULAN --}}
                    <div class="tab-pane fade" id="tabProgramUnggulan">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nama Program</label>
                                <input type="text" id="unggulanNama" class="form-control form-control-sm mb-2" value="TAHFIZ">
                                <label class="form-label fw-semibold small">Item Program <small class="text-muted fw-normal">(satu per baris)</small></label>
                                <textarea id="unggulanItems" class="form-control font-monospace" rows="8"
                                    style="font-size:.82rem">a. Al-Fatihah
b. Al-Fajr
c. Al-Ghasyiyah
d. Al-A'Ala
e. Al-Thoriq
f. Al-Buruj
g. Al-Insyiqaq
h. Al Mutafifin</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kolom Penilaian <small class="text-muted fw-normal">(pisahkan koma)</small></label>
                                <input type="text" id="unggulanKolom" class="form-control form-control-sm mb-2" value="Predikat,Keterangan">
                                <label class="form-label fw-semibold small">Lebar (px)</label>
                                <input type="number" id="unggulanWidth" class="form-control form-control-sm mb-2" value="642">
                                <label class="form-label fw-semibold small">Warna Header</label>
                                <input type="color" id="unggulanHeaderColor" class="form-control form-control-color form-control-sm mb-2" value="#1a5276">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="unggulanMergeHeader" checked>
                                    <label class="form-check-label small">Tampilkan nama program sebagai header merged</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- EKSKUL --}}
                    <div class="tab-pane fade" id="tabEkskul">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Daftar Ekskul <small class="text-muted fw-normal">(satu per baris)</small></label>
                                <textarea id="ekskulItems" class="form-control font-monospace" rows="10"
                                    style="font-size:.82rem">Tapak Suci
Futsal
Karate
Panahan
Tilawah
Tahfidz
Bahasa Arab
Kaligrafi
English Fun
Sains Club
Math Club
Mewarnai
Tari
Catur
Teater
Dokcil
TIK</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kolom Penilaian <small class="text-muted fw-normal">(pisahkan koma)</small></label>
                                <input type="text" id="ekskulKolom" class="form-control form-control-sm mb-2" value="Predikat,Keterangan">
                                <label class="form-label fw-semibold small">Lebar (px)</label>
                                <input type="number" id="ekskulWidth" class="form-control form-control-sm mb-2" value="642">
                                <label class="form-label fw-semibold small">Warna Header</label>
                                <input type="color" id="ekskulHeaderColor" class="form-control form-control-color form-control-sm mb-2" value="#1a5276">
                            </div>
                        </div>
                    </div>

                    {{-- ABSENSI --}}
                    <div class="tab-pane fade" id="tabAbsensi">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Jumlah Siswa (baris)</label>
                                <input type="number" id="absensiRows" class="form-control form-control-sm" value="30" min="5" max="50">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Lebar (px)</label>
                                <input type="number" id="absensiWidth" class="form-control form-control-sm" value="642">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Warna Header</label>
                                <input type="color" id="absensiHeaderColor" class="form-control form-control-color form-control-sm" value="#1a5276">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Kolom Kehadiran <small class="text-muted fw-normal">(pisahkan koma)</small></label>
                                <input type="text" id="absensiKolom" class="form-control form-control-sm" value="S,I,A,Keterangan">
                            </div>
                        </div>
                    </div>

                    {{-- AREA TTD --}}
                    <div class="tab-pane fade" id="tabTTD">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>Area tanda tangan disisipkan sebagai blok kolom.
                        </div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    Kolom TTD
                                    <small class="text-muted fw-normal">(format: Label,Nama,Jabatan — satu per baris)</small>
                                </label>
                                <textarea id="ttdKolom" class="form-control font-monospace" rows="4"
                                    style="font-size:.82rem">Orang Tua,@{{nama_ortu}},
Wali Kelas,@{{wali_kelas}},NBM : @{{nbm_wali}}
Kepala Sekolah,@{{kepala_sekolah}},NBM : @{{nip}}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Lebar Area (px)</label>
                                <input type="number" id="ttdWidth" class="form-control form-control-sm" value="642">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Tinggi Ruang TTD (px)</label>
                                <input type="number" id="ttdHeight" class="form-control form-control-sm" value="80">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Posisi Y di canvas (px)</label>
                                <input type="number" id="ttdPosY" class="form-control form-control-sm" value="950">
                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-content --}}
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" id="btnInsertTable">
                    <i class="bi bi-table me-1"></i>Sisipkan ke Canvas
                </button>
            </div>
        </div>
    </div>
</div>