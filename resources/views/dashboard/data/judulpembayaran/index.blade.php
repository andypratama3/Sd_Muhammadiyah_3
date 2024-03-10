@extends('layouts.dashboard')
@section('title','Kategori Pembayaran')
@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        @include('layouts.flashmessage')
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-4"> Kategori Pembayaran
                <a href="{{ route('dashboard.datamaster.judul.pembayaran.create') }}" class="btn btn-success btn-sm float-right">Tambah <i class="fas fa-plus"></i></a>
                </h4>
                <div class="table-responsive">
                    <table class="table mt-4 text-center" >
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kategori</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($judul_pembayarans as $judul)
                            <tr>
                                <td>{{ ++$no }}</td>
                                <td>{{ $judul->name }}</td>
                                <td>
                                    <a href="{{ route('dashboard.datamaster.judul.pembayaran.edit', $judul->slug) }}"
                                        class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                    <a href="#" data-id="{{ $judul->slug }}" class="btn btn-danger btn-sm delete"
                                        title="Hapus">
                                        <form action="{{ route('dashboard.datamaster.judul.pembayaran.destroy', $judul->slug) }}"
                                            id="delete-{{ $judul->slug }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('delete')
                                        </form>
                                        <i class="fas fa-trash"></i>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="col-md-12">Total : {{ $count }}</div>
                        <ul class="pagination justify-content-end">
                            <li class="page-item mr-2"> {{ $judul_pembayarans->onEachSide(1)->links() }}</li></li>
                        </ul></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
