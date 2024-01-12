<?php
namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FasilitasAction
{
    public function execute($FasilitasData)
    {
        if ($FasilitasData->foto) {
            $fotorFiles = $FasilitasData->foto;
            $fasilitas_name = [];

            foreach ($fotorFiles as $fotorFile) {
                $ext = $fotorFile->getClientOriginalExtension();
                $uniqueIdentifier = Str::random(8); 
                $file_name = 'Fasilitas_' . Str::slug($FasilitasData->nama_fasilitas) . '_' . $uniqueIdentifier . '_' . date('YmdHis') . ".$ext";
                $upload_path = public_path('storage/img/fasilitas/');
                $fotorFile->move($upload_path, $file_name);
                $fasilitas_name[] = $file_name;
            }
        }


        $fasilitas = Fasilitas::updateOrCreate(
            ['slug' => $FasilitasData->slug],
            [
                'nama_fasilitas' => $FasilitasData->nama_fasilitas,
                'desc' => $FasilitasData->desc,
                'foto' => implode(',', $fasilitas_name),
            ]
        );
    }
}
