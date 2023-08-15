<?php

namespace App\Http\Requests\Dashboard\Berita;

use Illuminate\Foundation\Http\FormRequest;

class BeritaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getJudul()
    {
        return $this->judul;
    }

    public function getDesc()
    {
        return $this->desc;
    }

    public function getFoto()
    {
        return $this->foto;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'judul' => 'required',
            'desc' => 'required',
            'foto' => 'mimes:jpg,jpeg,png',

        ];
    }

}
