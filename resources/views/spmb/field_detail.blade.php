<div class="col-md-6">
    {!! Form::label('nama', 'Nama', ['class' => 'form-label']) !!}
    {!! Form::text('nama', null, ['class' => 'form-control', 'id' => 'nama' , 'placeholder' => 'Nama Siswa']) !!}
    @error('nama')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
<div class="col-md-6">
    {!! Form::label('nama', 'Nama', ['class' => 'form-label']) !!}
    {!! Form::text('nama', null, ['class' => 'form-control','id' => 'nama' , 'placeholder' => 'Nama Siswa']) !!}
</div>

<div class="col-md-12">
    <button class="btn btn-primary" type="submit">Simpan</button>
</div>
