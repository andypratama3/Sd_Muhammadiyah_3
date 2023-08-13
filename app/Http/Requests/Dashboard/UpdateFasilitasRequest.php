<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFasilitasRequest extends FormRequest
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
            'foto' => 'required|unique:fasilitas,foto,,id',
        ];
    }
    public function message()
    {
        return [
            'required' => ':attribute tidak boleh kosong!',
            'unique' => ':attribute tidak boleh sama!',
        ];
    }
}
