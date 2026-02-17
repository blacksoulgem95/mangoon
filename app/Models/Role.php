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
