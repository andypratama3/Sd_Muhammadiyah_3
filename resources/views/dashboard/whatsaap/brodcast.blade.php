@extends('layouts.dashboard')

@section('title','Whatsaap Create')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Buat Pengumuman</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.monitoring.whatsapp.store') }}" method="POST">
                        @csrf
                        <div class="mb-2 form-group">
                            <label class="form-label" for="kelas">Kelas</label>
                            <select name="kelas_id" id="" class="form-control">
                                <option value="" selected>Pilih Kelas</option>
                                @foreach($kelas as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2 form-group">
                            <label class="form-label" for="kelas">Kategori Pembayaran</label>
                            <select name="kategori_pembayaran" class="form-control" id="kategori_pembayaran">
                                <option value="" selected>Pilih Kelas</option>
                                @foreach($kategoriPembayaran as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->name }}</option>
                                @endforeach
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-2 form-group d-none" id="kategori_lainnya">
                            <div class="form-group row">
                                <label class="form-label" for="kelas">Nama Pembayaran</label>
                                <input type="text" class="form-control" name="kategori_lainnya">
                            </div>
                            <div class="form-group">
                                <label for="" class="form-group">Nominal</label>
                                <input type="text">
                            </div>
                        </div>


                        <a href="{{ route('whatsapp.webhook') }}" class="btn btn-danger btn-sm">Kembali</a>
                        <button type="submit" class="btn btn-primary btn-sm float-end">Kirim Pengumuman</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        $(document).ready(function () {
            $('#kategori_pembayaran').on('change', function () {
                if ($(this).val() == 'lainnya') {
                    $('#kategori_lainnya').removeClass('d-none');
                } else {
                    $('#kategori_lainnya').addClass('d-none');
                }
            });
        });
    </script>
@endpush

@endsection
