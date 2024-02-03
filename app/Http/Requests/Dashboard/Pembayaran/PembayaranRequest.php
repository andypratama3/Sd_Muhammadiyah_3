<?php

namespace App\Http\Requests\Dashboard\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;

class PembayaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getSiswa()
    {
        $this->siswa_id;
    }
    public function getKelas()
    {
        $this->kelas;
    }
    public function getCategoryKelas()
    {
        $this->category_kelas;
    }

    public function getGrossAmount()
    {
        $this->gross_amount;
    }
    public function getSlug()
    {
        $this->slug;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
