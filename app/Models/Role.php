<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "slug",
        "description",
        "guard_name",
        "level",
        "is_active",
        "is_system",
        "metadata",
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "level" => "integer",
            "is_active" => "boolean",
            "is_system" => "boolean",
            "metadata" => "array",
        ];
    }

    /**
     * Get the users that belong to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "role_user")
            ->withPivot("library_id", "expires_at", "is_active", "metadata")
            ->withTimestamps();
    }

    /**
     * Get the permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, "permission_role")
            ->withPivot("is_active", "metadata")
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query): void
    {
        $query->where("is_active", true);
    }

    /**
     * Scope a query to only include system roles.
     */
    public function scopeSystem($query): void
    {
        $query->where("is_system", true);
    }

    /**
     * Scope a query to only include non-system roles.
     */
    public function scopeNonSystem($query): void
    {
        $query->where("is_system", false);
    }

    /**
     * Scope a query to filter by guard name.
     */
    public function scopeByGuard($query, string $guardName): void
    {
        $query->where("guard_name", $guardName);
    }

    /**
     * Scope a query to order by level.
     */
    public function scopeByLevel($query): void
    {
        $query->orderByDesc("level");
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(int|string|Permission $permission): bool
    {
        if ($permission instanceof Permission) {
            return $this->permissions()
                ->where("permissions.id", $permission->id)
                ->exists();
        }

        if (is_numeric($permission)) {
            return $this->permissions()
                ->where("permissions.id", $permission)
                ->exists();
        }

        return $this->permissions()
            ->where("permissions.slug", $permission)
            ->exists();
    }

    /**
     * Give a permission to the role.
     */
    public function givePermissionTo(
        int|string|Permission $permission,
        array $attributes = [],
    ): bool {
        $permissionModel = $this->resolvePermission($permission);

        if (!$permissionModel) {
            return false;
        }

        if ($this->hasPermission($permissionModel)) {
            return true;
        }

        $this->permissions()->attach(
            $permissionModel->id,
            array_merge(
                [
                    "is_active" => true,
                ],
                $attributes,
            ),
        );

        return true;
    }

    /**
     * Assign multiple permissions to the role.
     */
    public function assignPermissions(array $permissions): bool
    {
        $success = true;
        foreach ($permissions as $permission) {
            if (!$this->givePermissionTo($permission)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Revoke a permission from the role.
     */
    public function revokePermissionFrom(
        int|string|Permission $permission,
    ): bool {
        $permissionModel = $this->resolvePermission($permission);

        if (!$permissionModel) {
            return false;
        }

        $this->permissions()->detach($permissionModel->id);

        return true;
    }

    /**
     * Remove multiple permissions from the role.
     */
    public function removePermissions(array $permissions): bool
    {
        $success = true;
        foreach ($permissions as $permission) {
            if (!$this->revokePermissionFrom($permission)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Sync permissions for the role.
     */
    public function syncPermissions(array $permissions): void
    {
        $permissionIds = [];

        foreach ($permissions as $permission) {
            $permissionModel = $this->resolvePermission($permission);
            if ($permissionModel) {
                $permissionIds[] = $permissionModel->id;
            }
        }

        $this->permissions()->sync($permissionIds);
    }

    /**
     * Get all active permissions for this role.
     */
    public function activePermissions(): BelongsToMany
    {
        return $this->permissions()->wherePivot("is_active", true);
    }

    /**
     * Get permission count for this role.
     */
    public function getPermissionCount(): int
    {
        return $this->permissions()->count();
    }

    /**
     * Resolve a permission from various input types.
     */
    protected function resolvePermission(
        int|string|Permission $permission,
    ): ?Permission {
        if ($permission instanceof Permission) {
            return $permission;
        }

        if (is_numeric($permission)) {
            return Permission::find($permission);
        }

        return Permission::where("slug", $permission)->first();
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return "slug";
    }

    /**
     * Create or get the admin role.
     */
    public static function createAdminRole(): self
    {
        $role = static::firstOrCreate(
            ["slug" => "admin"],
            [
                "name" => "Administrator",
                "description" => "Has all permissions.",
                "guard_name" => "web",
                "level" => 100,
                "is_active" => true,
                "is_system" => true,
            ],
        );

        // Assign all system permissions
        $permissions = Permission::where("is_system", true)->pluck("id");
        $role->permissions()->sync($permissions);

        return $role;
    }

    /**
     * Create or get the member role.
     */
    public static function createMemberRole(): self
    {
        return static::firstOrCreate(
            ["slug" => "member"],
            [
                "name" => "Member",
                "description" => "Default role for registered users.",
                "guard_name" => "web",
                "level" => 10,
                "is_active" => true,
                "is_system" => true,
            ],
        );
    }

    /**
     * Create all system roles.
     */
    public static function createSystemRoles(): array
    {
        return [
            "admin" => static::createAdminRole(),
            "member" => static::createMemberRole(),
        ];
    }
}
