<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Trait UploadAble
 * @package App\Traits
 */
trait UploadAble
{
    public function upload_file(UploadedFile $file, $folder = null,  $file_name = null, $disk = 'public')
    {
        if (!Storage::directories($disk.'/'.$folder)) {
            Storage::makeDirectory($disk.'/'.$folder,0777, true); //if directory not exist then make the directory
        }

        $filenameWithExt = $file->getClientOriginalName(); // Get filename with extension like index.jpg
        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME); // Get just filename  like index
        $extension       = $file->getClientOriginalExtension(); // Get just extension like .jpg

        $fileNameToStore = !is_null($file_name) ? str_replace(' ', '-', $file_name).'.'.$extension : str_replace(' ', '-', $filename).'-'.rand(111111,999999).'.'.$extension; //Filename to store  like index1545gfh5465.jpg
        $file->storeAs($folder,$fileNameToStore,$disk); //store file in targetted folder
        return $fileNameToStore;
    }

    public function delete_file($filename,$folder,$disk = 'public')
    {
        if(Storage::exists($disk.'/'.$folder.$filename))
        {
            Storage::disk($disk)->delete($folder.$filename);
            return TRUE;
        }
        return false;
    }

    public function upload_base64_image($image_64,$folder) {
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

        $replace = substr($image_64, 0, strpos($image_64, ',')+1);

        // find substring fro replace here eg: data:image/png;base64,

        $image = str_replace($replace, '', $image_64);

        $image = str_replace(' ', '+', $image);

        $imageName = Str::random(10).'.'.$extension;

        if (app()->environment('production')) {
            $destinationPath = '/home/demoffxy/emed/storage/app/public/' . $folder . $imageName;


            if (file_put_contents($destinationPath, base64_decode($image))) {

            } else {
                \Log::info("Failed to save file.");
            }

        }else{
            Storage::disk('public')->put($folder.$imageName, base64_decode($image));
        }

        return $imageName;

    }

    public function uploadCompressImage(UploadedFile $file, $folder = null, $fileName = null, $disk = 'public')
    {
        // Check if the directory exists, if not, create it
        if (!Storage::directories($disk . '/' . $folder)) {
            Storage::makeDirectory($disk . '/' . $folder, 0777, true);
        }

        $filenameWithExt = $file->getClientOriginalName();
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        $extension = $file->getClientOriginalExtension();

        // Generate a unique filename
        $fileNameToStore = !is_null($fileName) ? str_replace(' ', '-', $fileName) . '.' . $extension : str_replace(' ', '-', $filename) . '-' . rand(111111, 999999) . '.' . $extension;

        // Compress image if it exceeds 25KB
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            $compressedImage = Image::make($file);
            if ($compressedImage->filesize() > 25000) { // Check if image size exceeds 25KB
                $compressedImage->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            $compressedImage = $compressedImage->encode('jpg', 75); // Encode as JPEG with 75% quality
            $compressedImageStream = $compressedImage->stream()->detach();
            Storage::disk($disk)->put($folder . '/' . $fileNameToStore, $compressedImageStream);
        } else {
            // For non-image files, store without compression
            $file->storeAs($folder, $fileNameToStore, $disk);
        }

        return $fileNameToStore;
    }
}
