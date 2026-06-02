<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadHelper
{
    public static function uploadFile(UploadedFile $file, string $path, string $disk = 's3')
    {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($path, $filename, $disk);
        return $filename;
    }

    public static function deleteFile(string $filePath, string $disk = 's3')
    {
        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }

    public static function getFileUrl(string $filePath, string $disk = 's3'): string
    {
        if (!Storage::disk($disk)->exists($filePath)) {
            return false;
        }

        return Storage::disk($disk)->temporaryUrl(
            $filePath,
            Carbon::now()->addMinutes(5)
        );
    }
}
