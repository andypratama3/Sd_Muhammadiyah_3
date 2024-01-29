@extends('layouts.dashboard')
@section('title','Nilai')
@section('content')

<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Pelajaran {{ $pelajaran->name }}</h5>
                    <a href="{{ route('dashboard.datasekolah.kelas.create') }}" class="btn btn-success float-right">Tambah <i class="fas fa-plus"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Mata Pelajaran</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kelass as $kelas)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>Kelas {{ $kelas->name }}</td>
                            <td>
                                <a href="{{ route('nilai.matapelajaran.siswa.genap') }}" class="btn btn-primary">Smester Genap</a>
                                <a href="" class="btn btn-warning">Smester Ganjil</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>

</script>
@endpush
@endsection
