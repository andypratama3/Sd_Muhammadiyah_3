@extends('layouts.user')

@section('title','Cara Melakukan Pembayaran')

@push('meta_user')
    <meta name="description" content="Panduan lengkap cara melakukan pembayaran di Sekolah Kreatif Muhammadiyah 3 Samarinda, Kalimantan Timur.">
    <meta name="keywords" content="Pembayaran Sekolah, Sekolah Kreatif Muhammadiyah 3, SD Muhammadiyah Samarinda, Kalimantan Timur, Panduan Pembayaran Sekolah">
    <meta name="author" content="Sekolah Kreatif Muhammadiyah 3 Samarinda">
    <meta name="copyright" content="Sekolah Kreatif Muhammadiyah 3 Samarinda">
    <meta name="viewport" content="width=device-width, initial-scale=1">
@endpush

@push('css_user')
@endpush

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <a href="{{ route('index') }}" class="btn btn-primary"
                style="color: #ffffff; background-color: #5ce70b !important; border-color: #5ce70b !important">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>

            <div class="mt-3 border-0 shadow-sm card">
                <div class="text-white card-header bg-primary">
                    <h4 class="mb-0">Panduan Pembayaran Sekolah Kreatif SD Muhammadiyah 3 Samarinda</h4>
                </div>
                <div class="card-body">
                    <p>Berikut adalah langkah-langkah pembayaran di <strong>Sekolah Kreatif Muhammadiyah 3 Samarinda</strong> untuk mempermudah proses administrasi:</p>

                    <ol class="ps-3">
                        <li>Masukkan <strong>NISN</strong> siswa pada form yang tersedia di halaman pembayaran.</li>
                        <li>Pilih <strong>tahun</strong> ajaran yang ingin dibayarkan.</li>
                        <li>Pilih <strong>jenis tagihan</strong> (misalnya SPP, DPP, Seragam, dll).</li>
                        <li>
                            Pilih tagihan yang akan dibayar, lalu pilih metode pembayaran:
                            <ul>
                                <li>Tekan tombol <strong>Bayar</strong> untuk melanjutkan.</li>
                                <li>Pembayaran via <strong>Virtual Account (VA)</strong> bank.</li>
                                <li>Untuk pembayaran SPP, silakan scan QR-code yang tersedia dengan mengklik "Lihat Kode QR".</li>
                                <li>Pembayaran <strong>online</strong> dapat dilakukan melalui Midtrans (e-Wallet, QRIS, dll).</li>
                            </ul>
                        </li>
                        <li>Jika memilih Virtual Account, salin nomor VA yang muncul di halaman pembayaran.</li>
                        <li>Pada aplikasi mobile banking atau ATM, pilih <strong>BRIVA</strong> sebagai metode VA.</li>
                        <li>Setelah pembayaran berhasil, sistem akan <strong>otomatis mengonfirmasi</strong> transaksi Anda.</li>
                        <li>Pastikan status tagihan berubah menjadi <strong>Lunas</strong> di halaman riwayat pembayaran.</li>
                        <li>Jika terjadi kendala atau status belum berubah, Tunggu Beberapa Saat, Atau silakan unggah bukti pembayaran secara manual atau hubungi admin keuangan.</li>
                    </ol>

                    <p class="mt-4">Terima kasih atas partisipasinya. Pembayaran tepat waktu sangat mendukung kelancaran proses belajar mengajar di sekolah.</p>

                    <p><strong>Kontak Bantuan:</strong><br>
                        Admin Keuangan: <a href="https://wa.me/6282225249993" target="_blank">082225249993</a><br>
                        {{-- Email: <a href="mailto:keuangan@sekolahkreatif.sch.id">keuangan@sekolahkreatif.sch.id</a> --}}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
