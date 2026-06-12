<?php

namespace App\Services {
    function file_put_contents($filename, $data, $flags = 0, $context = null) {
        if (isset($GLOBALS['mock_file_put_contents_fail']) && $GLOBALS['mock_file_put_contents_fail'] === true) {
            return false;
        }
        return \file_put_contents($filename, $data, $flags, $context);
    }
}

namespace {
    use App\Services\ImageSanitizerService;

    it('strips injected metadata from JPEG images', function () {
        $service = new ImageSanitizerService;

        // 1. Create a base JPEG
        $img = imagecreatetruecolor(10, 10);
        ob_start();
        imagejpeg($img, null, 100);
        $rawJpeg = ob_get_clean();
        imagedestroy($img);

        // 2. Inject custom metadata segments
        $metadataString = 'CleanMeMetadata';
        $len = strlen($metadataString) + 2;
        $comSegment = "\xFF\xFE".chr($len >> 8).chr($len & 0xFF).$metadataString;
        $app1Segment = "\xFF\xE1".chr(0x00).chr(0x0C)."Exif\x00\x00data";
        $app13Segment = "\xFF\xED".chr(0x00).chr(0x0C)."Photoshop\x00";

        $jpegWithMetadata = substr($rawJpeg, 0, 2) . $comSegment . $app1Segment . $app13Segment . substr($rawJpeg, 2);

        // Save to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempFile, $jpegWithMetadata);

        // Verify metadata is present
        expect(str_contains(file_get_contents($tempFile), $metadataString))->toBeTrue();
        expect(str_contains(file_get_contents($tempFile), 'Photoshop'))->toBeTrue();

        // 3. Run the sanitization logic
        $result = $service->sanitize($tempFile, 'image/jpeg');
        expect($result)->toBeTrue();

        // 4. Assert the metadata is completely gone
        $cleanJpeg = file_get_contents($tempFile);
        expect(str_contains($cleanJpeg, $metadataString))->toBeFalse();
        expect(str_contains($cleanJpeg, 'Photoshop'))->toBeFalse();

        // Cleanup
        unlink($tempFile);
    });

    it('strips injected metadata from PNG images', function () {
        $service = new ImageSanitizerService;

        // 1. Create a base PNG
        $img = imagecreatetruecolor(10, 10);
        ob_start();
        imagepng($img);
        $rawPng = ob_get_clean();
        imagedestroy($img);

        // 2. Inject custom metadata chunk before IEND
        $metadataString = 'CleanMeMetadata';
        $chunkData = "Description\x00" . $metadataString;
        $chunkLength = pack('N', strlen($chunkData));
        $chunkType = "tEXt";
        $crc = pack('N', crc32($chunkType . $chunkData));
        $tEXtChunk = $chunkLength . $chunkType . $chunkData . $crc;

        $iendPos = strpos($rawPng, 'IEND');
        $pngWithMetadata = substr($rawPng, 0, $iendPos - 4) . $tEXtChunk . substr($rawPng, $iendPos - 4);

        // Save to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempFile, $pngWithMetadata);

        expect(str_contains(file_get_contents($tempFile), $metadataString))->toBeTrue();

        // 3. Run the sanitization logic
        $result = $service->sanitize($tempFile, 'image/png');
        expect($result)->toBeTrue();

        // 4. Assert the metadata is completely gone
        $cleanPng = file_get_contents($tempFile);
        expect(str_contains($cleanPng, $metadataString))->toBeFalse();

        // Cleanup
        unlink($tempFile);
    });

    it('throws exception for unsupported image type', function () {
        $service = new ImageSanitizerService;
        $service->sanitize('test.gif', 'image/gif');
    })->throws(RuntimeException::class, 'Unsupported image type.');

    it('throws exception if reading JPEG file fails', function () {
        $service = new ImageSanitizerService;
        $service->sanitize('nonexistent_file.jpg', 'image/jpeg');
    })->throws(RuntimeException::class, 'Failed to read image file.');

    it('throws exception if reading PNG file fails', function () {
        $service = new ImageSanitizerService;
        $service->sanitize('nonexistent_file.png', 'image/png');
    })->throws(RuntimeException::class, 'Failed to read image file.');

    it('throws exception for invalid size of JPEG image', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempFile, 'abc'); // size < 4

