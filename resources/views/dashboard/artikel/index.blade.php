@extends('layouts.dashboard')
@section('title','Artikel')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Data Artikel</h5>
                    <a href="{{ route('dashboard.news.artikel.create') }}" class="btn btn-primary float-end">Tambah</a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection
