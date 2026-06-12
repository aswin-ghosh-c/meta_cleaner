<?php

namespace App\Http\Controllers;

use App\Http\Requests\SanitizeRequest;
use App\Services\ImageSanitizerService;
use Exception;
use Inertia\Inertia;

class SanitizerController extends Controller
{
    protected ImageSanitizerService $sanitizerService;

    /**
     * Inject the ImageSanitizerService.
     */
    public function __construct(ImageSanitizerService $sanitizerService)
    {
        $this->sanitizerService = $sanitizerService;
    }

    /**
     * Render the Inertia front-end wrapper.
     */
    public function index()
    {
        return Inertia::render('Sanitizer/Index');
    }

    /**
     * Execute metadata stripping on upload and return immediately.
     */
    public function sanitize(SanitizeRequest $request)
    {
        $file = $request->file('file');
        $path = $file->getRealPath();
        $mime = $file->getMimeType();
        $originalName = $file->getClientOriginalName();

        try {
            $this->sanitizerService->sanitize($path, $mime);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Return the clean file as a download and delete it from temporary folder after sending
        return response()->download($path, $originalName)->deleteFileAfterSend(true);
    }
}
