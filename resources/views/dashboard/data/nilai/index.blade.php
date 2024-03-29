@extends('layouts.dashboard')
@section('title','Nilai')
@section('content')

<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Mata Pelajaran</h5>
                    <a href="{{ route('dashboard.datasekolah.kelas.create') }}" class="btn btn-success float-right">Tambah <i class="fas fa-plus"></i></a>
            </div>
            <div class="table-responsive">
                @if ($guru && $guru->pelajarans->count() > 0)
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Mata Pelajaran</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guru->pelajarans as $pelajaran)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $pelajaran->name }}</td>
                            <td><a href="{{ route('nilai.matapelajaran', $pelajaran->name) }}" class="btn btn-primary"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                    @else
                        <div class="container text-center">
                            <h2>Tidak Ada Pelajaran</h2>
                        </div>
                    @endif
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
