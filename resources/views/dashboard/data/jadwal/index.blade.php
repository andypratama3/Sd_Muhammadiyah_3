@extends('layouts.dashboard')
@section('title','Jadwal')
@section('content')

<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Data Jadwal</h5>
                    <a href="{{ route('dashboard.datamaster.jadwal.create') }}" class="btn btn-primary float-end">Tambah</a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Smester</th>
                            <th>Jadwal</th>
                            <th>Kelas</th>
                            <th>Kategori Kelas</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwals as $jadwal)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td> {{ $jadwal->smester }} </td>
                            <td><a href="{{ asset('storage/file/jadwal/'. $jadwal->jadwal) }}" target="_blank">Lihat Jadwal</a></td>
                            <td> {{ $jadwal->kelas_jadwal->name }} </td>
                            <td> {{ $jadwal->category_kelas }} </td>
                            <td>
                                <a href="{{ route('dashboard.datamaster.jadwal.show', $jadwal->slug) }}" class="btn btn-dark btn-sm"><i
                                        class="fas fa-info-circle"></i></a>
                                <a href="{{ route('dashboard.datamaster.jadwal.edit', $jadwal->slug) }}" class="btn btn-primary btn-sm"><i
                                        class="fa fa-pen"></i></a>
                                <a href="#" data-id="{{ $jadwal->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                    <form action="{{ route('dashboard.datamaster.jadwal.destroy', $jadwal->slug) }}"
                                        id="delete-{{ $jadwal->slug }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('delete')
                                    </form>
                                    <i class="fas fa-trash"></i>
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
