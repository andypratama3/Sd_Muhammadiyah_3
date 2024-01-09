<?php

namespace App\Http\Requests\Dashboard\Jadwal;

use Illuminate\Foundation\Http\FormRequest;

class Jadwal extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getTahun_Ajaran()
    {
        return $this->tahun_ajaran;
    }
    public function getJadwal()
    {
        return $this->jadwal;
    }
    public function getKelas()
    {
        return $this->kelas;
    }
    public function getCategoryKelas()
    {
        return $this->category_kelas;
    }
    public function getSlug()
    {
        return $this->slug;
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
