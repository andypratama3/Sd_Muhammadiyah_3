<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class CooperationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function getName()
    {
        return $this->name;
    }

    public function getFoto()
    {
        return $this->foto;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
