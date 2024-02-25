@extends('layouts.dashboard')
@section('title', 'Detail Siswa')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detail Siswa</h6>
    </div>
    <hr>
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Siswa</h6>
    </div>
    <div class="card-body">
            <div class="row">
                <div class="col-md-6 justify-center d-block text-center mb-5">
                    <p>Foto</p>
                    <img src="{{ asset('storage/img/siswa/'. $siswa->foto)  }}" alt="" class="h-40" style="width: 50%;">
                </div>
                <div class="col-md-6">
                    <div class="form-group row mt-5">
                        <label class="col-sm-3 text-dark" for="name">Nama</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->name }}</h6>
                            <hr>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-sm-3 text-dark" for="name">Nisn</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->nisn }}</h6>
                            <hr>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-sm-3 text-dark" for="name">Jenis Kelamin</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->jk }}</h6>
                            <hr>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-sm-3 text-dark" for="name">Kelas</label>
                        <div class="col-sm-9">
                            @foreach ($siswa->kelas as $kelas)
                            <h6>: {{ $kelas->name }}</h6>
                            @endforeach
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tmpt_lahir">Tempat Lahir</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->tmpt_lahir }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tgl_lahir">Tanggal Lahir</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->tgl_lahir }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="agama">Agama</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->agama }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="beasiswa">Beasiswa</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->beasiswa }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelas">Kelas</label>
                        <div class="col-sm-9">
                            @foreach ($siswa->kelas as $kelas)
                            <h6>: {{ $kelas->name }}</h6>
                            <hr>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="category_kelas">Kategori Kelas</label>
                        <div class="col-sm-9">
                            @foreach ($siswa->kelas as $kelas)
                            <h6>: {{ $kelas->pivot->category_kelas }}</h6>
                            <hr>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- Data Detai lSiswa --}}
                <div class="col-md-12">
                    <hr>
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
                        <h6 class="m-0 font-weight-bold text-primary">Alamat Tinggal</h6>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="rt">RT</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->rt }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="rw">RW</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->rw }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="provinsi">Provinsi</label>
                        <div class="col-sm-9">
                            <input type="hidden" id="provinsi_id" value="{{ $siswa->provinsi_id }}">
                            <h6 id="provinsi">: {{ $siswa->provinsi_id }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kabupaten">Kabupaten / Kota</label>
                        <div class="col-sm-9">
                            <input type="hidden" id="kabupaten_id" value="{{ $siswa->kabupaten_id }}">
                            <h6 id="kabupaten">: {{ $siswa->kabupaten_id }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kecamatan">Kecamatan</label>
                        <div class="col-sm-9">
                            <input type="hidden" id="kecamatan_id" value="{{ $siswa->kecamatan_id }}">
                            <h6 id="kecamatan">: {{ $siswa->kecamatan_id }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelurahan">Kelurahan</label>
                        <div class="col-sm-9">
                            <input type="hidden" id="kalurahan_id" value="{{ $siswa->kalurahan_id }}">
                            <h6 id="kelurahan">: {{ $siswa->kelurahan_id }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Jenis Tinggal</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->jenis_tinggal }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="nama_jalan">Nama Jalan</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->nama_jalan }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="hp">HP Orang Tua</label>
                        <div class="col-sm-9">
                            <h6>: {{ $siswa->no_hp }}</h6>
                            <hr>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                        <a href="{{ route('dashboard.datamaster.siswa.index') }}"
                            class="btn btn-danger float-lg-start mr-2">Kembali</a>
                        <a href="{{ route('siswa.cetakData', $siswa->slug) }}" class="btn btn-primary float-lg-right">Cetak Data</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@push('js')
<script>

async function getProvinsi() {
    provinsi_id = $('#provinsi_id').val();
    kabupaten_id = $('#kabupaten_id').val();
    kecamatan_id = $('#kecamatan_id').val();
    kelurahan_id = $('#kelurahan_id').val();
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        type: "POST",
        url: "{{ route('getprovinsi.api') }}",
        data: {
            provinsi_id: provinsi_id,
            kabupaten_id: kabupaten_id,
            kecamatan_id: kecamatan_id,
            kelurahan_id: kelurahan_id,

        },
        dataType: "json",
        success: function (response) {
            provinsi = response.provinsi.name;
            kabupaten = response.kabupaten.name;
            kecamatan = response.kecamatan.name;
            kelurahan = response.kelurahan.name;
            $('#provinsi').text(": " + provinsi);
            $('#kabupaten').text(": " + kabupaten);
            $('#kecamatan').text(": " + kecamatan);
            $('#kelurahan').text(": " + kelurahan);

        }
    });
    }
// function getKabupaten() {
//     provinsi_id = $('#provinsi_id').val();
//     kabupaten_id = $('kabupaten_id').val();
//     $.ajaxSetup({
//             headers: {
//                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//             }
//         });

//     $.ajax({
//         type: "POST",
//         url: "{{ route('getkabupaten.api') }}",
//         data: {
//             provinsi_id: provinsi_id,
//             kabupaten_id : kabupaten_id,
//         },
//         dataType: "json",
//         success: function (response) {
//             kabupaten = response.response.name;
//             $('#kabupaten').text(": " + kabupaten);
//         }
//     });
// }
    getProvinsi();
//  getKabupaten();

</script>
@endpush
@endsection
