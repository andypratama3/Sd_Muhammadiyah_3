{{-- resources/views/dashboard/document/templates/_modal_kop.blade.php --}}
<div class="modal fade" id="modalKop" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title fs-6">
                    <i class="bi bi-bank text-primary me-2"></i>Buat Kop Surat
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Logo Sekolah</label>
                        <input type="file" id="kopLogoFile" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text">Kosongkan → placeholder <code>@{{logo}}</code></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Ukuran Logo (px)</label>
                        <input type="number" id="kopLogoSize" class="form-control form-control-sm" value="90" min="40" max="200">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Nama Yayasan / Majelis</label>
                        <input type="text" id="kopLine1" class="form-control form-control-sm"
                            value="MAJELIS DIKDASMEN MUHAMMADIYAH SAMARINDA">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Nama Sekolah (Besar)</label>
                        <input type="text" id="kopLine2" class="form-control form-control-sm" value="Sekolah Kreatif">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Sub-nama / Jenjang</label>
                        <input type="text" id="kopLine3" class="form-control form-control-sm"
                            value="SD MUHAMMADIYAH 3 SAMARINDA SEBERANG">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <input type="text" id="kopLine4" class="form-control form-control-sm"
                            value="Jalan Dato Iba Telp. (0541) 260066 Kel. Sungai Keledang - Samarinda Seberang 75131">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email / Website</label>
                        <input type="text" id="kopLine5" class="form-control form-control-sm"
                            value="E-mail : sdmuhammadiyahtiga@ymail.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">NPSN / Akreditasi</label>
                        <input type="text" id="kopLine6" class="form-control form-control-sm" value="NPSN : 30404112">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Garis Bawah Kop</label>
                        <select id="kopBorderStyle" class="form-select form-select-sm">
                            <option value="double">Garis Double (tebal + tipis)</option>
                            <option value="single">Garis Single</option>
                            <option value="none">Tanpa Garis</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnAddKop">
                    <i class="bi bi-plus-circle me-1"></i>Tambahkan ke Canvas
                </button>
            </div>
        </div>
    </div>
</div>