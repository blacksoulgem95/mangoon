
```markdown
# Mangoon Manga Management System
**Version:** 1.0.0  
**Author:** The Mangoon Development Team  
**Date:** 2026‑02‑15  

---

## Table of Contents

1. [Overview](#overview)  
2. [Database Schema](#database-schema)  
3. [Application Architecture](#application-architecture)  
4. [Routes & Controllers](#routes--controllers)  
5. [Views & UI Flow](#views--ui-flow)  
6. [Storage Configuration](#storage-configuration)  
7. [Testing & CI](#testing--ci)  
8. [Future Enhancements](#future-enhancements)  

---

## 1. Overview

Mangoon is a Laravel‑12 application that provides a full‑stack solution for managing, browsing, and reading manga.  
Key features:

- **CBZ‑based manga uploads** (ZIP archives containing images).  
- **Retro‑futuristic Fallout‑style UI** using Tailwind + custom CSS.  
- **Admin panel** for CRUD of Manga and Chapters, bulk uploads, and relationship management.  
- **Reader** that streams pages directly from the CBZ archive via the `ChapterController@page` route.  
- **Flexible storage**: local disk or any S3‑compatible provider (AWS, DigitalOcean Spaces, etc.).  
- **Internationalization & translations** for manga metadata.

---

## 2. Database Schema

| Table | Key Columns | Relationships | Notes |
|-------|-------------|---------------|-------|
| `mangas` | `id`, `slug`, `author`, `illustrator`, `status`, `type`, `cover_image`, `banner_image`, `publication_year`, `rating`, `is_active`, `is_featured`, `is_mature` | **HasMany** `chapters`<br>**BelongsToMany** `categories`<br>**BelongsToMany** `tags`<br>**BelongsToMany** `libraries`<br>**HasMany** `translations` | Soft‑deleted (`deleted_at`) |
| `manga_translations` | `id`, `manga_id`, `language_code`, `title`, `synopsis`, `description` | **BelongsTo** `manga` | |
| `chapters` | `id`, `manga_id`, `slug`, `chapter_number`, `title`, `volume_number`, `release_date`, `cbz_file_path`, `storage_disk`, `file_size`, `page_count`, `sort_order`, `is_active`, `is_premium` | **BelongsTo** `manga` | Soft‑deleted (`deleted_at`) |
| `manga_tags` | `manga_id`, `tag_id` | Pivot table for `mangas` ↔ `tags` | |
| `manga_categories` | `manga_id`, `category_id` | Pivot table for `mangas` ↔ `categories` | |
| `manga_libraries` | `manga_id`, `library_id` | Pivot table for `mangas` ↔ `libraries` | |
| `tags` | `id`, `slug`, `name` | **HasMany** `manga_translations` | |
| `categories` | `id`, `slug`, `name` | **HasMany** `manga_translations` | |
| `libraries` | `id`, `slug`, `name` | **HasMany** `manga_translations` | |
| `sources` | `id`, `slug`, `name` | **HasMany** `mangas` | |
| `permissions` / `roles` | standard Laravel permission tables | ACL for admin features | |

**Notes on relationships**

- The *pivot* tables use `sort_order` to order tags, categories, and libraries.
- Chapters reference a CBZ file on disk. `storage_disk` determines which Laravel filesystem disk to use.
- The `page_count` column is updated after a CBZ is uploaded or the file is replaced.

---

## 3. Application Architecture

```
┌───────────────────────┐
│   Routes (web.php)    │
├───────────────────────┤
│   Controllers          │
│   ├─ MangaController   │
│   ├─ ChapterController│
│   ├─ Admin\MangaController  │
│   ├─ Admin\ChapterController│
│   └─ ...                │
├───────────────────────┤
│   Models               │
│   ├─ Manga            │
│   ├─ MangaTranslation │
│   ├─ Chapter          │
│   ├─ Tag, Category, Library, Source │
├───────────────────────┤
│   Views                │
│   ├─ layouts/app.blade.php │
│   ├─ manga/*.blade.php │
│   ├─ reader/*.blade.php │
│   └─ admin/*/*.blade.php │
├───────────────────────┤
│   Resources (CSS/JS)   │
│   ├─ css/central/stylesheet.css  │
│   ├─ css/app.css            │
│   └─ js/...                  │
├───────────────────────┤
│   Storage              │
│   └─ storage/app/public/manga/ │
└───────────────────────┘
```

- **Controllers** handle HTTP requests, fetch data via Eloquent models, and return Blade views or JSON responses (e.g., for page lists).
- **Views** use the Fallout‑style CSS. The `layouts.app` provides navigation, flash messages, and a consistent footer.
- **Assets** are built with Vite, bundling Tailwind and custom JS.
- **Storage** is configured in `config/filesystems.php`. The `manga_disk` disk defaults to `public` but can be overridden via environment variables.

---

## 4. Routes & Controllers

### Front‑end Routes

| Route | Controller | Purpose | Example URL |
|-------|------------|---------|-------------|
| `/` | `MangaController@index` | Show manga listings | `/` |
| `/manga/{manga}` | `MangaController@show` | Manga detail page | `/manga/great-manga` |
| `/manga/{manga}/chapters` | `MangaController@chapters` | List chapters of a manga | `/manga/great-manga/chapters` |
| `/read/{manga}/{chapter}` | `ChapterController@show` | Reader page (first page of chapter) | `/read/great-manga/chapter-1` |
| `/read/{manga}/{chapter}/page/{page}` | `ChapterController@page` | Serve a single page image | `/read/great-manga/chapter-1/page/5` |
| `/read/{manga}/{chapter}/pages` | `ChapterController@pages` | JSON list of all pages (used by JS) | `/read/great-manga/chapter-1/pages` |

### Admin Routes

All admin routes are prefixed with `/admin` and guarded by the `auth` middleware.

| Route | Controller | Purpose |
|-------|------------|---------|
| `/admin/manga` | `Admin\MangaController@index` | List mangas |
| `/admin/manga/create` | `Admin\MangaController@create` | Show create form |
| `/admin/manga` (POST) | `Admin\MangaController@store` | Persist new manga |
| `/admin/manga/{manga}` | `Admin\MangaController@show` | View details |
| `/admin/manga/{manga}/edit` | `Admin\MangaController@edit` | Edit form |
| `/admin/manga/{manga}` (PUT) | `Admin\MangaController@update` | Persist changes |
| `/admin/manga/{manga}` (DELETE) | `Admin\MangaController@destroy` | Soft delete |
| `/admin/manga/{id}/restore` | `Admin\MangaController@restore` | Restore soft‑deleted manga |
| `/admin/manga/{id}/force-delete` | `Admin\MangaController@forceDelete` | Hard delete |
| `/admin/manga/{manga}/toggle-active` | `Admin\MangaController@toggleActive` | Enable/disable |
| `/admin/manga/{manga}/toggle-featured` | `Admin\MangaController@toggleFeatured` | Feature/unfeature |

| Chapter Admin Routes | Controller | Purpose |
|---------------------|------------|---------|
| `/admin/manga/{manga}/chapters` | `Admin\ChapterController@index` | List chapters |
| `/admin/manga/{manga}/chapters/create` | `Admin\ChapterController@create` | Form to create a chapter (CBZ upload) |
| `/admin/manga/{manga}/chapters` (POST) | `Admin\ChapterController@store` | Persist new chapter |
| `/admin/manga/{manga}/chapters/{chapter}` | `Admin\ChapterController@show` | View chapter details |
| `/admin/manga/{manga}/chapters/{chapter}/edit` | `Admin\ChapterController@edit` | Edit form |
| `/admin/manga/{manga}/chapters/{chapter}` (PUT) | `Admin\ChapterController@update` | Persist changes |
| `/admin/manga/{manga}/chapters/{chapter}` (DELETE) | `Admin\ChapterController@destroy` | Soft delete |
| `/admin/manga/{manga}/chapters/{chapter}/restore` | `Admin\ChapterController@restore` | Restore |
| `/admin/manga/{manga}/chapters/{chapter}/force-delete` | `Admin\ChapterController@forceDelete` | Hard delete |
| `/admin/manga/{manga}/chapters/{chapter}/toggle-active` | `Admin\ChapterController@toggleActive` | Enable/disable |

**Bulk upload** is handled via the `bulkUpload` action on `Admin\ChapterController`, which accepts an array of CBZ files.

---

## 5. Views & UI Flow

### Front‑end

1. **Home Page** (`/`)  
   - Shows a grid of manga cards.  
   - Users can filter by search term, category, tag, or sort order.

2. **Manga Detail** (`/manga/{slug}`)  
   - Displays cover, banner, synopsis, and a list of chapters.  
   - Clicking a chapter takes you to the reader.

3. **Chapter Reader** (`/read/{manga}/{chapter}`)  
   - Shows the first page with navigation controls.  
   - Left/right overlays allow page swiping.  
   - Sidebar shows chapter navigation and actions.

4. **Page Serving** (`/read/{manga}/{chapter}/page/{page}`)  
   - Controller extracts the requested image from the CBZ and streams it with correct MIME type.  
   - Browser cache headers are set for long‑term caching.

5. **Page List JSON** (`/read/{manga}/{chapter}/pages`)  
   - Returns an array of page URLs used by the JS slider.

### Admin

1. **Dashboard** (`/admin`)  
   - Quick stats and links to Manga, Chapters, Users, etc.

2. **Manga Management**  
   - List, create, edit, delete, restore, toggle active/featured.  
   - The create/edit forms support file uploads for cover and banner, multi‑select for tags, categories, and libraries, and translation blocks.

3. **Chapter Management**  
   - List of chapters with sort order, status, and actions.  
   - Create form includes CBZ upload, optional cover image, and metadata.  
   - Bulk upload form allows uploading multiple CBZ files in one request.

4. **Page Viewer in Admin**  
   - The chapter show page displays the cover image, metadata, and actions.

All forms use Laravel's CSRF protection, and validation errors are displayed next to each field. Flash messages use the alert component defined in the layout.

---

## 6. Storage Configuration

`config/filesystems.php`

| Disk | Driver | Root | URL | Notes |
|------|--------|------|-----|-------|
| `local` | `local` | `storage_path('app/private')` | N/A | For private files. |
| `public` | `local` | `storage_path('app/public')` | `APP_URL/storage` | Publicly accessible. |
| `s3` | `s3` | — | `AWS_URL` | Requires `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, optionally `AWS_ENDPOINT`. |
| `manga_disk` | **Custom** | `storage_path('app/public/manga')` | `APP_URL/storage/manga` | Default disk for manga assets. Can override via `MANGA_DISK` env var. |
| `manga-s3` | `s3` | — | `MANGA_AWS_URL` | Alternative disk that uses S3, configured with `MANGA_AWS_*` env vars. |

