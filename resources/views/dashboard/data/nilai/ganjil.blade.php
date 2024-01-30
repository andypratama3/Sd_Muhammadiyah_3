@extends('layouts.dashboard')
@section('title','Nilai')
@section('content')

<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Kelas {{ $kelas_name }} Smester Ganjil</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush table-bordered text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Daftar Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($siswas as $sis)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $sis->name }}</td>
                            <td>
                                <table data-id="<?= $sis->id ?>">
                                    <tr style="display: block; width: 100%;">
                                        <td class="text-center justify-between d-flex">
                                            <div class="col-2">
                                                <div class="form-group row d-block text-center mx-4">
                                                    <label for="">Nilai</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group row d-block text-center mx-4">
                                                    <label for="">Nilai</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group row d-block text-center mx-4">
                                                    <label for="">Nilai</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group row d-block text-center mx-4">
                                                    <label for="">Nilai</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group row d-block text-center mx-4">
                                                    <label for="">Nilai</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="form-group d-flex text-center mt-4">
                                                 <a href="" class="btn btn-primary">Simpan</a>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('user_js')
<script>

</script>
@endpush
@endsection
