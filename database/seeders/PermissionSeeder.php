<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Manga Permissions
        $mangaPermissions = [
            [
                "resource" => "manga",
                "action" => "view",
                "scope" => "global",
                "description" => "View manga entries",
            ],
            [
                "resource" => "manga",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new manga entries",
            ],
            [
                "resource" => "manga",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit manga entries",
            ],
            [
                "resource" => "manga",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete manga entries",
            ],
            [
                "resource" => "manga",
                "action" => "download",
                "scope" => "global",
                "description" => "Download manga from external sources",
            ],
            [
                "resource" => "manga",
                "action" => "publish",
                "scope" => "global",
                "description" => "Publish manga entries",
            ],
            [
                "resource" => "manga",
                "action" => "feature",
                "scope" => "global",
                "description" => "Feature manga on homepage",
            ],
        ];

        // Library Permissions
        $libraryPermissions = [
            [
                "resource" => "library",
                "action" => "view",
                "scope" => "global",
                "description" => "View libraries",
            ],
            [
                "resource" => "library",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new libraries",
            ],
            [
                "resource" => "library",
                "action" => "edit",
                "scope" => "library",
                "description" => "Edit libraries",
            ],
            [
                "resource" => "library",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete libraries",
            ],
            [
                "resource" => "library",
                "action" => "manage",
                "scope" => "library",
                "description" => "Manage library content and settings",
            ],
        ];

        // Category Permissions
        $categoryPermissions = [
            [
                "resource" => "category",
                "action" => "view",
                "scope" => "global",
                "description" => "View categories",
            ],
            [
                "resource" => "category",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new categories",
            ],
            [
                "resource" => "category",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit categories",
            ],
            [
                "resource" => "category",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete categories",
            ],
        ];

        // Tag Permissions
        $tagPermissions = [
            [
                "resource" => "tag",
                "action" => "view",
                "scope" => "global",
                "description" => "View tags",
            ],
            [
                "resource" => "tag",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new tags",
            ],
            [
                "resource" => "tag",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit tags",
            ],
            [
                "resource" => "tag",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete tags",
            ],
        ];

        // Source Permissions
        $sourcePermissions = [
            [
                "resource" => "source",
                "action" => "view",
                "scope" => "global",
                "description" => "View sources",
            ],
            [
                "resource" => "source",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new sources",
            ],
            [
                "resource" => "source",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit sources",
            ],
            [
                "resource" => "source",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete sources",
            ],
        ];

        // Plugin Permissions
        $pluginPermissions = [
            [
                "resource" => "plugin",
                "action" => "view",
                "scope" => "global",
                "description" => "View plugins",
            ],
            [
                "resource" => "plugin",
                "action" => "install",
                "scope" => "global",
                "description" => "Install new plugins",
            ],
            [
                "resource" => "plugin",
                "action" => "configure",
                "scope" => "global",
                "description" => "Configure plugin settings",
            ],
            [
                "resource" => "plugin",
                "action" => "activate",
                "scope" => "global",
                "description" => "Activate/deactivate plugins",
            ],
            [
                "resource" => "plugin",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete plugins",
            ],
        ];

        // User Permissions
        $userPermissions = [
            [
                "resource" => "user",
                "action" => "view",
                "scope" => "global",
                "description" => "View users",
            ],
            [
                "resource" => "user",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new users",
            ],
            [
                "resource" => "user",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit users",
            ],
            [
                "resource" => "user",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete users",
            ],
            [
                "resource" => "user",
                "action" => "assign-roles",
                "scope" => "global",
                "description" => "Assign roles to users",
            ],
        ];

        // Role Permissions
        $rolePermissions = [
            [
                "resource" => "role",
                "action" => "view",
                "scope" => "global",
                "description" => "View roles",
            ],
            [
                "resource" => "role",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new roles",
            ],
            [
                "resource" => "role",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit roles",
            ],
            [
                "resource" => "role",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete roles",
            ],
            [
                "resource" => "role",
                "action" => "assign-permissions",
                "scope" => "global",
                "description" => "Assign permissions to roles",
            ],
        ];

        // Permission Permissions (meta!)
        $permissionPermissions = [
            [
                "resource" => "permission",
                "action" => "view",
                "scope" => "global",
                "description" => "View permissions",
            ],
            [
                "resource" => "permission",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new permissions",
            ],
            [
                "resource" => "permission",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit permissions",
            ],
            [
                "resource" => "permission",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete permissions",
            ],
        ];

        // Language Permissions
        $languagePermissions = [
            [
                "resource" => "language",
                "action" => "view",
                "scope" => "global",
                "description" => "View languages",
            ],
            [
                "resource" => "language",
                "action" => "create",
                "scope" => "global",
                "description" => "Create new languages",
            ],
            [
                "resource" => "language",
                "action" => "edit",
                "scope" => "global",
                "description" => "Edit languages",
            ],
            [
                "resource" => "language",
                "action" => "delete",
                "scope" => "global",
                "description" => "Delete languages",
            ],
        ];

        // System Permissions
        $systemPermissions = [
            [
                "resource" => "system",
                "action" => "settings",
                "scope" => "global",
                "description" => "Manage system settings",
            ],
            [
                "resource" => "system",
                "action" => "logs",
                "scope" => "global",
                "description" => "View system logs",
            ],
            [
                "resource" => "system",
                "action" => "maintenance",
                "scope" => "global",
                "description" => "Enable/disable maintenance mode",
            ],
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $mangaPermissions,
            $libraryPermissions,
            $categoryPermissions,
            $tagPermissions,
            $sourcePermissions,
            $pluginPermissions,
            $userPermissions,
            $rolePermissions,
            $permissionPermissions,
            $languagePermissions,
            $systemPermissions,
        );

        // Create permissions
        $createdPermissions = [];
        foreach ($allPermissions as $permData) {
            $slug = "{$permData["resource"]}.{$permData["action"]}";
            $name =
                ucfirst($permData["action"]) .
                " " .
                ucfirst($permData["resource"]);

            $permission = Permission::firstOrCreate(
                ["slug" => $slug],
                [
                    "name" => $name,
                    "description" => $permData["description"],
                    "guard_name" => "web",
                    "resource" => $permData["resource"],
                    "action" => $permData["action"],
                    "scope" => $permData["scope"],
                    "is_active" => true,
                    "is_system" => true,
                ],
            );

            $createdPermissions[$slug] = $permission;
        }

        $this->command->info("Permissions created successfully!");

        // Assign permissions to roles
        $this->assignPermissionsToRoles($createdPermissions);
    }

    /**
     * Assign permissions to roles.
     */
    protected function assignPermissionsToRoles(array $permissions): void
    {
    protected function assignPermissionsToRoles(array $permissions): void
    {
        // Admin - all permissions
        $admin = Role::admin(); // Ensure admin role exists
        if ($admin) {
            $admin
                ->permissions()
                ->sync(
                    collect($permissions)
                        ->map(fn($permission) => $permission->id)
                        ->all(),
                );
            $this->command->info("Admin role: all permissions assigned");
        }

        // Editor - content management permissions
        $editor = Role::editor(); // Ensure editor role exists
        if ($editor) {
            $editorPermissions = [
                "manga.view",
                "manga.create",
                "manga.edit",
                "manga.download",
                "library.view",
                "library.manage",
                "category.view",
                "category.create",
                "category.edit",
                "tag.view",
                "tag.create",
                "tag.edit",
                "source.view",
            ];

            $permissionIds = collect($editorPermissions)
                ->map(fn($slug) => $permissions[$slug]->id ?? null)
                ->filter()
                ->all();

            $editor->permissions()->sync($permissionIds);
            $this->command->info(
                "Editor role: content management permissions assigned",
            );
        }

        // Reader - viewing permissions only
        $reader = Role::reader(); // Ensure reader role exists
        if ($reader) {
            $readerPermissions = [
                "manga.view",
                "library.view",
                "category.view",
                "tag.view",
                "source.view",
            ];

            $permissionIds = collect($readerPermissions)
                ->map(fn($slug) => $permissions[$slug]->id ?? null)
                ->filter()
                ->all();

            $reader->permissions()->sync($permissionIds);
            $this->command->info("Reader role: viewing permissions assigned");
        }

        // Moderator - moderation permissions
        $moderator = Role::firstOrCreate(
            // Use firstOrCreate for moderator
            ["slug" => "moderator"],
            [
                "name" => "Moderator",
                "description" => "Moderator with content review permissions",
                "guard_name" => "web",
                "level" => 75,
                "is_active" => true,
                "is_system" => false,
            ],
        );
        // Ensure moderator role exists before assigning permissions
        if ($moderator) {
            $moderatorPermissions = [
                "manga.view",
                "manga.edit",
                "manga.publish",
                "manga.feature",
                "library.view",
                "library.manage",
                "category.view",
                "category.edit",
                "tag.view",
                "tag.edit",
                "source.view",
                "source.edit",
                "user.view",
            ];

            $permissionIds = collect($moderatorPermissions)
                ->map(fn($slug) => $permissions[$slug]->id ?? null)
                ->filter()
                ->all();

            $moderator->permissions()->sync($permissionIds);
            $this->command->info(
                "Moderator role: moderation permissions assigned",
            );
        }

        // Contributor - limited content creation permissions
        $contributor = Role::firstOrCreate(
            // Use firstOrCreate for contributor
            ["slug" => "contributor"],
            [
                "name" => "Contributor",
                "description" =>
                    "Contributor with limited content creation permissions",
                "guard_name" => "web",
                "level" => 25,
                "is_active" => true,
                "is_system" => false,
            ],
        );
        // Ensure contributor role exists before assigning permissions
        if ($contributor) {
            $contributorPermissions = [
                "manga.view",
                "manga.create",
                "library.view",
                "category.view",
                "tag.view",
                "tag.create",
                "source.view",
            ];

            $permissionIds = collect($contributorPermissions)
                ->map(fn($slug) => $permissions[$slug]->id ?? null)
                ->filter()
                ->all();

            $contributor->permissions()->sync($permissionIds);
            $this->command->info(
                "Contributor role: contribution permissions assigned",
            );
        }

        $this->command->info("All permissions assigned to roles successfully!");
    }
}
