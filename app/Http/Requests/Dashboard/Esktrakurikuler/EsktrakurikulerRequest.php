<?php

namespace App\Http\Requests\Dashboard\Esktrakurikuler;

use Illuminate\Foundation\Http\FormRequest;

class EsktrakurikulerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function getName(){
        $this->name;
    }
    public function getDesc(){
        $this->desc;
    }
    public function getFoto(){
        $this->foto;
    }
    public function getSlug(){
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
