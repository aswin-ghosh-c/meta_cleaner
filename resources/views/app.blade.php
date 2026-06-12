<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- Primary Meta Tags -->
        <meta name="description" content="Clean and sanitize your images from tracking metadata, EXIF profiles, GPS coordinates, and camera tags instantly. 100% private, zero data retention.">
        <meta name="keywords" content="metadata cleaner, exif stripper, sanitize image, strip gps, zero storage, privacy cleaner, photo sanitizer, document cleaner">
        <meta name="author" content="Anonymous_Document_Sanitizer">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url('/') }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:title" content="Anonymous Document Sanitizer - Strip Image Metadata & EXIF">
        <meta property="og:description" content="Securely strip EXIF, IPTC, and XMP metadata arrays from JPEG and PNG files in memory. 100% confidential with zero retention.">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url('/') }}">
        <meta name="twitter:title" content="Anonymous Document Sanitizer - Strip Image Metadata & EXIF">
        <meta name="twitter:description" content="Securely strip EXIF, IPTC, and XMP metadata arrays from JPEG and PNG files in memory. 100% confidential with zero retention.">

        <title inertia>{{ Str::replace('_', ' ', config('app.name', 'Anonymous Document Sanitizer')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/js/app.js', 'resources/css/app.css'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 selection:bg-cyan-500 selection:text-slate-950 transition-colors duration-300">
        @inertia
    </body>
</html>
