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
        $this->kelas_id;
    }
    public function getCategoryKelas()
    {
        $this->category_kelas;
    }

    public function getGrossAmount()
    {
        $this->gross_amount;
    }
    public function getOrderID()
    {
        $this->order_id;
    }
    public function getJudul()
    {
        $this->judul_id;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
