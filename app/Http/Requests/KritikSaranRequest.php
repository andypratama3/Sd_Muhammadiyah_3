<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KritikSaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }
    public function getName()
    {
        $this->name;
    }
    public function getSubject()
    {
        $this->subejct;
    }
    public function getEmail()
    {
        $this->email;
    }
    public function getMessage()
    {
        $this->message;
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
