<?php

namespace App\Services;

use App\Contracts\PluginInterface;
use App\Models\Plugin;
use App\Models\Source;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Plugin Manager Service
 *
 * Manages the lifecycle and execution of manga download plugins.
 */
class PluginManager
{
    /**
     * Cache key for active plugins.
     */
    protected const CACHE_KEY_ACTIVE_PLUGINS = 'mangoon.plugins.active';

    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Registered plugin instances.
     *
     * @var array<string, PluginInterface>
     */
    protected array $instances = [];

    /**
     * Get all available plugins.
     */
    public function all(): Collection
    {
        return Plugin::all();
    }

    /**
     * Get all active and installed plugins.
     */
    public function active(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE_PLUGINS, self::CACHE_TTL, function () {
            return Plugin::available()->byPriority()->get();
        });
    }

    /**
     * Get plugin by slug.
     */
    public function find(string $slug): ?Plugin
    {
        return Plugin::where('slug', $slug)->first();
    }

    /**
     * Get plugin by ID.
     */
    public function findById(int $id): ?Plugin
    {
        return Plugin::find($id);
    }

    /**
     * Get or create plugin instance.
     */
    public function getInstance(Plugin $plugin): ?PluginInterface
    {
        $slug = $plugin->slug;

        if (isset($this->instances[$slug])) {
            return $this->instances[$slug];
        }

        $instance = $plugin->instantiate();

        if ($instance) {
            $this->instances[$slug] = $instance;
        }

        return $instance;
    }

    /**
     * Register a plugin from a class name.
     */
    public function register(string $className): ?Plugin
    {
        try {
            if (! class_exists($className)) {
                Log::warning("Plugin class not found: {$className}");

                return null;
            }

            $plugin = Plugin::registerFromClass($className);

            if ($plugin) {
                $this->clearCache();
                Log::info("Plugin registered successfully: {$plugin->name}");
            }

            return $plugin;
        } catch (\Exception $e) {
            Log::error("Failed to register plugin: {$e->getMessage()}", [
                'class' => $className,
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * Register multiple plugins.
     */
    public function registerMany(array $classNames): array
    {
        $registered = [];

        foreach ($classNames as $className) {
            $plugin = $this->register($className);
            if ($plugin) {
                $registered[] = $plugin;
            }
        }

        return $registered;
    }

    /**
     * Install a plugin.
     */
    public function install(Plugin $plugin): bool
    {
        try {
            if ($plugin->is_installed) {
                Log::info("Plugin already installed: {$plugin->name}");

                return true;
            }

            if (! $plugin->requirementsMet()) {
                Log::error("Plugin requirements not met: {$plugin->name}");

                return false;
            }

            $success = $plugin->install();

            if ($success) {
                $this->clearCache();
                Log::info("Plugin installed successfully: {$plugin->name}");
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Failed to install plugin: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Uninstall a plugin.
     */
    public function uninstall(Plugin $plugin): bool
    {
        try {
            if ($plugin->is_system) {
                Log::warning("Cannot uninstall system plugin: {$plugin->name}");

                return false;
            }

            $success = $plugin->uninstall();

            if ($success) {
                $this->clearCache();
                Log::info("Plugin uninstalled successfully: {$plugin->name}");
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Failed to uninstall plugin: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Activate a plugin.
     */
    public function activate(Plugin $plugin): bool
    {
        try {
            $success = $plugin->activate();

            if ($success) {
                $this->clearCache();
                Log::info("Plugin activated: {$plugin->name}");
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Failed to activate plugin: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Deactivate a plugin.
     */
    public function deactivate(Plugin $plugin): bool
    {
        try {
            $success = $plugin->deactivate();

            if ($success) {
                $this->clearCache();
                Log::info("Plugin deactivated: {$plugin->name}");
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Failed to deactivate plugin: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Test a plugin.
     */
    public function test(Plugin $plugin): array
    {
        try {
            return $plugin->test();
        } catch (\Exception $e) {
            Log::error("Plugin test failed: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Plugin test failed: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Update plugin configuration.
     */
    public function updateConfig(Plugin $plugin, array $config): bool
    {
        try {
            $success = $plugin->updateConfig($config);

            if ($success) {
                Log::info("Plugin configuration updated: {$plugin->name}");
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Failed to update plugin config: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Find plugins that support a specific source.
     */
    public function findBySource(Source|int $source): Collection
    {
        $sourceId = $source instanceof Source ? $source->id : $source;

        return Plugin::available()
            ->get()
            ->filter(function ($plugin) use ($sourceId) {
                return $plugin->supportsSource($sourceId);
            });
    }

    /**
     * Get the best plugin for a source (highest priority).
     */
    public function getBestForSource(Source|int $source): ?Plugin
    {
        return $this->findBySource($source)
            ->sortByDesc('priority')
            ->first();
    }

    /**
     * Download manga using appropriate plugin.
     */
    public function downloadManga(string $url, ?Plugin $plugin = null, array $options = []): array
    {
        try {
            // If no plugin specified, try to find the best one
            if (! $plugin) {
                $plugin = $this->findPluginForUrl($url);
            }

            if (! $plugin) {
                return [
                    'success' => false,
                    'message' => 'No suitable plugin found for the URL',
                    'data' => [],
                    'manga' => null,
                ];
            }

            if (! $plugin->isReady()) {
                return [
                    'success' => false,
                    'message' => "Plugin '{$plugin->name}' is not ready",
                    'data' => [],
                    'manga' => null,
                ];
            }

            $instance = $this->getInstance($plugin);

            if (! $instance) {
                return [
                    'success' => false,
                    'message' => "Failed to instantiate plugin '{$plugin->name}'",
                    'data' => [],
                    'manga' => null,
                ];
            }

            $result = $instance->download($url, $options);

            if ($result['success']) {
                $plugin->incrementDownloads();
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Download failed: {$e->getMessage()}", [
                'url' => $url,
                'plugin' => $plugin?->slug,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Download failed: '.$e->getMessage(),
                'data' => [],
                'manga' => null,
            ];
        }
    }

    /**
     * Search manga using a plugin.
     */
    public function search(Plugin $plugin, string $query, array $filters = []): array
    {
        try {
            if (! $plugin->isReady()) {
                return [];
            }

            $instance = $this->getInstance($plugin);

            if (! $instance) {
                return [];
            }

            return $instance->search($query, $filters);
        } catch (\Exception $e) {
            Log::error("Search failed: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'query' => $query,
                'exception' => $e,
            ]);

            return [];
        }
    }

    /**
     * Get statistics for all plugins.
     */
    public function getStatistics(): array
    {
        $stats = [
            'total_plugins' => Plugin::count(),
            'active_plugins' => Plugin::active()->count(),
            'installed_plugins' => Plugin::installed()->count(),
            'total_downloads' => Plugin::sum('downloads_count'),
            'plugins' => [],
        ];

        foreach (Plugin::all() as $plugin) {
            $stats['plugins'][$plugin->slug] = $plugin->getStatistics();
        }

        return $stats;
    }

    /**
     * Find plugin that can handle a URL.
     */
    protected function findPluginForUrl(string $url): ?Plugin
    {
        $activePlugins = $this->active();

        foreach ($activePlugins as $plugin) {
            $instance = $this->getInstance($plugin);

            if (! $instance) {
                continue;
            }

            // Try to parse metadata - if it succeeds, this plugin can handle the URL
            try {
                $metadata = $instance->parseMetadata($url);
                if (! empty($metadata)) {
                    return $plugin;
                }
            } catch (\Exception $e) {
                // This plugin can't handle the URL, continue to next
                continue;
            }
        }

        return null;
    }

    /**
     * Clear plugin cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE_PLUGINS);
    }

    /**
     * Discover and register plugins from a directory.
     */
    public function discover(?string $directory = null): array
    {
        $directory = $directory ?? app_path('Plugins');

        if (! is_dir($directory)) {
            return [];
        }

        $registered = [];
        $files = glob($directory.'/*.php');

        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);

            if ($className) {
                $plugin = $this->register($className);
                if ($plugin) {
                    $registered[] = $plugin;
                }
            }
        }

        return $registered;
    }

    /**
     * Get class name from file path.
     */
    protected function getClassNameFromFile(string $file): ?string
    {
        $basename = basename($file, '.php');
        $namespace = 'App\\Plugins';

        $className = "{$namespace}\\{$basename}";

        return class_exists($className) ? $className : null;
    }

    /**
     * Get plugin types.
     */
    public function getTypes(): Collection
    {
        return Plugin::select('type')
            ->distinct()
            ->pluck('type');
    }

    /**
     * Get plugins by type.
     */
    public function getByType(string $type): Collection
    {
        return Plugin::byType($type)->get();
    }

    /**
     * Check if any plugin supports a source.
     */
    public function hasPluginForSource(Source|int $source): bool
    {
        return $this->findBySource($source)->isNotEmpty();
    }

    /**
     * Validate plugin configuration.
     */
    public function validateConfig(Plugin $plugin, array $config): bool
    {
        try {
            $instance = $this->getInstance($plugin);

            if (! $instance) {
                return false;
            }

            return $instance->validateConfig($config);
        } catch (\Exception $e) {
            Log::error("Plugin config validation failed: {$e->getMessage()}", [
                'plugin' => $plugin->slug,
                'exception' => $e,
            ]);

            return false;
        }
    }
}
