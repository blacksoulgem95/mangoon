# Mangoon - Manga Management Platform

A modern manga and comic management platform built with Laravel 12, React, TypeScript, and PostgreSQL. Supports CBZ format, external OAuth, multi-user with library-scoped permissions, and S3/MinIO storage.

## Features

- **Manga Management**: Full CRUD for manga, chapters, and CBZ files
- **CBZ Reader**: On-demand extraction and WebP conversion for optimal viewing
- **Multi-User System**: Role-based access control with library-scoped permissions
- **External OAuth**: Generic OAuth2 support (GitHub, Google, etc.)
- **Storage**: S3/MinIO integration with pre-signed URLs
- **External Sources**: Plugin system for nhentai, MangaDex with cookie management
- **Version Merging**: Link manga translations and detect duplicates
- **Multi-Language**: Support for manga in multiple languages
- **Dockerized**: Full Docker Compose and Kubernetes deployment
- **E2E Testing**: Comprehensive Playwright test suite

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4, PostgreSQL 15, Redis
- **Frontend**: React 19, TypeScript, Vite, TailwindCSS 4, Zustand
- **Storage**: MinIO (S3-compatible)
- **Auth**: JWT + Laravel Socialite
- **Queue**: Redis-based job processing
- **Testing**: Playwright (E2E), Pest (Backend)
- **Deployment**: Docker Compose, Kubernetes

## Quick Start (Development)

### Prerequisites

- Docker and Docker Compose
- Node.js 20+ (for frontend development)
- PHP 8.4+ (for local development)

### 1. Clone and Setup

```bash
git clone <repository-url>
cd mangoon

# Backend setup
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Frontend setup
cd frontend
cp .env.example .env 2>/dev/null || true
```

### 2. Start Services

```bash
# Start all services (backend, frontend, postgres, redis, minio)
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Create MinIO bucket
docker-compose exec minio mc mb --ignore-existing minio/mangoon
```

### 3. Access the Application

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **MinIO Console**: http://localhost:9001
- **Admin Panel**: http://localhost:5173/admin

## Configuration

### OAuth Setup

1. Create an OAuth app on your provider (e.g., GitHub)
2. Set callback URL: `http://localhost:8000/api/v1/auth/oauth/{provider}/callback`
3. Update `.env`:

```env
SOCIALITE_PROVIDERS=github
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
```

### S3/MinIO Setup

```env
MANGA_DISK=s3
MANGA_AWS_ACCESS_KEY_ID=minioadmin
MANGA_AWS_SECRET_ACCESS_KEY=minioadmin
MANGA_AWS_DEFAULT_REGION=us-east-1
MANGA_AWS_BUCKET=mangoon
MANGA_AWS_ENDPOINT=http://localhost:9000
MANGA_AWS_USE_PATH_STYLE_ENDPOINT=true
```

### External Sources (nhentai)

1. Extract cookies from your browser while logged into nhentai
2. Export as JSON (using a browser extension)
3. Import via Admin Panel → Cookie Management
4. Cookies expire after 30 days by default

## Testing

### Backend Tests (Pest)

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test tests/Feature/MangaTest.php

# Run with coverage
docker-compose exec app php artisan test --coverage
```

### Frontend E2E Tests (Playwright)

```bash
cd frontend

# Install Playwright browsers (first time only)
npx playwright install chromium

# Run all tests
npm run test

# Run tests in UI mode
npm run test:ui

# Run tests with browser visible
npm run test:headed

# Run specific test file
npm run test tests/e2e/auth.spec.ts

