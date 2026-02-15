<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'guard_name',
        'resource',
        'action',
        'scope',
        'is_active',
        'is_system',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withPivot('is_active', 'metadata')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active permissions.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include system permissions.
     */
    public function scopeSystem($query): void
    {
        $query->where('is_system', true);
    }

    /**
     * Scope a query to only include non-system permissions.
     */
    public function scopeNonSystem($query): void
    {
        $query->where('is_system', false);
    }

    /**
     * Scope a query to filter by guard name.
     */
    public function scopeByGuard($query, string $guardName): void
    {
        $query->where('guard_name', $guardName);
    }

    /**
     * Scope a query to filter by resource.
     */
    public function scopeByResource($query, string $resource): void
    {
        $query->where('resource', $resource);
    }

    /**
     * Scope a query to filter by action.
     */
    public function scopeByAction($query, string $action): void
    {
        $query->where('action', $action);
    }

    /**
     * Scope a query to filter by scope.
     */
    public function scopeByScope($query, string $scope): void
    {
        $query->where('scope', $scope);
    }

    /**
     * Scope a query to filter by resource and action.
     */
    public function scopeByResourceAndAction($query, string $resource, string $action): void
    {
        $query->where('resource', $resource)->where('action', $action);
    }

    /**
     * Check if this permission is assigned to a specific role.
     */
    public function hasRole(int|string|Role $role): bool
    {
        if ($role instanceof Role) {
            return $this->roles()->where('roles.id', $role->id)->exists();
        }

        if (is_numeric($role)) {
            return $this->roles()->where('roles.id', $role)->exists();
        }

        return $this->roles()->where('roles.slug', $role)->exists();
    }

    /**
     * Assign this permission to a role.
     */
    public function assignToRole(int|string|Role $role, array $attributes = []): bool
    {
        $roleModel = $this->resolveRole($role);

        if (! $roleModel) {
            return false;
        }

        if ($this->hasRole($roleModel)) {
            return true;
        }

        $this->roles()->attach($roleModel->id, array_merge([
            'is_active' => true,
        ], $attributes));

        return true;
    }

    /**
     * Assign this permission to multiple roles.
     */
    public function assignToRoles(array $roles): bool
    {
        $success = true;

        foreach ($roles as $role) {
            if (! $this->assignToRole($role)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Remove this permission from a role.
     */
    public function removeFromRole(int|string|Role $role): bool
    {
        $roleModel = $this->resolveRole($role);

        if (! $roleModel) {
            return false;
        }

        $this->roles()->detach($roleModel->id);

        return true;
    }

    /**
     * Remove this permission from multiple roles.
     */
    public function removeFromRoles(array $roles): bool
    {
        $success = true;

        foreach ($roles as $role) {
            if (! $this->removeFromRole($role)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Sync roles for this permission.
     */
    public function syncRoles(array $roles): void
    {
        $roleIds = [];

        foreach ($roles as $role) {
            $roleModel = $this->resolveRole($role);
            if ($roleModel) {
                $roleIds[] = $roleModel->id;
            }
        }

        $this->roles()->sync($roleIds);
    }

    /**
     * Get all active roles for this permission.
     */
    public function activeRoles(): BelongsToMany
    {
        return $this->roles()->wherePivot('is_active', true);
    }

    /**
     * Get role count for this permission.
     */
    public function getRoleCount(): int
    {
        return $this->roles()->count();
    }

    /**
     * Check if this is a global scope permission.
     */
    public function isGlobal(): bool
    {
        return $this->scope === 'global';
    }

    /**
     * Check if this is a library scope permission.
     */
    public function isLibraryScoped(): bool
    {
        return $this->scope === 'library';
    }

    /**
     * Check if this is an own scope permission.
     */
    public function isOwnScoped(): bool
    {
        return $this->scope === 'own';
    }

    /**
     * Get the full permission identifier.
     */
    public function getIdentifier(): string
    {
        if ($this->resource && $this->action) {
            return "{$this->resource}.{$this->action}";
        }

        return $this->slug;
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Resolve a role from various input types.
     */
    protected function resolveRole(int|string|Role $role): ?Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        if (is_numeric($role)) {
            return Role::find($role);
        }

        return Role::where('slug', $role)->first();
    }

    /**
     * Create or get a permission by resource and action.
     */
    public static function createOrGet(
        string $resource,
        string $action,
        string $scope = 'global',
        array $attributes = []
    ): self {
        $slug = "{$resource}.{$action}";
        $name = ucfirst($action).' '.ucfirst($resource);

        return static::firstOrCreate(
            ['slug' => $slug],
            array_merge([
                'name' => $name,
                'description' => "Permission to {$action} {$resource}",
                'guard_name' => 'web',
                'resource' => $resource,
                'action' => $action,
                'scope' => $scope,
                'is_active' => true,
                'is_system' => false,
            ], $attributes)
        );
    }

    /**
     * Create standard CRUD permissions for a resource.
     */
    public static function createCrudPermissions(string $resource, string $scope = 'global'): array
    {
        $actions = ['view', 'create', 'edit', 'delete'];
        $permissions = [];

        foreach ($actions as $action) {
            $permissions[$action] = static::createOrGet($resource, $action, $scope);
        }

        return $permissions;
    }

    /**
     * Create manga management permissions.
     */
    public static function createMangaPermissions(): array
    {
        return [
            'view' => static::createOrGet('manga', 'view', 'global', ['is_system' => true]),
            'create' => static::createOrGet('manga', 'create', 'global', ['is_system' => true]),
            'edit' => static::createOrGet('manga', 'edit', 'global', ['is_system' => true]),
            'delete' => static::createOrGet('manga', 'delete', 'global', ['is_system' => true]),
            'download' => static::createOrGet('manga', 'download', 'global', ['is_system' => true]),
            'publish' => static::createOrGet('manga', 'publish', 'global', ['is_system' => true]),
        ];
    }

    /**
     * Create library management permissions.
     */
    public static function createLibraryPermissions(): array
    {
        return [
            'view' => static::createOrGet('library', 'view', 'global', ['is_system' => true]),
            'create' => static::createOrGet('library', 'create', 'global', ['is_system' => true]),
            'edit' => static::createOrGet('library', 'edit', 'library', ['is_system' => true]),
            'delete' => static::createOrGet('library', 'delete', 'global', ['is_system' => true]),
            'manage' => static::createOrGet('library', 'manage', 'library', ['is_system' => true]),
        ];
    }

    /**
     * Create plugin management permissions.
     */
    public static function createPluginPermissions(): array
    {
        return [
            'view' => static::createOrGet('plugin', 'view', 'global', ['is_system' => true]),
            'install' => static::createOrGet('plugin', 'install', 'global', ['is_system' => true]),
            'configure' => static::createOrGet('plugin', 'configure', 'global', ['is_system' => true]),
            'activate' => static::createOrGet('plugin', 'activate', 'global', ['is_system' => true]),
            'delete' => static::createOrGet('plugin', 'delete', 'global', ['is_system' => true]),
        ];
    }

    /**
     * Create user management permissions.
     */
    public static function createUserPermissions(): array
    {
        return [
            'view' => static::createOrGet('user', 'view', 'global', ['is_system' => true]),
            'create' => static::createOrGet('user', 'create', 'global', ['is_system' => true]),
            'edit' => static::createOrGet('user', 'edit', 'global', ['is_system' => true]),
            'delete' => static::createOrGet('user', 'delete', 'global', ['is_system' => true]),
            'assign-roles' => static::createOrGet('user', 'assign-roles', 'global', ['is_system' => true]),
        ];
    }

    /**
     * Create all system permissions.
     */
    public static function createSystemPermissions(): array
    {
        return array_merge(
            static::createMangaPermissions(),
            static::createLibraryPermissions(),
            static::createPluginPermissions(),
            static::createUserPermissions()
        );
    }
}
