# Mangoon - Manga Management Platform

A modern manga and comic management platform built with Laravel 12, React, TypeScript, and PostgreSQL. Supports CBZ format, external OAuth, multi-user with library-scoped permissions, and S3/MinIO storage.

## Features

- **Manga Management**: Full CRUD for manga, chapters, and CBZ files
- **CBZ Reader**: On-demand extraction and WebP conversion for optimal viewing
- **Multi-User System**: Role-based access control with library-scoped permissions
- **External OAuth**: Generic OAuth2 support (GitHub, Google, etc.)
- **Storage**: S3/MinIO integration with pre-signed URLs
- **External Sources**: Plugin system for nhentai, MangaDex, etc.
- **Multi-Language**: Manga version linking for translations
- **Dockerized**: Full Docker Compose and Kubernetes deployment

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4, PostgreSQL 15, Redis
- **Frontend**: React 19, TypeScript, Vite, TailwindCSS 4, Zustand
- **Storage**: MinIO (S3-compatible)
- **Auth**: JWT + Laravel Socialite
- **Queue**: Redis-based job processing
- **Deployment**: Docker Compose, Kubernetes

## Quick Start (Development)

### Prerequisites

- Docker and Docker Compose
- Node.js 20+ (for frontend development)

### 1. Clone and Setup

```bash
git clone <repository-url>
cd mangoon

# Copy environment files
cp .env.example .env

# Generate keys
php artisan key:generate
php artisan jwt:secret
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

## API Endpoints

### Authentication

- `POST /api/v1/auth/login` - Login with email/password
- `POST /api/v1/auth/logout` - Logout
- `POST /api/v1/auth/refresh` - Refresh JWT token
- `GET /api/v1/auth/me` - Get current user
- `GET /api/v1/auth/oauth/{provider}/redirect` - OAuth redirect
- `GET /api/v1/auth/oauth/{provider}/callback` - OAuth callback

### Manga

- `GET /api/v1/manga` - List manga
- `GET /api/v1/manga/{id}` - Get manga details
- `POST /api/v1/manga` - Create manga (admin)
- `PUT /api/v1/manga/{id}` - Update manga (admin)
- `DELETE /api/v1/manga/{id}` - Delete manga (admin)

### Chapters

- `GET /api/v1/chapters/{id}/pages` - Get chapter pages
- `GET /api/v1/chapters/{id}/page/{page}` - Get page URL

### Libraries

- `GET /api/v1/libraries` - List libraries
- `POST /api/v1/libraries/{id}/users/{user}/assign-role` - Assign role

## Project Structure

```
mangoon/
├── app/                    # Laravel backend
│   ├── Http/Controllers/Api/V1/
│   ├── Models/
│   ├── Services/
│   │   ├── S3Service.php
│   │   └── CbzProcessor.php
│   └── Plugins/
├── frontend/               # React frontend
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── stores/         # Zustand stores
│   │   └── services/
│   └── package.json
├── k8s/                    # Kubernetes manifests
├── docker-compose.yml
└── Dockerfile
```

## Development

### Backend

```bash
# Run in development mode
composer run dev

# Run tests
composer test
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
```

## License

MIT License

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request
