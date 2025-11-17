<?php

namespace App\Helpers;

class ImageUrlToBase64
{
    public static function imageUrlToBase64(string $imageUrl): string
    {
        // Jika URL, gunakan file_get_contents
        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageContents = file_get_contents($imageUrl);
        } 
        // Jika path lokal, gunakan public_path
        else {
            $fullPath = public_path($imageUrl);
            if (!file_exists($fullPath)) {
                throw new \RuntimeException("File not found: {$fullPath}");
            }
            $imageContents = file_get_contents($fullPath);
        }

        if ($imageContents === false) {
            throw new \RuntimeException("Failed to fetch the image from: {$imageUrl}");
        }

        $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $imageContents);
        $base64 = base64_encode($imageContents);

        return "data:{$mimeType};base64,{$base64}";
    }
}