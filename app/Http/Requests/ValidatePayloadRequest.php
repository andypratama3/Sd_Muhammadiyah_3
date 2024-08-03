<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePayloadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Adjust as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Define your validation rules
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    protected function prepareForValidation()
    {
        $blockedPayloads = [
            'bad_payload',
            'another_bad_payload',
        ];

        foreach ($blockedPayloads as $payload) {
            if (in_array($payload, $this->all())) {
                abort(400, 'Payload blocked');
            }
        }
    }
}
