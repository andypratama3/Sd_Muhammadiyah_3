@extends('layouts.dashboard')
@section('title','Kelas')
@section('content')
<div class="col-lg-12 mb-4">
    <!-- Simple Tables -->
    <div class="card">
        @include('layouts.flashmessage')
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="m-0 font-weight-bold text-center">Data Kelas</h5>
                <a href="{{ route('dashboard.datasekolah.kelas.create') }}" class="btn btn-success btn-sm float-right">Tambah <i class="fas fa-plus"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush text-center">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Kelas</th>
                        <th>Kategori Kelas</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kelass as $kelas)
                    <tr>
                        <td>{{ ++$no }}</td>
                        <td>{{ $kelas->name }}</td>
                        @php
                            $categoryKelas = json_decode($kelas->category_kelas, true);
                            sort($categoryKelas);
                        @endphp
                        <td>{{ implode(', ', $categoryKelas) }}</td>
                        <td>
                            <a href="{{ route('dashboard.datasekolah.kelas.show', $kelas->slug) }}" class="btn btn-dark btn-sm"><i
                                    class="fas fa-info-circle"></i></a>
                            <a href="{{ route('dashboard.datasekolah.kelas.edit', $kelas->slug) }}" class="btn btn-primary btn-sm"><i
                                    class="fa fa-pen"></i></a>
                            <a href="#" data-id="{{ $kelas->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                <form action="{{ route('dashboard.datasekolah.kelas.destroy', $kelas->slug) }}"
                                    id="delete-{{ $kelas->slug }}" method="POST" enctype="multipart/form-data">
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
@push('user_js')
<script>

</script>
@endpush
@endsection
