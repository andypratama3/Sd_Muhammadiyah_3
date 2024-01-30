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

    public function getName()
    {
        $this->name;
    }
    public function getGrossAmount()
    {
        $this->gross_amount;
    }
    public function getKelas()
    {
        $this->kelas;
    }
    public function getCategoryKelas()
    {
        $this->category_kelas;
    }
    public function getOrderId()
    {
        $this->name;
    }
    public function getName()
    {
        $this->name;
    }
    public function getName()
    {
        $this->name;
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
