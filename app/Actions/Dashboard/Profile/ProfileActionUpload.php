<?php

namespace App\Actions\Dashboard\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileActionUpload
{
    public function execute($request)
    {
        $user = User::find(auth()->id());

        $image = $request->input('image');
        $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
        $image = str_replace(' ', '+', $image);

        $imageName = time() . '.jpg';
        $imageData = base64_decode($image);

        $img = Image::make($imageData);

        $img->resize(560, 560, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $img->encode('jpg', 90);

        if ($user->avatar !== 'default.jpg' && $user->avatar !== '') {
            Storage::delete('public/img/profile/' . $user->avatar);
        }

        Storage::put('public/img/profile/' . $imageName, $img->stream());

        $user->avatar = $imageName;
        $user->save();

        return $user;
    }
}
