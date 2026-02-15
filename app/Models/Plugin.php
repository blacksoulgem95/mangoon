<?php

namespace App\Models;

use App\Contracts\PluginInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plugin extends Model
{
    /** @use HasFactory<\Database\Factories\PluginFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'class_name',
        'version',
        'description',
        'documentation',
        'author',
        'author_url',
        'icon',
        'config_schema',
        'config_values',
        'default_config',
        'is_active',
        'is_installed',
        'is_system',
        'type',
        'requires_php',
        'requires_laravel',
        'dependencies',
        'downloads_count',
        'last_used_at',
        'installed_at',
        'priority',
        'rate_limit',
        'concurrent_limit',
        'metadata',
        'supported_sources',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config_schema' => 'array',
            'config_values' => 'array',
            'default_config' => 'array',
            'is_active' => 'boolean',
            'is_installed' => 'boolean',
            'is_system' => 'boolean',
            'dependencies' => 'array',
            'downloads_count' => 'integer',
            'last_used_at' => 'datetime',
            'installed_at' => 'datetime',
            'priority' => 'integer',
            'rate_limit' => 'integer',
            'concurrent_limit' => 'integer',
            'metadata' => 'array',
            'supported_sources' => 'array',
        ];
    }

    /**
     * Scope a query to only include active plugins.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include installed plugins.
     */
    public function scopeInstalled($query): void
    {
        $query->where('is_installed', true);
    }

    /**
     * Scope a query to only include system plugins.
     */
    public function scopeSystem($query): void
    {
        $query->where('is_system', true);
    }

    /**
     * Scope a query to only include non-system plugins.
     */
    public function scopeNonSystem($query): void
    {
        $query->where('is_system', false);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Scope a query to order by priority.
     */
    public function scopeByPriority($query): void
    {
        $query->orderByDesc('priority');
    }

    /**
     * Scope a query to only include downloaders.
     */
    public function scopeDownloaders($query): void
    {
        $query->where('type', 'downloader');
    }

    /**
     * Scope a query to only include available plugins (installed and active).
     */
    public function scopeAvailable($query): void
    {
        $query->where('is_installed', true)->where('is_active', true);
    }

    /**
     * Get sources that this plugin supports.
     */
    public function sources(): \Illuminate\Database\Eloquent\Collection
    {
        if (empty($this->supported_sources)) {
            return collect([]);
        }

        return Source::whereIn('id', $this->supported_sources)->get();
    }

    /**
     * Check if plugin supports a specific source.
     */
    public function supportsSource(int|string $sourceIdOrSlug): bool
    {
        if (empty($this->supported_sources)) {
            return false;
        }

        if (is_numeric($sourceIdOrSlug)) {
            return in_array($sourceIdOrSlug, $this->supported_sources, true);
        }

        $source = Source::where('slug', $sourceIdOrSlug)->first();

        return $source && in_array($source->id, $this->supported_sources, true);
    }

    /**
     * Instantiate the plugin class.
     */
    public function instantiate(): ?PluginInterface
    {
        try {
            if (! class_exists($this->class_name)) {
                return null;
            }

            $instance = new $this->class_name($this->config_values ?? []);

            if (! $instance instanceof PluginInterface) {
                return null;
            }

            return $instance;
        } catch (\Exception $e) {
            \Log::error("Failed to instantiate plugin {$this->name}: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Activate the plugin.
     */
    public function activate(): bool
    {
        if (! $this->is_installed) {
            return false;
        }

        $this->is_active = true;

        return $this->save();
    }

    /**
     * Deactivate the plugin.
     */
    public function deactivate(): bool
    {
        $this->is_active = false;

        return $this->save();
    }

    /**
     * Install the plugin.
     */
    public function install(): bool
    {
        if ($this->is_installed) {
            return true;
        }

        try {
            $instance = $this->instantiate();

            if (! $instance) {
                return false;
            }

            $instance->initialize();

            $this->is_installed = true;
            $this->installed_at = now();

            return $this->save();
        } catch (\Exception $e) {
            \Log::error("Failed to install plugin {$this->name}: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Uninstall the plugin.
     */
    public function uninstall(): bool
    {
        if ($this->is_system) {
            return false;
        }

        try {
            $instance = $this->instantiate();

            if ($instance) {
                $instance->cleanup();
            }

            $this->is_installed = false;
            $this->is_active = false;
            $this->installed_at = null;

            return $this->save();
        } catch (\Exception $e) {
            \Log::error("Failed to uninstall plugin {$this->name}: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Test the plugin functionality.
     */
    public function test(): array
    {
        try {
            $instance = $this->instantiate();

            if (! $instance) {
                return [
                    'success' => false,
                    'message' => 'Failed to instantiate plugin',
                ];
            }

            return $instance->test();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Plugin test failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Update plugin configuration.
     */
    public function updateConfig(array $config): bool
    {
        try {
            $instance = $this->instantiate();

            if ($instance) {
                $instance->validateConfig($config);
            }

            $this->config_values = array_merge($this->config_values ?? [], $config);

            return $this->save();
        } catch (\Exception $e) {
            \Log::error("Failed to update plugin config: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Increment the downloads count.
     */
    public function incrementDownloads(int $count = 1): bool
    {
        $this->last_used_at = now();

        return $this->increment('downloads_count', $count);
    }

    /**
     * Check if the plugin is ready to use.
     */
    public function isReady(): bool
    {
        if (! $this->is_active || ! $this->is_installed) {
            return false;
        }

        $instance = $this->instantiate();

        return $instance && $instance->isReady();
    }

    /**
     * Get plugin statistics.
     */
    public function getStatistics(): array
    {
        $baseStats = [
            'downloads_count' => $this->downloads_count,
            'last_used_at' => $this->last_used_at?->toISOString(),
            'installed_at' => $this->installed_at?->toISOString(),
        ];

        $instance = $this->instantiate();

        if ($instance) {
            return array_merge($baseStats, $instance->getStatistics());
        }

        return $baseStats;
    }

    /**
     * Check if plugin dependencies are met.
     */
    public function dependenciesMet(): bool
    {
        if (empty($this->dependencies)) {
            return true;
        }

        foreach ($this->dependencies as $dependencySlug) {
            $dependency = static::where('slug', $dependencySlug)
                ->where('is_installed', true)
                ->where('is_active', true)
                ->exists();

            if (! $dependency) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if PHP version requirement is met.
     */
    public function phpVersionMet(): bool
    {
        if (! $this->requires_php) {
            return true;
        }

        return version_compare(PHP_VERSION, $this->requires_php, '>=');
    }

    /**
     * Check if Laravel version requirement is met.
     */
    public function laravelVersionMet(): bool
    {
        if (! $this->requires_laravel) {
            return true;
        }

        return version_compare(app()->version(), $this->requires_laravel, '>=');
    }

    /**
     * Check if all requirements are met.
     */
    public function requirementsMet(): bool
    {
        return $this->phpVersionMet() && $this->laravelVersionMet() && $this->dependenciesMet();
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Register a plugin from a class.
     */
    public static function registerFromClass(string $className): ?self
    {
        try {
            if (! class_exists($className)) {
                return null;
            }

            $instance = new $className;

            if (! $instance instanceof PluginInterface) {
                return null;
            }

            $slug = \Str::slug($instance->getName());

            $plugin = static::updateOrCreate(
                ['class_name' => $className],
                [
                    'name' => $instance->getName(),
                    'slug' => $slug,
                    'version' => $instance->getVersion(),
                    'description' => $instance->getDescription(),
                    'documentation' => $instance->getDocumentation(),
                    'author' => $instance->getAuthor(),
                    'author_url' => $instance->getAuthorUrl(),
                    'icon' => $instance->getIcon(),
                    'type' => $instance->getType(),
                    'config_schema' => $instance->getConfigSchema(),
                    'default_config' => $instance->getDefaultConfig(),
                    'requires_php' => $instance->getRequiredPhpVersion(),
                    'requires_laravel' => $instance->getRequiredLaravelVersion(),
                    'dependencies' => $instance->getDependencies(),
                    'priority' => $instance->getPriority(),
                    'rate_limit' => $instance->getRateLimit(),
                    'concurrent_limit' => $instance->getConcurrentLimit(),
                    'supported_sources' => $instance->getSupportedSources(),
                ]
            );

            return $plugin;
        } catch (\Exception $e) {
            \Log::error("Failed to register plugin from class {$className}: {$e->getMessage()}");

            return null;
        }
    }
}
