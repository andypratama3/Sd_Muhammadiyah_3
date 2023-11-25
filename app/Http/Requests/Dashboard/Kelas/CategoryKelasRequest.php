<?php

namespace App\Http\Requests\Dashboard\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class CategoryKelasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getKelas()
    {
        $this->kelas;
    }
    public function getCategoryKelas()
    {
        $this->categorykelas;
    }
    public function getSlug()
    {
        $this->kelas;
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
