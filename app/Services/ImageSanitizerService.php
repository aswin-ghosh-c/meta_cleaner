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
            return $this->sanitizeJpeg($path);
        }

        if ($mime === 'image/png') {
            return $this->sanitizePng($path);
        }

        throw new RuntimeException('Unsupported image type.');
    }

    /**
     * Sanitize JPEG files by skipping APP1 (EXIF/XMP), APP13 (IPTC), and COM markers.
     */
    private function sanitizeJpeg(string $path): bool
    {
        $data = file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException('Failed to read image file.');
        }

        $len = strlen($data);
        if ($len < 4) {
            throw new RuntimeException('Invalid image file size.');
        }

        // JPEG SOI (Start Of Image) check
        if (substr($data, 0, 2) !== "\xFF\xD8") {
            throw new RuntimeException('Not a valid JPEG image.');
        }

        $output = "\xFF\xD8";
        $i = 2;

        while ($i < $len) {
            // Find next marker
            if ($data[$i] !== "\xFF") {
                // To be safe, copy remainder and exit loop if marker is missing
                $output .= substr($data, $i);
                break;
            }

            // Read marker byte
            if ($i + 1 >= $len) {
                $output .= substr($data, $i);
                break;
            }
            $marker = substr($data, $i, 2);

            if ($marker === "\xFF\xDA") {
                // SOS (Start of Scan) - compressed entropy-coded data follows. Copy and break.
                $output .= substr($data, $i);
                break;
            }

            $i += 2;

            // Markers without length:
            // \xFF\xD0 to \xFF\xD9 (RST0-RST7, EOI)
            // \xFF\x01 (TEM)
            $markerByte = ord($marker[1]);
            if (($markerByte >= 0xD0 && $markerByte <= 0xD9) || $markerByte === 0x01) {
                $output .= $marker;
                continue;
            }

            // Read length (2 bytes)
            if ($i + 2 > $len) {
                $output .= substr($data, $i - 2);
                break;
            }
            $segLen = (ord($data[$i]) << 8) + ord($data[$i + 1]);

            // Metadata markers to strip:
            // APP1 (\xFF\xE1) - EXIF, XMP
            // APP13 (\xFF\xED) - IPTC
            // COM (\xFF\xFE) - Comment
            if ($marker === "\xFF\xE1" || $marker === "\xFF\xED" || $marker === "\xFF\xFE") {
                // Skip the marker segment entirely (including length bytes)
                $i += $segLen;
            } else {
                // Copy segment to clean file
                $output .= $marker . substr($data, $i, $segLen);
                $i += $segLen;
            }
        }

        if (file_put_contents($path, $output) === false) {
            throw new RuntimeException('Failed to write sanitized JPEG file.');
        }

        return true;
    }

    /**
     * Sanitize PNG files by omitting metadata chunks like eXIf, tEXt, zTXt, iTXt, tIME, and dSIG.
     */
    private function sanitizePng(string $path): bool
    {
        $data = file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException('Failed to read image file.');
        }

        $len = strlen($data);
        if ($len < 8) {
            throw new RuntimeException('Invalid image file size.');
        }

        $sig = "\x89PNG\r\n\x1a\n";
        if (substr($data, 0, 8) !== $sig) {
            throw new RuntimeException('Not a valid PNG image.');
        }

        $output = $sig;
        $i = 8;

        while ($i < $len) {
            if ($i + 8 > $len) {
                $output .= substr($data, $i);
                break;
            }

            // Read Chunk Length (4 bytes, network byte order)
            $chunkLenData = substr($data, $i, 4);
            $chunkLen = unpack('N', $chunkLenData)[1];
            
            // Read Chunk Type (4 bytes)
            $chunkType = substr($data, $i + 4, 4);

            $totalChunkSize = 4 + 4 + $chunkLen + 4; // Length + Type + Data + CRC
            if ($i + $totalChunkSize > $len) {
                $output .= substr($data, $i);
                break;
            }

            // Metadata chunks to strip
            $stripChunks = ['eXIf', 'tEXt', 'zTXt', 'iTXt', 'tIME', 'dSIG'];

            if (in_array($chunkType, $stripChunks)) {
                // Skip chunk entirely
                $i += $totalChunkSize;
            } else {
                // Copy chunk to clean file
                $output .= substr($data, $i, $totalChunkSize);
                $i += $totalChunkSize;
            }

            if ($chunkType === 'IEND') {
                break;
            }
        }

        if (file_put_contents($path, $output) === false) {
            throw new RuntimeException('Failed to write sanitized PNG file.');
        }

        return true;
    }
}

