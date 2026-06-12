<?php

use App\Services\ImageSanitizerService;

it('strips injected metadata from JPEG images', function () {
    $service = new ImageSanitizerService;

    // 1. Create a base JPEG
    $img = imagecreatetruecolor(10, 10);
    ob_start();
    imagejpeg($img, null, 100);
    $rawJpeg = ob_get_clean();

    // 2. Inject a custom Comment (COM) segment containing "CleanMeMetadata"
    $metadataString = 'CleanMeMetadata';
    $len = strlen($metadataString) + 2;
    $comSegment = "\xFF\xFE".chr($len >> 8).chr($len & 0xFF).$metadataString;
    $jpegWithMetadata = substr($rawJpeg, 0, 2).$comSegment.substr($rawJpeg, 2);

    // Save to a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
    file_put_contents($tempFile, $jpegWithMetadata);

    // Verify the metadata is indeed present in the source file
    expect(str_contains(file_get_contents($tempFile), $metadataString))->toBeTrue();

    // 3. Run the sanitization logic via Service
    $result = $service->sanitize($tempFile, 'image/jpeg');
    expect($result)->toBeTrue();

    // 4. Assert the metadata string is completely gone
    $cleanJpeg = file_get_contents($tempFile);
    expect(str_contains($cleanJpeg, $metadataString))->toBeFalse();

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

    // 2. Inject metadata by appending a text block
    $metadataString = 'CleanMeMetadata';
    $pngWithMetadata = $rawPng.$metadataString;

    // Save to a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
    file_put_contents($tempFile, $pngWithMetadata);

    // Verify metadata is present in the source file
    expect(str_contains(file_get_contents($tempFile), $metadataString))->toBeTrue();

    // 3. Run the sanitization logic via Service
    $result = $service->sanitize($tempFile, 'image/png');
    expect($result)->toBeTrue();

    // 4. Assert the metadata string is completely gone
    $cleanPng = file_get_contents($tempFile);
    expect(str_contains($cleanPng, $metadataString))->toBeFalse();

    // Cleanup
    unlink($tempFile);
});
