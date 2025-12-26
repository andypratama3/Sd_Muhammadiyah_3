<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class PrestasiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return tru;
    }

    public function getName()
    {
        $this->name;
    }
    public function getDesc()
    {
        $this->description;
    }
    public function getFoto()
    {
        $this->foto;
    }
    public function getStatus()
    {
        $this->status;
    }
    public function getSlug()
    {
        $this->slug;
    }

    public function getTingkat()
    {
        $this->tingkat;
    }
    public function getPenyelenggara()
    {
        $this->penyelenggara;
    }
    public function getTanggal()
    {
        $this->tanggal;
    }
    public function getJuara()
    {
        $this->juara;
    }

    public function getPrestasiKategori()
    {
        $this->prestasi_kategori;
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
