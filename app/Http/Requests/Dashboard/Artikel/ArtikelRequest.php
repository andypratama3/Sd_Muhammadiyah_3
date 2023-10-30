<?php

namespace App\Http\Requests\Dashboard\Artikel;

use Illuminate\Foundation\Http\FormRequest;

class ArtikelRequest extends FormRequest
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
        return $this->name;
    }
    public function getCategorys()
    {
        return $this->categorys;
    }
    public function getArtikel()
    {
        return $this->artikel;
    }
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
