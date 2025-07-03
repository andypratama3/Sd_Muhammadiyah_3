@extends('layouts.user')

@section('title', 'Update Nomor HP Orang Tua')

@push('css_user')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="row justify-content-center mt-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Verifikasi & Update Nomor WhatsApp Orang Tua
            </div>
            <div class="card-body">
                <form id="form-orangtua" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nisn" id="nisn_hidden">

                    <div class="mb-3">
                        <label for="siswa" class="form-label">Pilih Siswa <code>*</code></label>
                        <select id="siswa" class="select2" data-placeholder="Pilih Siswa">
                            <option></option>
                            @foreach ($siswas as $siswa)
                            <option value="{{ $siswa->nisn }}">{{ $siswa->name }} | {{ $siswa->nisn }} | {{ $siswa->kelas->pluck('name')->join(', ') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="konfirmasiOrtuWrapper" style="display:none;">
                        <label for="konfirmasi_ortu" class="form-label">Tulis Nama Ayah / Ibu / Wali <code>*</code></label>
                        <input type="text" class="form-control" id="konfirmasi_ortu" placeholder="Contoh: Ahmad">
                        <div class="form-text text-danger" id="konfirmasi_msg" style="display: none;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Nomor HP Orang Tua</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control" placeholder="Akan muncul setelah verifikasi">
                    </div>

                    <div class="text-end">
                        <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Simpan</button>
                    </div>
                </form>


                <div class="card-footer mt-4">
                    <div class="row mt-2">
                       <p>Jika Mengalami Kesulitan <br> <a href="https://wa.me/+6282217160075" target="_blank" class="text-white btn btn-primary mt-2"><i class="fa-brands fa-whatsapp"></i> Hubungi Admin </a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_user')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: $(this).data('placeholder'),
            allowClear: true
        });

        $('#siswa').on('change', function () {
            let nisn = $(this).val();

            $('#nisn_hidden').val(nisn);
            $('#no_hp').val('');
            $('#konfirmasi_ortu').val('');
            $('#konfirmasi_msg').hide().text('');
            $('#konfirmasiOrtuWrapper').show();
            $('#submitBtn').prop('disabled', true);
            $('#form-orangtua').attr('action', `{{ route('pengisian.update', ['nisn' => ':nisn']) }}`.replace(':nisn', nisn));
        });

        $('#konfirmasi_ortu').on('keyup', function () {
            let nisn = $('#nisn_hidden').val();
            let nama_konfirmasi = $(this).val().trim();

            if (!nama_konfirmasi) return;

            $.ajax({
                type: "POST",
                url: "{{ route('pengisian.verifikasi') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    nisn: nisn,
                    nama_konfirmasi: nama_konfirmasi
                },
                success: function (res) {

                    if(res.status == 'error') {
                        $('#no_hp').val('');
                        $('#submitBtn').prop('disabled', true);
                        let msg = res.message || 'Nama tidak cocok dengan data siswa.';
                        $('#konfirmasi_msg').text(msg).show();
                        return;
                    }

                    $('#no_hp').val(res.no_hp);
                    $('#konfirmasi_msg').hide();
                    $('#submitBtn').prop('disabled', false);
                },
            });
        });

        $('#form-orangtua').on('submit', function (e) {
            if ($('#submitBtn').is(':disabled')) {
                e.preventDefault();
                alert('Verifikasi belum berhasil. Silakan periksa kembali nama orang tua.');
            }
        });
    });
</script>
@endpush
