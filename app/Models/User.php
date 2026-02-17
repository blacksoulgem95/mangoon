<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ["name", "email", "password"];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
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

    /**
     * Get the library the user is associated with.
     */
    public function library(): BelongsTo
    {
        // This assumes a direct many-to-one or one-to-one relationship,
        // or you might need to adjust based on how library association is truly managed.
        // If it's through roles, the logic in hasRole/assignRole/removeRole is more critical.
        return $this->belongsTo(Library::class, "library_id"); // Assuming a library_id foreign key on users table
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
        $roles = $this->roles()->wherePivot("is_active", true);

        if ($libraryId !== null) {
            $roles->wherePivot("library_id", $libraryId);
        }

        $roles = $roles->get();

        foreach ($roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions.
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
            ->wherePivot("is_active", true)
            ->when($libraryId !== null, function ($query) use ($libraryId) {
                $query->wherePivot("library_id", $libraryId);
            })
            ->get();

        $permissions = collect();

        foreach ($roles as $role) {
            // Fetch active permissions for each role
            $rolePermissions = $role->activePermissions()->get();
            $permissions = $permissions->merge($rolePermissions);
        }

        // Return unique permissions based on their ID
        return $permissions->unique("id");
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
            return Role::find($role);
        }

        // Otherwise, assume it's a slug and try to find by slug
        return Role::where("slug", $role)->first();
    }
}
