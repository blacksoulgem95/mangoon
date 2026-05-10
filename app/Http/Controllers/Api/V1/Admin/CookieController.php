<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CookieController extends Controller
{
    public function index()
    {
        $cookies = UserCookie::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $cookies->map(function ($cookie) {
                return [
                    'id' => $cookie->id,
                    'source' => $cookie->source,
                    'expires_at' => $cookie->expires_at?->toIso8601String(),
                    'is_active' => $cookie->is_active,
                    'created_at' => $cookie->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => ['required', Rule::in(['nhentai', 'mangadex', 'custom'])],
            'cookies' => ['required', 'array'],
            'cookies.*.name' => 'required|string',
            'cookies.*.value' => 'required|string',
            'cookies.*.domain' => 'nullable|string',
            'cookies.*.path' => 'nullable|string',
            'cookies.*.expires' => 'nullable|integer',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $expiresAt = null;
        if (isset($validated['expires_in_days'])) {
            $expiresAt = now()->addDays($validated['expires_in_days']);
        }

        $cookie = UserCookie::create([
            'user_id' => Auth::id(),
            'source' => $validated['source'],
            'cookies' => $validated['cookies'],
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return response()->json([
            'data' => $cookie,
            'message' => 'Cookie saved successfully',
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $cookie = UserCookie::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'cookies' => 'sometimes|array',
            'cookies.*.name' => 'string',
            'cookies.*.value' => 'string',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['cookies'])) {
            $currentCookies = $cookie->cookies;
            foreach ($validated['cookies'] as $updateCookie) {
                $key = array_search($updateCookie['name'], array_column($currentCookies, 'name'));
                if ($key !== false) {
                    $currentCookies[$key] = array_merge($currentCookies[$key], $updateCookie);
                }
            }
            $cookie->cookies = $currentCookies;
        }

        if (isset($validated['expires_in_days'])) {
            $cookie->expires_at = now()->addDays($validated['expires_in_days']);
        }

        if (isset($validated['is_active'])) {
            $cookie->is_active = $validated['is_active'];
        }

        $cookie->save();

        return response()->json([
            'data' => $cookie,
            'message' => 'Cookie updated successfully',
        ]);
    }

    public function destroy(string $id)
    {
        $cookie = UserCookie::where('user_id', Auth::id())->findOrFail($id);
        $cookie->delete();

        return response()->json([
            'message' => 'Cookie deleted successfully',
        ]);
    }

    public function importFromBrowser(Request $request)
    {
        $validated = $request->validate([
            'source' => ['required', Rule::in(['nhentai', 'mangadex'])],
            'cookiesJson' => 'required|string',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            $cookies = json_decode($validated['cookiesJson'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid JSON format',
                ], 400);
            }

            $expiresAt = null;
            if (isset($validated['expires_in_days'])) {
                $expiresAt = now()->addDays($validated['expires_in_days']);
            }

            $cookie = UserCookie::create([
                'user_id' => Auth::id(),
                'source' => $validated['source'],
                'cookies' => $cookies,
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);

            return response()->json([
                'data' => $cookie,
                'message' => 'Cookies imported successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to import cookies',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
