<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

trait HandlesImageUploads
{
    /**
     * Move an uploaded file into public/uploads/{folder} and return its public path.
     */
    protected function storeUploadedImage(UploadedFile $file, string $folder): string
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path("uploads/{$folder}");

        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        $file->move($destinationPath, $fileName);

        return "/uploads/{$folder}/{$fileName}";
    }

    /**
     * Delete a previously uploaded local image (no-op for external URLs).
     */
    protected function deleteUploadedImage(?string $path): void
    {
        if ($path && str_starts_with($path, '/uploads/')) {
            $fullPath = public_path($path);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }
    }
}
