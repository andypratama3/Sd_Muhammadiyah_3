<?php

namespace App\Http\Requests\Dashboard\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class FasilitasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function getNama_fasilitas(): string
    {
        return $this->nama_fasilitas;
    }

    public function getDesc(): string
    {
        return $this->desc;
    }

    public function getFoto(): array
    {
        return $this->foto;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_fasilitas' => 'required',
            'desc' => 'required',
            'foto' => 'required',
        ];
    }
}
