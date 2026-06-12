<?php

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

it('renders the sanitizer landing page successfully', function () {
    $response = $this->get(route('sanitizer.index'));

    $response->assertStatus(200);
});

it('fails validation when no file is uploaded', function () {
    $response = $this->post(route('sanitizer.sanitize'), [], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['file']);
});

it('fails validation when an unsupported mime-type is uploaded', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->post(route('sanitizer.sanitize'), [
        'file' => $file,
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['file']);
});

it('fails validation when a file exceeds the 10MB size limit', function () {
    // 11MB is 11264 KB
    $file = UploadedFile::fake()->create('large.jpg', 11 * 1024, 'image/jpeg');

    $response = $this->post(route('sanitizer.sanitize'), [
        'file' => $file,
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['file']);
});

it('successfully sanitizes a valid image and deletes the temporary file afterwards', function () {
    // Create a real JPEG image using fake upload
    $file = UploadedFile::fake()->image('test.jpg', 50, 50);

    $response = $this->post(route('sanitizer.sanitize'), [
        'file' => $file,
    ]);

    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=test.jpg');

    // Assert response is a BinaryFileResponse and deleteFileAfterSend is enabled
    $baseResponse = $response->baseResponse;
    expect($baseResponse)->toBeInstanceOf(BinaryFileResponse::class);

    $filePath = $baseResponse->getFile()->getPathname();
    expect($filePath)->toBeFile();

    // Simulate Laravel's lifecycle cleanup: send response content (which deletes the file after send)
    $baseResponse->sendContent();

    // Verify the temporary file has been deleted from disk
    expect($filePath)->not()->toBeFile();
});

it('returns 422 JSON response when SanitizerService fails', function () {
    $this->mock(\App\Services\ImageSanitizerService::class, function ($mock) {
        $mock->shouldReceive('sanitize')->andThrow(new Exception('Mocked sanitization failure'));
    });

    $file = UploadedFile::fake()->image('test.jpg', 50, 50);

    $response = $this->post(route('sanitizer.sanitize'), [
        'file' => $file,
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422);
    $response->assertJson(['message' => 'Mocked sanitization failure']);
});

