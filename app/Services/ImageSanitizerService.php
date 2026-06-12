<?php

namespace App\Services;

use RuntimeException;

class ImageSanitizerService
{
    /**
     * Sanitize the image at the given file path based on its mime type.
     *
     * @throws RuntimeException
     */
    public function sanitize(string $path, string $mime): bool
    {
        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = @imagecreatefromjpeg($path);
            if (! $image) {
                throw new RuntimeException('The file could not be processed as a valid JPEG image.');
            }

            // Re-save file using imagejpeg. This recreates the image pixel buffer and
            // completely omits any EXIF, IPTC, or XMP metadata headers from the input file.
            if (! imagejpeg($image, $path, 100)) {
                throw new RuntimeException('Failed to process and clean JPEG image.');
            }

            return true;
        }

        if ($mime === 'image/png') {
            $image = @imagecreatefrompng($path);
            if (! $image) {
                throw new RuntimeException('The file could not be processed as a valid PNG image.');
            }

            // Preserve PNG transparency settings (disable blending, save alpha)
            imagealphablending($image, false);
            imagesavealpha($image, true);

            // Re-save file using imagepng. This recreates the image pixel buffer and
            // completely discards any metadata blocks (like iTXt, tEXt, zTXt).
            if (! imagepng($image, $path, 9)) {
                throw new RuntimeException('Failed to process and clean PNG image.');
            }

            return true;
        }

        throw new RuntimeException('Unsupported image type.');
    }
}
