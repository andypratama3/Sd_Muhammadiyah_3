@extends('layouts.user')

@section('title', 'Update Nomor HP Orang Tua')

@push('css_user')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
/* Hilangkan panah pada input number untuk browser WebKit (Chrome, Safari, Edge) */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Hilangkan spinner pada Firefox */
input[type=number] {
    -moz-appearance: textfield;
}
</style>

@endpush

@section('content')
<div class="mt-4 mb-4 row justify-content-center">
    <div class="col-md-6">
        <div class="shadow-sm card">
            <div class="text-white card-header bg-primary">
                Update Nomor WhatsApp Orang Tua
            </div>
            <div class="card-body">
                <form id="form-orangtua" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="nisn" id="nisn_hidden">

                    <div class="mb-3">
                        <label for="siswa" class="form-label">Pilih Siswa <code>*</code></label>
                        <select id="siswa" class="select2 form-control @error('siswa') is-invalid @enderror" data-placeholder="Pilih Siswa">
                            <option></option>
                            @foreach ($siswas as $siswa)
                                <option value="{{ $siswa->nisn }}">{{ $siswa->name }} | {{ $siswa->nisn }} | {{ $siswa->kelas->pluck('name')->join(', ') }}</option>
                            @endforeach
                        </select>
                        <div id="siswa-error" class="invalid-feedback d-none"></div>
                    </div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Nomor HP Orang Tua</label>
                        <input type="text" name="no_hp" id="no_hp"
                                class="form-control @error('no_hp') is-invalid @enderror"
                                placeholder="Nomor HP akan tampil di sini">
                        <div id="nohp-error" class="invalid-feedback d-none"></div>
                    </div>

                    <div class="text-end">
                        <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Simpan</button>
                    </div>
                </form>

                <div class="mt-4 card-footer">
                    <div class="mt-2 row">
                        <p>Jika Mengalami Kesulitan <br>
                            <a href="https://wa.me/+6282217160075" target="_blank" class="mt-2 text-white btn btn-primary">
                                <i class="fa-brands fa-whatsapp"></i> Hubungi Admin
                            </a>
                        </p>
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


            if (!nisn) {
                $('#submitBtn').prop('disabled', true);
                $('#no_hp').val('');
                $('#nohp-error').addClass('d-none').text('');
                $('#siswa-error').addClass('d-none').text('');
                return;
            }

            $.ajax({
                type: "GET",
                url: `{{ url('/pengisian-formulir/whatsaap/') }}/${nisn}`,
                success: function (response) {
                    if (response.status === 'success') {
                        $('#no_hp').val(response.data.no_hp || '');
                        $('#submitBtn').prop('disabled', false);
                        $('#form-orangtua').attr('action', `{{ url('/pengisian-formulir/whatsaap/update') }}/${nisn}`);
                    } else {
                        $('#siswa').addClass('is-invalid');
                        $('#siswa-error').removeClass('d-none').text('Data siswa tidak ditemukan.');
                        $('#submitBtn').prop('disabled', true);
                    }
                },
                error: function () {
                    $('#siswa').addClass('is-invalid');
                    $('#siswa-error').removeClass('d-none').text('Gagal mengambil data siswa.');
                }
            });
        });

        $('#form-orangtua').on('submit', function (e) {
            let noHp = $('#no_hp').val().trim();
            let regexHp = /^08[0-9]{9,11}$/;

            if (!regexHp.test(noHp)) {
                e.preventDefault();
                $('#no_hp').addClass('is-invalid');
                $('#nohp-error').removeClass('d-none').text('Nomor HP harus diawali 08 dan 11-13 digit.');
                return false;
            }
        });

        $('#form-orangtua').on('submit', function (e) {
            if ($('#submitBtn').is(':disabled')) {
                e.preventDefault();
                $('#no_hp').addClass('is-invalid');
                $('#nohp-error').removeClass('d-none').text('Silakan pilih siswa terlebih dahulu.');
            }
        });
    });
</script>
@endpush
