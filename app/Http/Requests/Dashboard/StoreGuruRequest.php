<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuruRequest extends FormRequest
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
    public function getLulusan()
    {
        $this->lulusan;
    }
    public function getDesc()
    {
        $this->description;
    }
    public function getFoto()
    {
        $this->foto;
    }
    public function getKaryawan_id()
    {
        $this->karyawan_id;
    }
    public function getSlug()
    {
        $this->slug;
    }
    public function getPelajarans()
    {
        $this->pelajarans;
    }

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
