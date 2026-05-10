<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    public function index()
    {
        $libraries = Library::with(['translations'])->get();
        
        return response()->json([
            'data' => $libraries->map(function ($library) {
                return [
                    'id' => $library->id,
                    'name' => $library->getTranslatedName(),
                    'description' => $library->translations->first()?->description,
                    'featured_manga_count' => $library->manga()->wherePivot('is_featured', true)->count(),
                ];
            }),
        ]);
    }

    public function show(Library $library)
    {
        $library->load(['translations', 'manga']);
        
        return response()->json($library);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $library = Library::create($validated);

        return response()->json($library, 201);
    }

    public function update(Request $request, Library $library)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $library->update($validated);

        return response()->json($library);
    }

    public function destroy(Library $library)
    {
        $library->delete();

        return response()->json(['message' => 'Library deleted successfully']);
    }

    public function users(Library $library)
    {
        $users = $library->users()->with('roles')->get();
        
        return response()->json([
            'data' => $users->map(function ($user) use ($library) {
                $role = $user->roles()->wherePivot('library_id', $library->id)->first();
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role ? $role->name : null,
                    'role_slug' => $role ? $role->slug : null,
                ];
            }),
        ]);
    }

    public function assignRole(Request $request, Library $library, User $user)
    {
        $validated = $request->validate([
            'role_slug' => 'required|exists:roles,slug',
            'expires_at' => 'nullable|date',
        ]);

        $role = Role::where('slug', $validated['role_slug'])->first();
        
        $library->users()->attach($user->id, [
            'role_id' => $role->id,
            'library_id' => $library->id,
            'is_active' => true,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->name,
            'role' => $role->name,
        ]);
    }

    public function removeRole(Library $library, User $user, string $role)
    {
        $roleId = is_numeric($role) ? $role : Role::where('slug', $role)->value('id');
        
        $library->users()->detach($user->id, ['role_id' => $roleId]);

        return response()->json(['message' => 'Role removed successfully']);
    }
}
