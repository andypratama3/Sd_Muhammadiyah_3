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

            <form action="{{ route('spmb.store') }}" method="POST" id="ppdbForm">
                @csrf
                <!-- step information -->
                <div class="step active">
                    <div class="section-title">📝 Informasi Pendaftaran</div>
                    <div class="row mx-2">
                         <p>Penerimaan Peserta Didik Baru (PPDB) SD Muhammadiyah 3 Samarinda dilakukan secara **online dan offline** melalui website resmi SD Muhammadiyah 3 Samarinda.</p>
                            <ul style="list-style: decimal">
                                <li>Pendaftaran wajib dilakukan melalui website resmi SD Muhammadiyah 3 Samarinda dengan mengisi data secara benar dan lengkap.</li>
                                <li>Data yang diisi terdiri dari:
                                    <ul>
                                        <li>Data Calon Siswa</li>
                                        <li>Data Orang Tua/Wali</li>
                                        <li>Data Lampiran</li>
                                    </ul>
                                </li>
                                <li>Pengisian data memerlukan pembayaran sebesar <strong>Rp300.000</strong>, yang mencakup:
                                    <ul>
                                        <li>Biaya Analisis Psikologi</li>
                                        <li>Biaya Administrasi</li>
                                    </ul>
                                </li>
                                <li>Jika pengisian data dilakukan tanpa pembayaran, maka data yang telah diisi tidak akan tersimpan.</li>
                                <li>Setelah melakukan pendaftaran dan pembayaran, data akan dikonfirmasi melalui website resmi SD Muhammadiyah 3 Samarinda.</li>
                                <li>Jika data sudah terkonfirmasi, proses akan dilanjutkan ke tahap berikutnya, termasuk pengisian data lanjutan.</li>
                                <li>Jika terjadi kendala atau status belum berubah, Tunggu Beberapa Saat, Atau silakan unggah bukti pembayaran secara manual atau hubungi admin keuangan.</li>
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
                            <label for="nama" class="form-label">Nama Lengkap Anak</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" value="{{ old('nama') }}" />
                            @error('nama')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" id="tempat_lahir" value="{{ old('tempat_lahir') }}" />
                            @error('tempat_lahir')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" value="{{ old('tanggal_lahir') }}" />
                            @error('tanggal_lahir')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="agama" class="form-label">Agama</label>
                            <input type="text" class="form-control @error('agama') is-invalid @enderror" id="agama" value="{{ old('agama') }}" readonly />
                            @error('agama')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 col-sm-6 mb-3">
                            <label for="warga_negara" class="form-label">Warga Negara / Suku</label>
                            <input type="text" class="form-control @error('warga_negara') is-invalid @enderror" id="warga_negara" value="{{ old('warga_negara') }}" />
                            @error('warga_negara')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" rows="2">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="section-title">🏫 Asal Sekolah</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nama_asal_sekolah" class="form-label">Nama Asal Sekolah</label>
                            <input type="text" class="form-control @error('nama_asal_sekolah') is-invalid @enderror" id="nama_asal_sekolah" value="{{ old('nama_asal_sekolah') }}" />
                            @error('nama_asal_sekolah')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sttb" class="form-label">Nomor / Tanggal STTB</label>
                            <input type="text" class="form-control @error('sttb') is-invalid @enderror" id="sttb" value="{{ old('sttb') }}" />
                            @error('sttb')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="alamat_sekolah" class="form-label">Alamat Sekolah Asal</label>
                            <input type="text" class="form-control @error('alamat_sekolah') is-invalid @enderror" id="alamat_sekolah" value="{{ old('alamat_sekolah') }}" />
                            @error('alamat_sekolah')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
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
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i>Bayar</button>
                    </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('ppdb_asset/js/index.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

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

        $(document).ready(function () {
            $('.step').on('click', '.button_pay', function () {

            });
        });
    </script>
</body>

</html>