        try {
            $service->sanitize($tempFile, 'image/jpeg');
        } finally {
            unlink($tempFile);
        }
    })->throws(RuntimeException::class, 'Invalid image file size.');

    it('throws exception for invalid size of PNG image', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempFile, 'abc'); // size < 8

        try {
            $service->sanitize($tempFile, 'image/png');
        } finally {
            unlink($tempFile);
        }
    })->throws(RuntimeException::class, 'Invalid image file size.');

    it('throws exception if JPEG does not start with SOI', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempFile, 'abcdefgh');

        try {
            $service->sanitize($tempFile, 'image/jpeg');
        } finally {
            unlink($tempFile);
        }
    })->throws(RuntimeException::class, 'Not a valid JPEG image.');

    it('throws exception if PNG does not start with signature', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempFile, 'abcdefgh');

        try {
            $service->sanitize($tempFile, 'image/png');
        } finally {
            unlink($tempFile);
        }
    })->throws(RuntimeException::class, 'Not a valid PNG image.');

    it('throws exception if writing sanitized JPEG fails', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $img = imagecreatetruecolor(10, 10);
        ob_start();
        imagejpeg($img, null, 100);
        $rawJpeg = ob_get_clean();
        file_put_contents($tempFile, $rawJpeg);
        imagedestroy($img);

        // Turn on write failure mock
        $GLOBALS['mock_file_put_contents_fail'] = true;

        try {
            $service->sanitize($tempFile, 'image/jpeg');
        } finally {
            $GLOBALS['mock_file_put_contents_fail'] = false;
            unlink($tempFile);
        }
    })->throws(RuntimeException::class, 'Failed to write sanitized JPEG file.');

    it('throws exception if writing sanitized PNG fails', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $img = imagecreatetruecolor(10, 10);
        ob_start();
        imagepng($img);
        $rawPng = ob_get_clean();
        file_put_contents($tempFile, $rawPng);
        imagedestroy($img);

        // Turn on write failure mock
        $GLOBALS['mock_file_put_contents_fail'] = true;

        try {
            $service->sanitize($tempFile, 'image/png');
        } finally {
            $GLOBALS['mock_file_put_contents_fail'] = false;
            unlink($tempFile);
        }
    })->throws(RuntimeException::class, 'Failed to write sanitized PNG file.');

    it('breaks JPEG loop and copies remainder if non-marker byte is found', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $jpegData = "\xFF\xD8\xFF\xE0\x00\x02\x12\x34";
        file_put_contents($tempFile, $jpegData);

        try {
            $service->sanitize($tempFile, 'image/jpeg');
            expect(file_get_contents($tempFile))->toBe($jpegData);
        } finally {
            unlink($tempFile);
        }
    });

    it('breaks JPEG loop if file ends with trailing 0xFF', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $jpegData = "\xFF\xD8\xFF\xE0\x00\x02\xFF";
        file_put_contents($tempFile, $jpegData);

        try {
            $service->sanitize($tempFile, 'image/jpeg');
            expect(file_get_contents($tempFile))->toBe($jpegData);
        } finally {
            unlink($tempFile);
        }
    });

    it('breaks JPEG loop if segment length bytes are missing', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $jpegData = "\xFF\xD8\xFF\xE0\x00";
        file_put_contents($tempFile, $jpegData);

        try {
            $service->sanitize($tempFile, 'image/jpeg');
            expect(file_get_contents($tempFile))->toBe($jpegData);
        } finally {
            unlink($tempFile);
        }
    });

    it('breaks PNG loop if chunk header is incomplete', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $pngData = "\x89PNG\r\n\x1a\n\x00\x00\x00";
        file_put_contents($tempFile, $pngData);

        try {
            $service->sanitize($tempFile, 'image/png');
            expect(file_get_contents($tempFile))->toBe($pngData);
        } finally {
            unlink($tempFile);
        }
    });

    it('breaks PNG loop if chunk data is incomplete', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $pngData = "\x89PNG\r\n\x1a\n" . pack('N', 100) . "IHDR";
        file_put_contents($tempFile, $pngData);

        try {
            $service->sanitize($tempFile, 'image/png');
            expect(file_get_contents($tempFile))->toBe($pngData);
        } finally {
            unlink($tempFile);
        }
    });

    it('copies standalone markers without length in JPEG', function () {
        $service = new ImageSanitizerService;
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        $jpegData = "\xFF\xD8\xFF\x01\xFF\xDA\x12\x34";
        file_put_contents($tempFile, $jpegData);

        try {
            $service->sanitize($tempFile, 'image/jpeg');
            expect(file_get_contents($tempFile))->toBe($jpegData);
        } finally {
            unlink($tempFile);
        }
    });
}
