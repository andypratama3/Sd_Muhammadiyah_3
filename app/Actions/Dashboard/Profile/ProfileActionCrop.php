<?php
namespace App\Actions\Dashboard\Profile;


use Storage;
use App\Models\User;
use Illuminate\Support\Str;
use Image;



class ProfileActionCrop
{
    public function execute($request)
    {
        $user = User::find(auth()->id());

        $avatar = $request->image;
        $avatar_parts = explode(';base64,', $avatar);
        $avatar_type_aux = explode('image/', $avatar_parts[0]);
        $avatar_type = $avatar_type_aux[1];
        $avatar_base64 = base64_decode($avatar_parts[1]);

        // Upload original profile picture
        $upload_path_original = public_path('storage/img/profile/');
        if (!file_exists($upload_path_original)) {
            mkdir($upload_path_original, 0777, true); // Create directory if it doesn't exist
        }
        $avatar_name = 'profile_'.Str::slug($user->name).'_'.date('YmdHis').".$avatar_type";
        file_put_contents($upload_path_original.$avatar_name, $avatar_base64);

        // Resize profile picture to 35px x 35px and upload resized image
        $upload_path_35x35 = public_path('storage/img/profile/35x35/');
        if (!file_exists($upload_path_35x35)) {
            mkdir($upload_path_35x35, 0777, true); // Create directory if it doesn't exist
        }
        $avatar_35x35 = Image::make($upload_path_original.$avatar_name)->fit(35, 35)->save($upload_path_35x35.$avatar_name);

        // Resize profile picture to 150px x 150px and upload resized image
        $upload_path_150x150 = public_path('storage/img/profile/150x150/');
        if (!file_exists($upload_path_150x150)) {
            mkdir($upload_path_150x150, 0777, true); // Create directory if it doesn't exist
        }
        $avatar_150x150 = Image::make($upload_path_original.$avatar_name)->fit(150, 150)->save($upload_path_150x150.$avatar_name);

        // Save file name to database
        $user->avatar = $avatar_name;
        $user->save();
    }

}
