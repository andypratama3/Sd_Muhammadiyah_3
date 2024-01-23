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

    public function getName()
    {
        $this->name;
    }
    public function getSex()
    {
        $this->sex;
    }
    public function getPhone()
    {
        $this->phone;
    }
    public function getSlug()
    {
        $this->slug;
    }


    //get role_id
    public function getRole()
    {
        $this->role_id;
    }
    //get permissions

    public function rules(): array
    {
        return [

        ];
    }
    public function message()
    {
        return [

        ];
    }
}
