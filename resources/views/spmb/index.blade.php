<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Formulir PPDB - SD Kreatif</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/2feee0b69e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('ppdb_asset/css/style.css') }}">
</head>

<body>
    <div class="container py-5">
        <div class="form-container">
            <img src="{{ asset('ppdb_asset/img/SD3_logo1.png') }}" alt="" class="logo-header img-fluid img-bordered">
            <h1>🎓 Formulir Pendaftaran Siswa Baru</h1>
            <h4>SD Kreatif Muhammadiyah 3 Samarinda<br>Tahun Ajaran 2025/2026</h4>

            <!-- Progress Bar -->
            <ul class="progressbar" id="progressbar">
                <li class="active">Informasi</li>
                <li>Data Siswa</li>
                <li>Orang Tua / Wali</li>
                <li>Lampiran</li>
                <li>Pembayaran</li>
            </ul>

            <form id="ppdbForm">
                <!-- step information -->
                <div class="step active">
                    <div class="section-title">📝 Informasi Pendaftaran</div>
                    <div class="row mx-2">
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quae?</p>
                        <ul style="list-style: decimal">
                            <li>Lorem ipsum dolor sit amet consectetur adipisicing elit.</li>
                            <li>Lorem ipsum dolor sit amet consectetur adipisicing elit.</li>
                            <li>Lorem ipsum dolor sit amet consectetur adipisicing elit.</li>
                            <li>Lorem ipsum dolor sit amet consectetur adipisicing elit.</li>
                        </ul>
                        <div class="col-md-12 mb-3">
                            <button type="button" onclick="nextStep()" class="btn btn-primary float-end">Setuju & Lanjut
                                <i class="fa fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
                <!-- Step 1 -->
                <div class="step">
                    <div class="section-title">🧒 Data Calon Siswa</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap Anak</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select">
                                <option value="">-- Pilih --</option>
                                <option>Laki-laki</option>
                                <option>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Agama</label>
                            <input type="text" class="form-control" value="Islam" readonly />
                        </div>
                        <div class="col-md-6 col-sm-6 mb-3">
                            <label class="form-label">Warga Negara / Suku</label>
                            <input type="text" class="form-control" value="" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="section-title">🏫 Asal Sekolah</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Sekolah Asal</label>
                            <input type="text" class="form-control" />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor / Tanggal STTB</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Alamat Sekolah Asal</label>
                            <input type="text" class="form-control" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i
                                class="fa fa-arrow-left"></i> Kembali</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut <i
                                class="fa fa-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step">
                    <div class="section-title">👨‍👩‍👧 Data Orang Tua / Wali</div>
                    <select name="selected_data" id="selected_data" class="form-control mb-2 form-select" required>
                        <option value="" selected disabled>Pilih Data</option>
                        <option value="orang_tua">Orang Tua</option>
                        <option value="wali">Wali</option>
                    </select>
                    <div class="row d-none" id="ortu">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ayah</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan Ayah</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir Ayah</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alamat</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ibu</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan Ibu</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir Ibu</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alamat</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP Orang Tua</label>
                            <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" />
                            <p style="font-size: 12px; margin-left: 5px; font-weight: bold; color: red;">
                                <code>Nomor WhastApp Aktif *</code>
                            </p>
                        </div>
                    </div>
                    <div class="row d-none" id="wali">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Wali</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan Wali</label>
                            <input type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP Wali</label>
                            <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" />
                            <p style="font-size: 12px; margin-left: 5px; font-weight: bold; color: red;">
                                <code>Nomor WhastApp Aktif *</code>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i
                                class="fa fa-arrow-left"></i> Kembali</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut <i
                                class="fa fa-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="step ">
                    <div class="section-title">📎 Lampiran Dokumen</div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">Upload Foto Copy STTB TK (Bagi yang TK)</label>
                            <input type="file" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Akta Kelahiran</label>
                            <input type="file" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Kartu Keluarga</label>
                            <input type="file" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pass Foto 3x4</label>
                            <input type="file" class="form-control" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i
                                class="fa fa-arrow-left"></i> Kembali</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut <i
                                class="fa fa-arrow-right"></i></button>
                    </div>
                </div>
                <div class="step">
                    <div class="section-title">🧾 Pembayaran</div>
                    <div class="row">
                        <div class="col-md-12 col-sm-6 mb-3">
                            <label class="form-label">Pembayaran</label>
                            <input type="text" class="form-control" value="Rp. 300.000" readonly />
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i
                                class="fa fa-arrow-left"></i> Kembali</button>
                        <button type="button" class="btn btn-primary">Selesai</button>
                    </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('ppdb_asset/js/index.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>
        function previewImage($file) {
            var file = $('#my_img_field')[0].files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = $('#');
                img.attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }

        // $(document).ready(function () {

        // });
    </script>
</body>

</html>
