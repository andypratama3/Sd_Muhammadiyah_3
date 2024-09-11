<?php

namespace App\Http\Requests\Dashboard\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class SiswaRequest extends FormRequest
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
    public function getJk(){
        $this->jk;
    }
    public function getTmptlahir(){
        $this->tmpt_lahir;
    }
    public function getTgllahir(){
        $this->tgl_lahir;
    }
    public function getNisn(){
        $this->nisn;
    }
    public function getAgama(){
        $this->agama;
    }
    //pendidikan sebelumnya
    public function getNamaPendidikan()
    {
        $this->nama_pendidikan;
    }
    public function getJalanPendidikan()
    {
        $this->nama_jalan_pendidikan;
    }
    public function getKelasTahun(){
        $this->kelas_tahun;
    }
    public function getTanggalMasuk(){
        $this->tanggal_masuk;
    }
    public function getBeasiswa(){
        $this->beasiswa;
    }
    public function getFoto(){
        $this->foto;
    }
    //request data orang tua
    public function getNamaAyah(){
        $this->nama_ayah;
    }
    public function getNamaIbu(){
        $this->nama_ibu;
    }
    public function getPendidikanAyah(){
        $this->pendidikan_ayah;
    }
    public function getPendidikanIbu(){
        $this->pendidikan_ibu;
    }
    public function getPekerjaanAyah(){
        $this->pekerjaan_ayah;
    }
    public function getPekerjaanIbu(){
        $this->pekerjaan_ibu;

    }
    public function getNamaWali(){
        $this->nama_wali;
    }
    public function getPekerjaanWali(){
        $this->pekerjaan_wali;

    }
    public function getAlamatWali(){
        $this->alamat_wali;

    }

    public function getRt()
    {
        $this->rt;
    }
    public function getRw()
    {
        $this->rw;
    }
    public function getProvinsi()
    {
        $this->provinsi_id;
    }
    public function getKabupaten()
    {
        $this->kabupaten_id;
    }
    public function getKecamatan()
    {
        $this->kecamatan_id;
    }
    public function getKelurahan()
    {
        $this->kelurahan_id;
    }

    public function getNamaJalan()
    {
        $this->nama_jalan;
    }
    public function getJenistinggal()
    {
        $this->jenis_tinggal;
    }
    public function getNoHp()
    {
        $this->no_hp;
    }

    public function getKelas()
    {
        $this->kelas;
    }
    public function getCategoryKelas()
    {
        $this->category_kelas;
    }
    public function getSlug()
    {
        $this->slug;
    }

    public function getSpp()
    {
        $this->spp;
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
