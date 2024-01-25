<?php

namespace App\Http\Requests\Dashboard\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class SiswaRequest extends FormRequest
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
    public function getNisn()
    {
        $this->nisn;
    }
    public function getJk()
    {
        $this->jk;
    }
    public function getTmptlahir()
    {
        $this->tmpt_lahir;
    }
    public function getTgllahir()
    {
        $this->tgl_lahir;
    }
    public function getNik()
    {
        $this->nik;
    }
    public function getAgama()
    {
        $this->agama;
    }
    public function getRt()
    {
        $this->rt;
    }
    public function getRw()
    {
        $this->rw;
    }
    public function getProvinsi()
    {
        $this->provinsi_id;
    }
    public function getKabupaten()
    {
        $this->kabupaten_id;
    }
    public function getKecamatan()
    {
        $this->kecamatan_id;
    }
    public function getKelurahan()
    {
        $this->kelurahan_id;
    }

    public function getNamaJalan()
    {
        $this->nama_jalan;
    }
    public function getJenistinggal()
    {
        $this->jenis_tinggal;
    }
    public function getNoHp()
    {
        $this->no_hp;
    }
    public function getBeasiswa()
    {
        $this->beasiswa;
    }
    public function getFoto()
    {
        $this->foto;
    }
    public function getSlug()
    {
        $this->slug;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
