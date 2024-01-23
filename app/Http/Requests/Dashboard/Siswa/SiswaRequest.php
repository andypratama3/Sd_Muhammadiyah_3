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
    public function getJk()
    {
        $this->jk;
    }
    public function getTmpt_lahir()
    {
        $this->tmpt_lahir;
    }
    public function getTgl_lahir()
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
    public function getKelurahan()
    {
        $this->kelurahan;
    }
    public function getKecamatan()
    {
        $this->kecamatan;
    }
    public function getKodepos()
    {
        $this->kodepos;
    }
    public function getJenis_tinggal()
    {
        $this->jenis_tinggal;
    }
    public function getNo_hp()
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