# Generate and view test report
npm run test
npm run test:report
```

### Test Structure

```
frontend/tests/e2e/
├── auth.spec.ts          # Authentication tests
├── manga.spec.ts         # Manga catalog and reader tests
└── admin.spec.ts         # Admin panel tests
```

## API Endpoints

### Authentication

- `POST /api/v1/auth/login` - Login with email/password
- `POST /api/v1/auth/logout` - Logout
- `POST /api/v1/auth/refresh` - Refresh JWT token
- `GET /api/v1/auth/me` - Get current user
- `GET /api/v1/auth/oauth/{provider}/redirect` - OAuth redirect
- `GET /api/v1/auth/oauth/{provider}/callback` - OAuth callback

### Manga

- `GET /api/v1/manga` - List manga (supports pagination, filtering)
- `GET /api/v1/manga/{id}` - Get manga details
- `POST /api/v1/manga` - Create manga (admin)
- `PUT /api/v1/manga/{id}` - Update manga (admin)
- `DELETE /api/v1/manga/{id}` - Delete manga (admin)
- `GET /api/v1/manga/{id}/versions` - List linked versions
- `POST /api/v1/manga/{id}/versions` - Link version
- `DELETE /api/v1/manga/{id}/versions/{versionId}` - Unlink version
- `GET /api/v1/admin/merge/suggestions` - Get duplicate suggestions

### Chapters

- `GET /api/v1/chapters/{id}/pages` - Get chapter pages
- `GET /api/v1/chapters/{id}/page/{page}` - Get page URL

### External Sources

- `GET /api/v1/sources/{source}/search?q=query` - Search external source
- `GET /api/v1/sources/{source}/{sourceId}` - Get source details
- `POST /api/v1/sources/{source}/{sourceId}/download` - Download from source

### Cookie Management

- `GET /api/v1/cookies` - List user cookies
- `POST /api/v1/cookies` - Save new cookie
- `PUT /api/v1/cookies/{id}` - Update cookie
- `DELETE /api/v1/cookies/{id}` - Delete cookie
- `POST /api/v1/cookies/import` - Import from browser JSON

### Libraries

- `GET /api/v1/libraries` - List libraries
- `POST /api/v1/libraries` - Create library
- `PUT /api/v1/libraries/{id}` - Update library
- `DELETE /api/v1/libraries/{id}` - Delete library
- `POST /api/v1/libraries/{id}/users/{user}/assign-role` - Assign role

## Project Structure

```
mangoon/
├── app/                    # Laravel backend
│   ├── Http/Controllers/Api/V1/
│   │   ├── AuthController.php
│   │   ├── MangaController.php
│   │   ├── ChapterController.php
│   │   ├── LibraryController.php
│   │   ├── ExternalSourceController.php
│   │   └── Admin/
│   │       └── CookieController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Manga.php
│   │   ├── Chapter.php
│   │   ├── Library.php
│   │   ├── OauthAccount.php
│   │   ├── UserCookie.php
│   │   ├── ExternalSource.php
│   │   └── MangaVersion.php
│   ├── Services/
│   │   ├── S3Service.php
│   │   └── CbzProcessor.php
│   ├── Jobs/
│   │   └── DownloadChapters.php
│   └── Plugins/
├── frontend/               # React frontend
│   ├── src/
│   │   ├── components/
│   │   │   ├── ui/        # shadcn/ui components
│   │   │   └── Layout.tsx
│   │   ├── pages/
│   │   │   ├── Home.tsx
│   │   │   ├── Login.tsx
│   │   │   ├── MangaDetail.tsx
│   │   │   ├── Reader.tsx
│   │   │   └── admin/
│   │   ├── stores/        # Zustand stores
│   │   │   ├── authStore.ts
│   │   │   └── mangaStore.ts
│   │   └── lib/
│   └── tests/e2e/         # Playwright tests
├── k8s/                    # Kubernetes manifests
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Kubernetes Deployment

### 1. Update Secrets

Edit `k8s/secret.yaml` with your actual values:
- Database credentials
- AWS/S3 credentials
- OAuth credentials
- JWT secret

### 2. Build and Push Images

```bash
# Build backend image
docker build -t your-registry/mangoon-app:latest .
docker push your-registry/mangoon-app:latest

# Build frontend image
cd frontend
docker build -t your-registry/mangoon-frontend:latest .
docker push your-registry/mangoon-frontend:latest
```

### 3. Deploy

```bash
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secret.yaml
kubectl apply -f k8s/
```

### 4. External Dependencies

You'll need to provision externally:
- PostgreSQL database
- Redis instance
- S3-compatible storage (MinIO, AWS S3, DigitalOcean Spaces)

## Development

### Backend

```bash
# Run in development mode
composer run dev

# Run tests
composer test

# Generate API documentation
php artisan swagger:generate
```

### Frontend

```bash
cd frontend

# Install dependencies
npm install

# Development server
npm run dev

# Build for production
npm run build

# Run E2E tests
npm run test
```

## License

MIT License

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Testing Requirements

- All new features must include tests
- Backend: Pest PHP tests in `tests/Feature/` and `tests/Unit/`
- Frontend: Playwright E2E tests in `frontend/tests/e2e/`
- All tests must pass before merging

## Support

For issues, questions, or contributions:
- Open an issue on GitHub
- Check existing documentation
- Contact the maintainers
