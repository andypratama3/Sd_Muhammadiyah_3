@extends('layouts.user')

@section('title', 'Pengisian Orang Tua')

@push('css_user')
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- Select2 Bootstrap 5 Theme --}}
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        /* Smooth animation for Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            transition: all 0.2s ease-in-out;
            min-height: 38px;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 2rem;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 38px;
            top: 0.3rem;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center mt-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Nomor WhatsApp Orang Tua
                </div>
                <div class="card-body">
                    {{-- Flash message --}}
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Validation error --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- <form action="{{ route('parent.phone.store') }}" method="POST"> --}}
                        @csrf
                        <div class="mb-3">
                            <p class="mb-0">Silahkan Pilih Siswa</p>
                            <p class="mb-0">Format: Nama | NISN | Kelas</p>
                            <label for="siswa" class="form-label mt-3">Nama Siswa</label>
                            <select name="siswa" id="siswa" class="select2" data-placeholder="Pilih Siswa">
                                <option></option>
                                @foreach ($siswas as $siswa)
                                    <option value="{{ $siswa->id }}">{{ $siswa->name }} | {{ $siswa->nisn }} | {{ $siswa->kelas->pluck('name')->join(', ') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor HP Orang Tua</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                Simpan
                            </button>
                        </div>
                    {{-- </form> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js_user')
    {{-- jQuery (dibutuhkan oleh Select2) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });
        });
    </script>
@endpush
