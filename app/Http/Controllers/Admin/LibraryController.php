<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LibraryController extends Controller
{
    /**
     * Display a listing of libraries.
     */
    public function index(Request $request): View
    {
        $query = Library::query()->with(['translations']);

        // Search
        if ($search = $request->input('search')) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('slug', 'like', "%{$search}%");
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by public status
        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $libraries = $query->paginate(50)->withQueryString();

        return view('admin.libraries.index', [
            'libraries' => $libraries,
            'filters' => $request->only(['search', 'is_active', 'is_public', 'sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new library.
     */
    public function create(): View
    {
        return view('admin.libraries.create');
    }

    /**
     * Store a newly created library in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:libraries,slug'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        // Ensure unique slug if generated
        if (!isset($validated['slug'])) {
            $originalSlug = $slug;
            $count = 1;
            while (Library::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
        }

        $library = Library::create([
            'slug' => $slug,
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'is_public' => $request->boolean('is_public', true),
        ]);

        // Create default translation (using app locale)
        $library->translations()->create([
            'language_code' => app()->getLocale(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.libraries.index')
            ->with('success', 'Library created successfully.');
    }

    /**
     * Display the specified library.
     */
    public function show(Library $library): View
    {
        $library->load(['translations', 'mangas', 'mangas.translations', 'mangas.source']);

        $users = User::with(['roles' => function ($query) use ($library) {
            $query->wherePivot('library_id', $library->id);
        }])->paginate(50); // Get users related to this library

        $roles = Role::all(); // All available roles for assignment

        return view('admin.libraries.show', [
            'library' => $library,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for editing the specified library.
     */
    public function edit(Library $library): View
    {
        // Load translation for current locale
        $translation = $library->translations()
            ->where('language_code', app()->getLocale())
            ->first();

        $library->name = $translation?->name;
        $library->description = $translation?->description;

        return view('admin.libraries.edit', [
            'library' => $library,
        ]);
    }

    /**
     * Update the specified library in storage.
     */
    public function update(Request $request, Library $library): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:libraries,slug,' . $library->id],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
        ]);

        $library->update([
            'slug' => $validated['slug'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'is_public' => $request->boolean('is_public'),
        ]);

        // Update default translation
        $library->translations()->updateOrCreate(
            ['language_code' => app()->getLocale()],
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.libraries.index')
            ->with('success', 'Library updated successfully.');
    }

    /**
     * Remove the specified library from storage.
     */
    public function destroy(Library $library): RedirectResponse
    {
        $library->delete();

        return redirect()
            ->route('admin.libraries.index')
            ->with('success', 'Library deleted successfully.');
    }

    /**
     * Assign a role to a user for a specific library.
     */
    public function assignRoleToUser(Request $request, Library $library, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->assignRole($validated['role_id'], $library->id);

        return back()->with('success', 'Role assigned to user successfully for this library.');
    }

    /**
     * Remove a role from a user for a specific library.
     */
    public function removeRoleFromUser(Request $request, Library $library, User $user, Role $role): RedirectResponse
    {
        $user->removeRole($role->id, $library->id);

        return back()->with('success', 'Role removed from user successfully for this library.');
    }
}
