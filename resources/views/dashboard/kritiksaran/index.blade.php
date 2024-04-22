@extends('layouts.dashboard')
@section('title','Kritik Saran')
@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4c class="m-0 font-weight-bold text-primary text-center">Kritik Saran</h4c>

            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Subject</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kritiksarans as $kritik)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $kritik->name }}</td>
                            <td>{{ $kritik->subject }}</td>
                            <td>
                                <a href="{{ route('dashboard.kritik.saran.show', $kritik->slug) }}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="#" data-id="{{ $kritik->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                    <form action="{{ route('dashboard.kritik.saran.destroy', $kritik->slug) }}"
                                        id="delete-{{ $kritik->slug }}" method="POST" enctype="multipart/form-data">
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
            <div class="card-footer clearfix">
                <ul class="pagination m-0 float-right">
                    {{ $kritiksarans->onEachSide(1)->links() }}
                </ul>
            </div>
            <div class="card-footer"></div>
        </div>
    </div>
</div>

@endsection
