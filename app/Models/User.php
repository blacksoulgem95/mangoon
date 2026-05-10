<?php

namespace App\Models;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = ["name", "email", "password", "is_root"];

    protected $hidden = ["password", "remember_token"];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ["password", "remember_token"];

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
            "is_root" => "boolean",
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'is_root' => $this->is_root,
            'email' => $this->email,
        ];
    }

    /**
     * Get the roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, "role_user")
            ->withPivot("library_id", "expires_at", "is_active", "metadata")
            ->withTimestamps();
    }

    public function oauthAccounts(): BelongsToMany
    {
        return $this->belongsToMany(OauthAccount::class)->withPivot([
            'token',
            'refresh_token',
            'expires_at',
            'additional_data'
        ]);
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class, "library_id");
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(Role|int|string $role, ?int $libraryId = null): bool
    {
        $query = $this->roles()->wherePivot("is_active", true);

        if ($libraryId !== null) {
            $query->wherePivot("library_id", $libraryId);
        }

        if ($role instanceof Role) {
            return $query->where("roles.id", $role->id)->exists();
        }

        if (is_int($role) || ctype_digit($role)) {
            return $query->where("roles.id", $role)->exists();
        }

        return $query->where("roles.slug", $role)->exists();
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param  array<int, \App\Models\Role|int|string>  $roles
     * @param  int|null  $libraryId
     */
    public function hasAnyRole(array $roles, ?int $libraryId = null): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role, $libraryId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given roles.
     *
     * @param  array<int, \App\Models\Role|int|string>  $roles
     * @param  int|null  $libraryId
     */
    public function hasAllRoles(array $roles, ?int $libraryId = null): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role, $libraryId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(
        int|string|Permission $permission,
        ?int $libraryId = null,
    ): bool {
        $rolesQuery = $this->roles()->wherePivot("is_active", true);

        if ($libraryId !== null) {
            $rolesQuery->wherePivot("library_id", $libraryId);
        }

        return $rolesQuery
            ->whereHas("permissions", function ($query) use ($permission) {
                if ($permission instanceof Permission) {
                    $query->where("permissions.id", $permission->id);
                } elseif (is_int($permission)) {
                    $query->where("permissions.id", $permission);
                } else {
                    $query->where("permissions.slug", $permission);
                }
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param  array<int, \App\Models\Permission|int|string>  $permissions
     * @param  int|null  $libraryId
     */
    public function hasAnyPermission(
        array $permissions,
        ?int $libraryId = null,
    ): bool {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission, $libraryId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions.
     *
     * @param  array<int, \App\Models\Permission|int|string>  $permissions
     * @param  int|null  $libraryId
     */
    public function hasAllPermissions(
        array $permissions,
        ?int $libraryId = null,
    ): bool {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission, $libraryId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign a role to the user.
     *
     * @param  \App\Models\Role|int|string  $role
     * @param  int|null  $libraryId
     * @param  array<string, mixed>  $attributes
     */
    public function assignRole(
        Role|int|string $role,
        ?int $libraryId = null,
        array $attributes = [],
    ): bool {
        $roleModel = $this->resolveRole($role);

        if (!$roleModel) {
            return false;
        }

        // Check if the role is already assigned for this library (or globally if no libraryId)
        $existingRole = $this->roles()
            ->wherePivot("role_id", $roleModel->id)
            ->when($libraryId !== null, function ($q) use ($libraryId) {
                $q->wherePivot("library_id", $libraryId);
            })
            ->first();

        if ($existingRole) {
            // If it exists, update the attributes if provided, otherwise do nothing
            if (!empty($attributes)) {
                $this->roles()->updateExistingPivot(
                    $roleModel->id,
                    array_merge(
                        [
                            "library_id" => $libraryId,
                            "is_active" => true,
                            "expires_at" => null,
                        ],
                        $attributes,
                    ),
                );
            }
            return true; // Role already assigned
        }

        $this->roles()->attach(
            $roleModel->id,
            array_merge(
                [
                    "library_id" => $libraryId,
                    "is_active" => true,
                    "expires_at" => null,
                ],
                $attributes,
            ),
        );

        return true;
    }

    /**
     * Assign multiple roles to the user.
     *
     * @param  array<int, \App\Models\Role|int|string>  $roles
     * @param  int|null  $libraryId
     */
    public function assignRoles(array $roles, ?int $libraryId = null): bool
    {
        $success = true;

        foreach ($roles as $role) {
            if (!$this->assignRole($role, $libraryId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(
        Role|int|string $role,
        ?int $libraryId = null,
    ): bool {
        $roleModel = $this->resolveRole($role);

        if (!$roleModel) {
            return false;
        }

        $this->roles()->detach($roleModel->id, $libraryId); // Pass libraryId directly to detach

        return true;
    }

    /**
     * Remove multiple roles from the user.
     *
     * @param  array<int, \App\Models\Role|int|string>  $roles
     * @param  int|null  $libraryId
     */
    public function removeRoles(array $roles, ?int $libraryId = null): bool
    {
        foreach ($roles as $role) {
            $this->removeRole($role, $libraryId);
        }

        return true;
    }

    /**
     * Sync roles for the user.
     *
     * @param  array<int, \App\Models\Role|int|string>  $roles
     * @param  int|null  $libraryId
     */
    public function syncRoles(array $roles, ?int $libraryId = null): void
    {
        $roleIds = [];
        foreach ($roles as $role) {
            $roleModel = $this->resolveRole($role);
            if ($roleModel) {
                $roleIds[] = $roleModel->id;
            }
        }

        // Detach existing roles for the given library scope first
        if ($libraryId !== null) {
            $this->roles()->detach(null, $libraryId);
        } else {
            // If no libraryId, consider detaching all roles (use with caution)
            // or implement a more specific logic if needed.
            // For now, we assume sync is always library-specific or global.
            // If syncing globally, you might detach all roles and then re-attach.
            // This part might need more specific requirements.
        }

        // Attach the new roles, ensuring correct library_id and other pivot data
        $syncData = [];
        foreach ($roleIds as $roleId) {
            $syncData[$roleId] = [
                "library_id" => $libraryId,
                "is_active" => true, // Defaulting to active, can be adjusted if attributes are passed
                "expires_at" => null,
                // Add other default pivot attributes if necessary
            ];
        }
        $this->roles()->sync($syncData);
    }

    /**
     * Get all permissions for the user (through roles).
     */
    public function getAllPermissions(
        ?int $libraryId = null,
    ): \Illuminate\Support\Collection {
        // Fetch roles associated with the user, filtered by library_id and is_active pivot attribute.
        $roles = $this->roles()
            ->with("permissions")
            ->wherePivot("is_active", true)
            ->when($libraryId !== null, function ($query) use ($libraryId) {
                $query->wherePivot("library_id", $libraryId);
            })
            ->get();

        return $roles->pluck("permissions")->flatten()->unique("id")->values();
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole("admin"); // Assumes 'admin' is a role slug
    }

    /**
     * Check if user is an editor.
     */
    public function isEditor(): bool
    {
        return $this->hasRole("editor"); // Assumes 'editor' is a role slug
    }

    /**
     * Check if user is a reader.
     */
    public function isReader(): bool
    {
        return $this->hasRole("reader"); // Assumes 'reader' is a role slug
    }

    /**
     * Get active roles for the user.
     */
    public function activeRoles(?int $libraryId = null): BelongsToMany
    {
        $query = $this->roles()->wherePivot("is_active", true);

        if ($libraryId !== null) {
            $query->wherePivot("library_id", $libraryId);
        }

        return $query;
    }

    /**
     * Check if user can perform an action on a resource.
     */
    public function canPerform(
        string $action,
        string $resource,
        ?int $libraryId = null,
    ): bool {
        // It's assumed that Permission::byResourceAndAction exists and is correctly implemented
        // to find a permission based on resource and action.
        $permission = Permission::query()
            ->where("resource", $resource)
            ->where("action", $action)
            ->first();

        if (!$permission) {
            // If no specific permission is found, access might be denied or allowed by default,
            // depending on the application's security policy. Returning false is safer.
            return false;
        }

        // Check if the user has this permission, optionally scoped by libraryId
        return $this->hasPermission($permission, $libraryId);
    }

    /**
     * Resolve a role from various input types.
     */
    protected function resolveRole(Role|int|string $role): ?Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        if (is_int($role) || ctype_digit($role)) {
            // If it's a numeric string or integer, try to find by ID
            return Role::query()->find($role);
        }

        // Otherwise, assume it's a slug and try to find by slug
        return Role::query()->where("slug", $role)->first();
    }
}
