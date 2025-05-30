<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Formulir PPDB - SD Kreatif</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/2feee0b69e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('ppdb_asset/css/style.css') }}">
    <style>
        code {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="form-container">
            <img src="{{ asset('ppdb_asset/img/SD3_logo1.png') }}" alt="" class="logo-header img-fluid img-bordered">
            <h1>🎓 Formulir Pendaftaran Siswa Baru</h1>
            <h4>Sekolah Kreatif SD Muhammadiyah 3 Samarinda<br>Tahun Ajaran 2025/2026</h4>

            <!-- Progress Bar -->
            <ul class="progressbar" id="progressbar">
                <li class="active">Informasi</li>
                <li>Data Siswa</li>
                <li>Orang Tua / Wali</li>
                <li>Lampiran</li>
                <li>Pembayaran</li>
            </ul>

            <form action="{{ route('spmb.store') }}" id="form-spmb" method="POST" enctype="multipart/form-data">
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
                                <li>Setelah melakukan pendaftaran dan pembayaran, data akan dikonfirmasi melalui website resmi Sekolah Kreatif SD Muhammadiyah 3 Samarinda.</li>
                                <li>Jika data sudah terkonfirmasi, proses akan dilanjutkan ke tahap berikutnya, termasuk pengisian data lanjutan.</li>
                                <li>Jika terjadi kendala atau status belum berubah, Tunggu Beberapa Saat, Atau silakan unggah bukti pembayaran secara manual atau hubungi admin PPDB.</li>
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
                    <h6 class="text-end m-3">Nomor Pendaftaran: {{ sprintf('%03d', $nomor_urut + 1) }}</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="hidden" name="order_id" value="">
                            <label for="nama" class="form-label">Nama Lengkap Anak <code>*</code></label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" id="nama" value="{{ old('nama') }}" />
                            @error('nama')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir <code>*</code></label>
                            <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir') }}" />
                            @error('tempat_lahir')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir <code>*</code></label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir') }}" />
                            @error('tanggal_lahir')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin <code>*</code></label>
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
                            <input type="text" class="form-control @error('agama') is-invalid @enderror" id="agama" name="agama" value="{{ old('agama') }}" />
                            @error('agama')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 col-sm-6 mb-3">
                            <label for="suku" class="form-label">Warga Negara / Suku</label>
                            <input type="text" class="form-control @error('suku') is-invalid @enderror" id="suku" name="suku" value="{{ old('suku') }}" />
                            @error('suku')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" id="alamat" rows="2">{{ old('alamat') }}</textarea>
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
                            <input type="text" class="form-control @error('nama_asal_sekolah') is-invalid @enderror" name="nama_asal_sekolah" id="nama_asal_sekolah" value="{{ old('nama_asal_sekolah') }}" />
                            @error('nama_asal_sekolah')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sttb" class="form-label">Nomor / Tanggal STTB</label>
                            <input type="text" class="form-control @error('sttb') is-invalid @enderror" name="sttb" id="sttb" value="{{ old('sttb') }}" />
                            @error('sttb')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="alamat_sekolah" class="form-label">Alamat Sekolah Asal</label>
                            <input type="text" class="form-control @error('alamat_sekolah') is-invalid @enderror" name="alamat_sekolah" id="alamat_sekolah" value="{{ old('alamat_sekolah') }}" />
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
                    <select name="select_data" id="select_data" class="form-control mb-2 form-select" required>
                        <option value="" {{ old('select_data') == '' ? 'selected' : '' }} disabled>Pilih Data</option>
                        <option value="orang_tua" {{ old('select_data') == 'orang_tua' ? 'selected' : '' }}>Orang Tua</option>
                        <option value="wali" {{ old('select_data') == 'wali' ? 'selected' : '' }}>Wali</option>
                    </select>
                    <div class="row d-none" id="ortu">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ayah <code>*</code></label>
                            <input type="text" class="form-control @error('nama_ayah') is-invalid @enderror" name="nama_ayah" value="{{ old('nama_ayah') }}" />
                            @error('nama_ayah')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan Ayah <code>*</code></label>
                            <input type="text" class="form-control @error('pekerjaan_ayah') is-invalid @enderror" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah') }}" />
                            @error('pekerjaan_ayah')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir Ayah <code>*</code></label>
                            <input type="text" class="form-control @error('pendidikan_ayah') is-invalid @enderror" name="pendidikan_ayah" value="{{ old('pendidikan_ayah') }}" />
                            @error('pendidikan_ayah')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alamat <code>*</code></label>
                            <input type="text" class="form-control @error('alamat_ayah') is-invalid @enderror" name="alamat_ayah" value="{{ old('alamat_ayah') }}" />
                            @error('alamat_ayah')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ibu <code>*</code></label>
                            <input type="text" class="form-control @error('nama_ibu') is-invalid @enderror" name="nama_ibu" value="{{ old('nama_ibu') }}" />
                            @error('nama_ibu')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan Ibu <code>*</code></label>
                            <input type="text" class="form-control @error('pekerjaan_ibu') is-invalid @enderror" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu') }}" />
                            @error('pekerjaan_ibu')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir Ibu <code>*</code></label>
                            <input type="text" class="form-control @error('pendidikan_ibu') is-invalid @enderror" name="pendidikan_ibu" value="{{ old('pendidikan_ibu') }}" />
                            @error('pendidikan_ibu')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alamat <code>*</code></label>
                            <input type="text" class="form-control @error('alamat_ibu') is-invalid @enderror" name="alamat_ibu" value="{{ old('alamat_ibu') }}" />
                            @error('alamat_ibu')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP Orang Tua <code>*</code></label>
                            <input type="tel" class="form-control @error('phone_orang_tua') is-invalid @enderror" name="phone_orang_tua" value="{{ old('phone_orang_tua') }}" placeholder="08xxxxxxxxxx" />
                            @error('phone_orang_tua')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p style="font-size: 12px; margin-left: 5px; font-weight: bold; color: red;">
                                <code>Nomor WhastApp Aktif *</code>
                            </p>
                        </div>
                    </div>
                    <div class="row d-none" id="wali">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Wali <code>*</code></label>
                            <input type="text" class="form-control @error('nama_wali') is-invalid @enderror" name="nama_wali" value="{{ old('nama_wali') }}" />
                            @error('nama_wali')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan Wali <code>*</code></label>
                            <input type="text" class="form-control @error('pekerjaan_wali') is-invalid @enderror" name="pekerjaan_wali" value="{{ old('pekerjaan_wali') }}" />
                            @error('pekerjaan_wali')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP Wali <code>*</code></label>
                            <input type="tel" class="form-control @error('phone_wali') is-invalid @enderror" name="phone_wali" value="{{ old('phone_wali') }}" placeholder="08xxxxxxxxxx" />
                            @error('phone_wali')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
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
                <div class="step">
                    <div class="section-title">📎 Lampiran Dokumen</div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label class="form-label">Upload Foto Copy STTB TK (Bagi yang TK)</label>
                            <input type="file" class="form-control @error('sttb_tk') is-invalid @enderror" name="sttb_tk" />
                            @error('sttb_tk')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Akta Kelahiran <code>*</code></label>
                            <input type="file" class="form-control @error('akta_kelahiran') is-invalid @enderror" name="akta_kelahiran" required />
                            @error('akta_kelahiran')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Kartu Keluarga <code>*</code></label>
                            <input type="file" class="form-control @error('kk') is-invalid @enderror" name="kk" required />
                            @error('kk')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pass Foto 3x4 <code>*</code></label>
                            <input type="file" class="form-control @error('pas_foto') is-invalid @enderror" name="pas_foto" required />
                            @error('pas_foto')
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
                <div class="step">
                    <div class="section-title">🧾 Pembayaran</div>
                    <div class="row">
                        <div class="col-md-12 col-sm-6 mb-3">
                            <label class="form-label">Pembayaran</label>
                            <input type="text" class="form-control" value="Rp. 300.000" readonly />
                        </div>
                        <div class="col-md-12 col-sm-6">
                            <ul>
                                <li>Silakan melakukan pembayaran sebesar <b>Rp. 300.000</b> melalui metode pembayaran yang tersedia.</li>
                                <li>Pastikan setelah melakukan pembayaran dengan Virtual Account, tekan tombol "Cek Status" agar formulir pendaftaran terkonfirmasi dan tersimpan.</li>
                                <li>Pembayaran hanya dapat dilakukan satu kali untuk setiap siswa.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i
                                class="fa fa-arrow-left"></i> Kembali</button>
                        <button type="button" class="btn btn-primary button_pay"><i class="fa fa-check"></i> Bayar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

    <script src="{{ asset('ppdb_asset/js/index.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


   <script>
    $(document).ready(function () {
        let currentOrderId = localStorage.getItem('currentOrderId') || null;

        $('.step').on('click', '.button_pay', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "GET",
                url: "{{ route('spmb.pay') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    nama: $('#nama').val(),
                },
                success: function (response) {
                    currentOrderId = response.order_id;

                    // Simpan order_id ke localStorage
                    localStorage.setItem('currentOrderId', currentOrderId);

                    if (response.status === 'already_paid') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sudah Dibayar',
                            text: 'Transaksi ini telah dibayar sebelumnya.',
                        }).then(() => {
                            // Inject order_id dan status ke form jika belum ada
                            if ($('#order_id').length) {
                                $('#order_id').val(currentOrderId);
                            } else {
                                $('#form-spmb').append('<input type="hidden" id="order_id" name="order_id" value="' + currentOrderId + '">');
                            }

                            if ($('#payment_status').length) {
                                $('#payment_status').val(response.transaction_status);
                            } else {
                                $('#form-spmb').append('<input type="hidden" id="payment_status" name="payment_status" value="' + response.transaction_status + '">');
                            }

                            SubmitType = true;
                            $('#form-spmb')[0].submit();
                        });

                        return;
                    }

                    let snapToken = response.snap_token;

                    snap.pay(snapToken, {
                        onSuccess: function (result) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Pembayaran Berhasil',
                            }).then(() => {
                                if ($('#order_id').length) {
                                    $('#order_id').val(currentOrderId);
                                } else {
                                    $('#form-spmb').append('<input type="hidden" id="order_id" name="order_id" value="' + currentOrderId + '">');
                                }

                                if ($('#payment_status').length) {
                                    $('#payment_status').val(result.transaction_status);
                                } else {
                                    $('#form-spmb').append('<input type="hidden" id="payment_status" name="payment_status" value="' + result.transaction_status + '">');
                                }

                                SubmitType = true;
                                $('#form-spmb')[0].submit();
                            });
                        },
                        onPending: function (result) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Pembayaran Sedang Dalam Proses',
                                text: 'Silakan lakukan pembayaran pada menu pembayaran.',
                            });
                        },
                        onError: function (result) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Pembayaran Gagal. Silakan coba lagi.',
                            });
                        }
                    });
                }
            });
        });

        // Isi order_id dari localStorage jika ada (optional)
        if (currentOrderId) {
            if ($('#order_id').length) {
                $('#order_id').val(currentOrderId);
            } else {
                $('#form-spmb').append('<input type="hidden" id="order_id" name="order_id" value="' + currentOrderId + '">');
            }
        }
    });
</script>

</body>

</html>
