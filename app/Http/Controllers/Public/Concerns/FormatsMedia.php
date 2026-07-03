<?php

namespace App\Http\Controllers\Public\Concerns;

trait FormatsMedia
{
    /**
     * Turn a stored relative path (e.g. "/uploads/hero/x.jpg") into an absolute URL,
     * or pass an already-absolute external URL through unchanged.
     */
    protected function mediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }
}
