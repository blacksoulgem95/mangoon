# Mangoon - Advanced Manga Management System

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Mangoon is a comprehensive, enterprise-grade manga management system built with Laravel 12. It provides a robust platform for managing, organizing, and distributing manga content with full internationalization support, granular access control, and an extensible plugin architecture.

## 📋 Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Schema](#database-schema)
- [Core Features](#core-features)
  - [Manga Management](#manga-management)
  - [Internationalization (i18n)](#internationalization-i18n)
  - [Libraries](#libraries)
  - [Plugin System](#plugin-system)
  - [Access Control (ACL)](#access-control-acl)
- [Usage Examples](#usage-examples)
- [Plugin Development](#plugin-development)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### 🌍 Comprehensive Internationalization (i18n)
- Multi-language support for all content (manga titles, descriptions, categories, tags, libraries)
- Easy language addition and management
- Fallback mechanism for missing translations
- Language-specific content versioning

### 📚 Advanced Manga Management
- Complete manga metadata tracking (author, illustrator, year, publisher, ISBN, etc.)
- Multiple manga versions support (translations, adaptations, spin-offs)
- Status tracking (ongoing, completed, hiatus, cancelled, upcoming)
- Type classification (manga, manhwa, manhua, webtoon, novel)
- Rich media support (cover images, banners)
- Rating and engagement metrics (views, favorites, ratings)
- Mature content filtering

### 🔌 Extensible Plugin System
- Standardized plugin interface for manga downloaders
- Built-in MangaDex plugin example
- Plugin configuration schema validation
- Rate limiting and concurrent download management
- Plugin priority system
- Automatic plugin registration and discovery
- Comprehensive plugin documentation

### 🔐 Granular Access Control (ACL)
- Role-based access control with permission management
- Library-scoped permissions
- Hierarchical role levels
- Permission scopes: global, library, own
- Temporary role assignments with expiration
- System roles protection (admin, editor, reader)

### 📖 Library Management
- Separate manga collections (Shonen, Shoujo, etc.)
- Public/private library settings
- Featured manga support
- Cross-library manga sharing
- Library-specific access control

### 🏷️ Rich Taxonomies
- Hierarchical categories
- Flexible tagging system
- Source tracking (publishers, scanlators, websites)
- All fully translatable

## 🔧 System Requirements

- **PHP**: 8.3 or higher
- **Laravel**: 12.x
- **Database**: MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+
- **Extensions**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **Composer**: 2.5+
- **Node.js**: 18+ (for asset compilation)

## 📦 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/mangoon.git
cd mangoon
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mangoon
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Seed the Database

```bash
php artisan db:seed --class=LanguageSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
```

### 6. Create Admin User

```bash
php artisan tinker
```

```php
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@mangoon.local',
    'password' => bcrypt('password'),
]);

$user->assignRole('admin');
```

### 7. Compile Assets

```bash
npm run build
# or for development
npm run dev
```

### 8. Start the Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

## ⚙️ Configuration

### Language Configuration

Add languages to support internationalization:

```php
use App\Models\Language;

Language::create([
    'code' => 'en',
    'name' => 'English',
    'native_name' => 'English',
    'is_active' => true,
    'is_default' => true,
]);

Language::create([
    'code' => 'it',
    'name' => 'Italian',
    'native_name' => 'Italiano',
    'is_active' => true,
]);

Language::create([
    'code' => 'ja',
    'name' => 'Japanese',
    'native_name' => '日本語',
    'is_active' => true,
]);
```

### Plugin Registration

Register plugins in `bootstrap/providers.php` or via the admin panel:

```php
use App\Models\Plugin;
use App\Plugins\MangaDexPlugin;

Plugin::registerFromClass(MangaDexPlugin::class);
```

## 🗄️ Database Schema

### Core Tables

- **languages**: Language definitions for i18n
- **mangas**: Main manga records with metadata
- **manga_translations**: Translated manga content (title, description)
- **manga_versions**: Relationships between manga versions
- **libraries**: Separate manga collections
- **library_translations**: Translated library information
- **categories**: Hierarchical manga categories
- **category_translations**: Translated category names
- **tags**: Flexible manga tagging
- **tag_translations**: Translated tag names
- **sources**: Manga sources (publishers, websites)
- **source_translations**: Translated source information

### ACL Tables

- **roles**: User roles (admin, editor, reader)
- **permissions**: Granular permissions (resource.action)
- **role_user**: User-role assignments (with library scope)
- **permission_role**: Role-permission assignments

### Plugin Tables

- **plugins**: Plugin registry and configuration

### Pivot Tables

- **manga_tag**: Manga-tag relationships
- **category_manga**: Manga-category relationships
- **library_manga**: Library-manga relationships (with featured flag)

## 🎯 Core Features

### Manga Management

#### Creating a Manga

```php
use App\Models\Manga;

$manga = Manga::create([
    'slug' => 'one-piece',
    'author' => 'Eiichiro Oda',
    'illustrator' => 'Eiichiro Oda',
    'publication_year' => 1997,
    'original_language' => 'ja',
    'status' => 'ongoing',
    'type' => 'manga',
    'is_active' => true,
]);

// Add translations
$manga->translations()->create([
    'language_code' => 'en',
    'title' => 'One Piece',
    'synopsis' => 'A story about pirates...',
    'description' => 'Full description here...',
]);

$manga->translations()->create([
    'language_code' => 'it',
    'title' => 'One Piece',
    'synopsis' => 'Una storia sui pirati...',
    'description' => 'Descrizione completa qui...',
]);
```

#### Managing Manga Versions

```php
use App\Models\MangaVersion;

// Link a translated version
$englishVersion = Manga::where('slug', 'one-piece-en')->first();
$japaneseVersion = Manga::where('slug', 'one-piece-jp')->first();

MangaVersion::create([
    'manga_id' => $japaneseVersion->id,
    'related_manga_id' => $englishVersion->id,
    'relationship_type' => 'translation',
    'language_code' => 'en',
    'is_primary' => true,
]);
```

#### Adding Tags and Categories

```php
// Create tags
$tag = Tag::create(['slug' => 'shonen']);
$tag->translations()->create([
    'language_code' => 'en',
    'name' => 'Shonen',
]);

// Attach to manga
$manga->tags()->attach($tag->id, ['sort_order' => 1]);

// Or use sync
$manga->tags()->sync([1, 2, 3]);
```

#### Querying Manga

```php
// Get active manga
$manga = Manga::active()->get();

// Get featured manga
$featured = Manga::featured()->get();

// Get popular manga
$popular = Manga::popular()->take(10)->get();

// Filter by status
$ongoing = Manga::byStatus('ongoing')->get();

// Filter by type
$manhwa = Manga::byType('manhwa')->get();

// With translations
$manga = Manga::with('translations')->find(1);
$title = $manga->getTitle('en'); // Get English title

// Search by translated title
$results = Manga::whereHas('translations', function ($query) {
    $query->where('title', 'like', '%One Piece%');
})->get();
```

### Internationalization (i18n)

#### Getting Translations

```php
// Get manga in specific language
$manga = Manga::find(1);
$translation = $manga->getTranslation('it');
$title = $manga->getTitle('it');
$synopsis = $manga->getSynopsis('it');

// With fallback to default language
app()->setLocale('it');
$title = $manga->getTitle(); // Returns Italian title or fallback
```

#### Working with Libraries

```php
use App\Models\Library;

$library = Library::create([
    'slug' => 'shonen',
    'icon' => 'heroicons.fire',
    'color' => '#FF5733',
    'is_active' => true,
    'is_public' => true,
    'sort_order' => 1,
]);

$library->translations()->create([
    'language_code' => 'en',
    'name' => 'Shonen',
    'description' => 'Action-packed manga for young audiences',
]);

$library->translations()->create([
    'language_code' => 'it',
    'name' => 'Shonen',
    'description' => 'Manga ricchi di azione per giovani lettori',
]);

// Add manga to library
$library->addManga($manga, [
    'is_featured' => true,
    'sort_order' => 1,
]);

// Get manga from library
$mangas = $library->mangas()->get();
$featuredMangas = $library->featuredMangas()->get();
```

### Plugin System

#### Using a Plugin

```php
use App\Models\Plugin;

// Get and instantiate plugin
$plugin = Plugin::where('slug', 'mangadex-plugin')->first();
$instance = $plugin->instantiate();

// Test plugin
$testResult = $plugin->test();

// Download manga
$result = $instance->download('https://mangadex.org/title/xxx');

if ($result['success']) {
    $manga = $result['manga'];
    echo "Downloaded: {$manga->slug}";
}

// Search for manga
$results = $instance->search('One Piece');

// Get chapters
$chapters = $instance->getChapters('https://mangadex.org/title/xxx');

// Download chapter
$chapterResult = $instance->downloadChapter('https://mangadex.org/chapter/xxx');
```

#### Plugin Configuration

```php
// Update plugin configuration
$plugin->updateConfig([
    'api_base_url' => 'https://api.mangadex.org',
    'preferred_language' => 'en',
    'download_quality' => 'data',
]);

// Activate/deactivate plugin
$plugin->activate();
$plugin->deactivate();

// Install/uninstall plugin
$plugin->install();
$plugin->uninstall();
```

### Access Control (ACL)

#### Setting Up Roles and Permissions

```php
use App\Models\Role;
use App\Models\Permission;

// Create system permissions
Permission::createSystemPermissions();

// Get or create roles
$admin = Role::admin();
$editor = Role::editor();
$reader = Role::reader();

// Assign permissions to roles
$admin->givePermissions([
    'manga.view',
    'manga.create',
    'manga.edit',
    'manga.delete',
    'library.manage',
    'plugin.configure',
]);

$editor->givePermissions([
    'manga.view',
    'manga.create',
    'manga.edit',
]);

$reader->givePermissions([
    'manga.view',
]);
```

#### Managing User Permissions

```php
use App\Models\User;

$user = User::find(1);

// Assign roles
$user->assignRole('editor');
$user->assignRole('admin', $libraryId); // Library-scoped role

// Check permissions
if ($user->hasRole('admin')) {
    // User is admin
}

if ($user->hasPermission('manga.edit')) {
    // User can edit manga
}

if ($user->can('edit', 'manga')) {
    // Alternative syntax
}

// Assign role with expiration
$user->assignRole('editor', null, [
    'expires_at' => now()->addDays(30),
]);

// Get all user permissions
$permissions = $user->getAllPermissions();

// Remove roles
$user->removeRole('editor');
$user->syncRoles(['reader']); // Remove all and assign reader
```

#### Library-Scoped Permissions

```php
$library = Library::where('slug', 'shonen')->first();

// Assign user as library manager
$user->assignRole('editor', $library->id);

// Check library-scoped permissions
if ($user->hasRole('editor', $library->id)) {
    // User can edit content in this library
}

// Check permission in specific library
if ($user->hasPermission('manga.edit', $library->id)) {
    // User can edit manga in this library
}
```

## 🔌 Plugin Development

### Creating a Custom Plugin

Create a new plugin by extending `AbstractPlugin`:

```php
namespace App\Plugins;

use App\Plugins\AbstractPlugin;
use App\Models\Manga;

class MyCustomPlugin extends AbstractPlugin
{
    protected string $name = 'My Custom Plugin';
    protected string $version = '1.0.0';
    protected string $description = 'Downloads manga from custom source';
    protected string $author = 'Your Name';
    protected string $type = 'downloader';
    protected array $supportedSources = [1, 2, 3]; // Source IDs

    public function getConfigSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'api_key' => [
                    'type' => 'string',
                    'description' => 'API key for authentication',
                ],
                'base_url' => [
                    'type' => 'string',
                    'default' => 'https://api.example.com',
                    'description' => 'Base API URL',
                ],
            ],
        ];
    }

    public function getDefaultConfig(): array
    {
        return [
            'enabled' => true,
            'api_key' => '',
            'base_url' => 'https://api.example.com',
        ];
    }

    public function download(string $url, array $options = []): array
    {
        try {
            $this->initialize();
            
            // Your download logic here
            $metadata = $this->parseMetadata($url);
            $manga = $this->createManga($metadata);
            
            $this->updateStatistics(true);
            
            return $this->successResponse('Download successful', [], $manga);
        } catch (\Exception $e) {
            $this->handleError($e);
            return $this->errorResponse('Download failed: ' . $e->getMessage());
        }
    }

    public function parseMetadata(string $url): array
    {
        // Parse metadata from URL
        return [
            'title' => 'Manga Title',
            'author' => 'Author Name',
            // ... more metadata
        ];
    }

    public function search(string $query, array $filters = []): array
    {
        // Implement search logic
        return [];
    }

    public function getChapters(string $url): array
    {
        // Get manga chapters
        return [];
    }

    public function downloadChapter(string $chapterUrl, array $options = []): array
    {
        // Download single chapter
        return $this->successResponse('Chapter downloaded', []);
    }
}
```

### Registering Your Plugin

```php
use App\Models\Plugin;
use App\Plugins\MyCustomPlugin;

Plugin::registerFromClass(MyCustomPlugin::class);
```

### Plugin Documentation

Your plugin should provide comprehensive documentation via the `getDocumentation()` method. This documentation will be displayed in the admin panel and should include:

- Installation instructions
- Configuration options
- Usage examples
- Troubleshooting tips
- API references
- Support information

## 📚 API Documentation

### REST API Endpoints (Coming Soon)

The system can be extended with REST API endpoints for:

- Manga CRUD operations
- Library management
- User authentication
- Plugin management
- Search and filtering

Example controller structure:

```php
// app/Http/Controllers/Api/MangaController.php
namespace App\Http\Controllers\Api;

use App\Models\Manga;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    public function index(Request $request)
    {
        $manga = Manga::active()
            ->with('translations')
            ->paginate($request->input('per_page', 20));
            
        return response()->json($manga);
    }
    
    public function show(Manga $manga)
    {
        $manga->load('translations', 'tags', 'categories', 'libraries');
        return response()->json($manga);
    }
    
    // ... more methods
}
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=MangaTest

# Run with coverage
php artisan test --coverage
```

### Example Test

```php
namespace Tests\Feature;

use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MangaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_manga(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/api/manga', [
            'slug' => 'test-manga',
            'author' => 'Test Author',
            'status' => 'ongoing',
            'type' => 'manga',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('mangas', ['slug' => 'test-manga']);
    }
}
```

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Style

- Follow PSR-12 coding standards
- Use Laravel best practices
- Write tests for new features
- Update documentation

### Running Code Style Checks

```bash
# Fix code style with Laravel Pint
vendor/bin/pint --format agent

# Run static analysis
vendor/bin/phpstan analyse
```

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework
- MangaDex API
- All contributors to this project

## 📞 Support

For support, please:

- Open an issue on GitHub
- Check the documentation
- Join our Discord community (coming soon)

## 🗺️ Roadmap

- [ ] REST API implementation
- [ ] GraphQL support
- [ ] Advanced search with Elasticsearch
- [ ] Reading progress tracking
- [ ] Recommendation system
- [ ] Mobile app (React Native)
- [ ] Docker support
- [ ] Kubernetes deployment configs
- [ ] More plugin examples (MangaPlus, Viz, etc.)
- [ ] WebSocket support for real-time updates
- [ ] Background job processing for downloads
- [ ] CDN integration
- [ ] Advanced analytics dashboard

## 📊 Performance

Mangoon is designed for performance:

- Database query optimization with eager loading
- Caching support (Redis, Memcached)
- Queue support for background jobs
- Image optimization
- CDN-ready architecture
- Pagination for large datasets

## 🔒 Security

Security features:

- Role-based access control
- Permission-based authorization
- SQL injection protection (Eloquent ORM)
- XSS protection
- CSRF protection
- Rate limiting
- Secure password hashing
- Two-factor authentication (planned)

---

**Built with ❤️ using Laravel 12**