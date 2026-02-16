<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::query()->with(['roles', 'manga']);

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate(50)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['roles', 'manga']);
        return view('admin.users.show', [
            'user' => $user,
        ]);
    }
}
