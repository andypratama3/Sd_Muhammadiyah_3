<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuruRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function getName()
    {
        $this->name;
    }
    public function getLulusan()
    {
        $this->lulusan;
    }
    public function getDesc()
    {
        $this->description;
    }
    public function getFoto()
    {
        $this->foto;
    }
    public function getKaryawan_id()
    {
        $this->karyawan_id;
    }
    public function getSlug()
    {
        $this->slug;
    }
    public function getPelajarans()
    {
        $this->pelajarans;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'pelajarans' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Kolom Nama tidak boleh kosong!',
            'foto.required' => 'Kolom File Foto tidak boleh kosong!',
            'foto.image' => 'Kolom File Foto harus berupa gambar!',
            'foto.mimes' => 'Kolom File Foto harus berformat jpg, jpeg, atau png!',
            'foto.max' => 'Ukuran File Foto maksimal 2MB!',
            'pelajarans.required' => 'Kolom Pelajaran tidak boleh kosong!',
        ];
    }
}
