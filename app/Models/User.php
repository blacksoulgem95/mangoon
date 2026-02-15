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
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('library_id', 'expires_at', 'is_active', 'metadata')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(int|string|Role $role, ?int $libraryId = null): bool
    {
        $query = $this->roles()->wherePivot('is_active', true);

        if ($libraryId !== null) {
            $query->wherePivot('library_id', $libraryId);
        }

        if ($role instanceof Role) {
            return $query->where('roles.id', $role->id)->exists();
        }

        if (is_numeric($role)) {
            return $query->where('roles.id', $role)->exists();
        }

        return $query->where('roles.slug', $role)->exists();
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
            if (! $this->hasRole($role, $libraryId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(int|string|Permission $permission, ?int $libraryId = null): bool
    {
        $roles = $this->roles()->wherePivot('is_active', true);

        if ($libraryId !== null) {
            $roles->wherePivot('library_id', $libraryId);
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
    public function hasAnyPermission(array $permissions, ?int $libraryId = null): bool
    {
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
    public function hasAllPermissions(array $permissions, ?int $libraryId = null): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission, $libraryId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(int|string|Role $role, ?int $libraryId = null, array $attributes = []): bool
    {
        $roleModel = $this->resolveRole($role);

        if (! $roleModel) {
            return false;
        }

        if ($this->hasRole($roleModel, $libraryId)) {
            return true;
        }

        $this->roles()->attach($roleModel->id, array_merge([
            'library_id' => $libraryId,
            'is_active' => true,
            'expires_at' => null,
        ], $attributes));

        return true;
    }

    /**
     * Assign multiple roles to the user.
     */
    public function assignRoles(array $roles, ?int $libraryId = null): bool
    {
        $success = true;

        foreach ($roles as $role) {
            if (! $this->assignRole($role, $libraryId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(int|string|Role $role, ?int $libraryId = null): bool
    {
        $roleModel = $this->resolveRole($role);

        if (! $roleModel) {
            return false;
        }

        $query = $this->roles()->where('roles.id', $roleModel->id);

        if ($libraryId !== null) {
            $query->wherePivot('library_id', $libraryId);
        }

        $query->detach();

        return true;
    }

    /**
     * Remove multiple roles from the user.
     */
    public function removeRoles(array $roles, ?int $libraryId = null): bool
    {
        $success = true;

        foreach ($roles as $role) {
            if (! $this->removeRole($role, $libraryId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roles, ?int $libraryId = null): void
    {
        // Remove all existing roles for the library scope
        $existingQuery = $this->roles();

        if ($libraryId !== null) {
            $existingQuery->wherePivot('library_id', $libraryId);
        }

        $existingQuery->detach();

        // Assign new roles
        $this->assignRoles($roles, $libraryId);
    }

    /**
     * Get all permissions for the user (through roles).
     */
    public function getAllPermissions(?int $libraryId = null): \Illuminate\Database\Eloquent\Collection
    {
        $roles = $this->roles()->wherePivot('is_active', true);

        if ($libraryId !== null) {
            $roles->wherePivot('library_id', $libraryId);
        }

        $roles = $roles->get();

        $permissions = collect();

        foreach ($roles as $role) {
            $rolePermissions = $role->activePermissions()->get();
            $permissions = $permissions->merge($rolePermissions);
        }

        return $permissions->unique('id');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is an editor.
     */
    public function isEditor(): bool
    {
        return $this->hasRole('editor');
    }

    /**
     * Check if user is a reader.
     */
    public function isReader(): bool
    {
        return $this->hasRole('reader');
    }

    /**
     * Get active roles for the user.
     */
    public function activeRoles(?int $libraryId = null): BelongsToMany
    {
        $query = $this->roles()->wherePivot('is_active', true);

        if ($libraryId !== null) {
            $query->wherePivot('library_id', $libraryId);
        }

        return $query;
    }

    /**
     * Check if user can perform an action on a resource.
     */
    public function can(string $action, string $resource, ?int $libraryId = null): bool
    {
        $permission = Permission::byResourceAndAction($resource, $action)->first();

        if (! $permission) {
            return false;
        }

        return $this->hasPermission($permission, $libraryId);
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
}
