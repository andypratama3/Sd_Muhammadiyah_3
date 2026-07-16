<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuruRequest extends FormRequest
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'foto.image' => 'Kolom File Foto harus berupa gambar!',
            'foto.mimes' => 'Kolom File Foto harus berformat jpg, jpeg, atau png!',
            'foto.max' => 'Ukuran File Foto maksimal 2MB!',
        ];
    }
}
