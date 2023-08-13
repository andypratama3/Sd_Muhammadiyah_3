<?php

namespace App\Http\Requests\Dashboard\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class KaryawanRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'name' => 'required',
            'sex' => 'required',
            'phone' => 'required',
        ];
    }
    public function message()
    {
        return [
            'required' => 'Attribut Tidak Boleh Kosong!',
        ];
    }
}