**How the UI uses storage**

- When creating or updating a manga, the `cover_image` and `banner_image` are stored on `manga_disk`.  
- When creating or updating a chapter, the CBZ file is stored on the disk specified in the chapter's `storage_disk` column (defaults to the configured `manga_disk`).  
- The `Chapter` model uses Laravel's `Storage` facade to read the CBZ file and extract page data.

---

## 7. Testing & CI

- **Pest** (`pestphp/pest`) is the testing framework.  
- Tests are located under `tests/Feature` and `tests/Unit`.  
- Example test files: `tests/Feature/MangaControllerTest.php`, `tests/Feature/ChapterControllerTest.php`.  
- CI pipeline (GitHub Actions) runs:  
  ```yaml
  - name: Setup PHP
    uses: shivammathur/setup-php@v2
    with:
      php-version: 8.3
  - name: Install Dependencies
    run: composer install --prefer-dist
  - name: Run Tests
    run: ./vendor/bin/pest --compact
  ```

---

## 8. Future Enhancements

| Area | Idea | Status |
|------|------|--------|
| **API** | Add REST endpoints for manga/chapters (JSON) | Planned |
| **Internationalization** | Add language switcher for UI and API | In progress |
| **User accounts** | Reader authentication & bookmarks | Planned |
| **Caching** | Cache extracted page lists to improve read speed | In progress |
| **Image processing** | Automatic thumbnail generation for covers | Planned |
| **Search** | Full‑text search with Algolia or Elastic | Planned |

---

### Contact & Contribution

- **Repository:** https://github.com/your-org/mangoon  
- **Issue Tracker:** https://github.com/your-org/mangoon/issues  
- **Contributing Guide:** `CONTRIBUTING.md`

Happy reading and developing!