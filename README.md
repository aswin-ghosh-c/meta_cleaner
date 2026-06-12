# Anonymous Document Sanitizer

An elegant, privacy-first web application designed to strip EXIF, IPTC, and XMP metadata arrays from JPEG, JPG, and PNG files securely in transient memory.

> [!NOTE]
> **Personal Project:** This is a personal project built for personal data optimization, data minimization, and individual privacy preservation.

---

## Features

- **Zero Data Retention:** Submitted images are processed entirely in memory and never written to disk or database storage.
- **Volatile Execution:** Files are stripped of metadata tags (GPS, camera profiles, device info) during the lifecycle of the HTTP request and immediately returned to the client.
- **Multilingual Support:** Localized interfaces available in English, Spanish, French, German, and Chinese.
- **Dark/Light Mode:** Responsive UI styled with Tailwind CSS v4 and Vue 3 components.

---

## Tech Stack

- **Backend:** Laravel 11.x (PHP 8.2+)
- **Frontend:** Vue 3, Inertia.js
- **Styling:** Tailwind CSS v4
- **Internationalization:** Vue I18n v11

---

## Local Development Setup

To run this project locally, ensure you have PHP, Composer, and Node.js installed.

### 1. Clone & Install Dependencies
```bash
composer install
npm install
```

### 2. Configure Environment
Copy the example environment file:
```bash
cp .env.example .env
```
Generate an application key:
```bash
php artisan key:generate
```

### 3. Run Development Servers
Start the Laravel serve command:
```bash
php artisan serve
```
In a separate terminal, start the Vite development server:
```bash
npm run dev
```

The application will be accessible at `http://127.0.0.1:8000`.

---

## Search Engine Optimization (SEO)

To maximize discoverability and indexation:
- **`robots.txt`**: Located at [/public/robots.txt](file:///c:/PunkLab/meta_cleaner/public/robots.txt), it configures crawl permissions and references the XML sitemap.
- **`sitemap.xml`**: Located at [/public/sitemap.xml](file:///c:/PunkLab/meta_cleaner/public/sitemap.xml), it specifies public routes for indexation.
- **Meta Tags**: Configured in [app.blade.php](file:///c:/PunkLab/meta_cleaner/resources/views/app.blade.php) with canonical URLs, Open Graph, and Twitter Cards.

---

## Contributing, MRs & Fixes

Contributions are welcome! Since this is a personal project, bug fixes, refactorings, design improvements, and translation updates are highly appreciated. 

If you notice any bugs or have ideas for enhancement:
1. Fork the repository.
2. Create a feature branch.
3. Submit a **Merge Request (MR) / Pull Request (PR)** with a clear description of your fixes/changes.
