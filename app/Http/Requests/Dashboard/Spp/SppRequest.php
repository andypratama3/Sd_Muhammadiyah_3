<?php

namespace App\Http\Requests\Dashboard\Spp;

use Illuminate\Foundation\Http\FormRequest;

class SppRequest extends FormRequest
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
        $this->name;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
