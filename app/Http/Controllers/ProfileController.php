<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the user's profile.
     */
    public function edit()
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = auth()->user();
            $oldData = $user->only(['name', 'email', 'department', 'phone']);
            $user->update($request->validated());

            // Log activity
            Activity::create([
                'log_name' => 'profile',
                'description' => "User '{$user->name}' updated their profile",
                'subject_type' => get_class($user),
                'subject_id' => $user->id,
                'causer_type' => get_class($user),
                'causer_id' => $user->id,
                'properties' => [
                    'old' => $oldData,
                    'new' => $request->validated(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);

            return redirect()
                ->route('profile.edit')
                ->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            Log::error('Profile update failed: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update profile.');
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $user = auth()->user();

            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Log activity
            Activity::create([
                'log_name' => 'profile',
                'description' => "User '{$user->name}' changed their password",
                'subject_type' => get_class($user),
                'subject_id' => $user->id,
                'causer_type' => get_class($user),
                'causer_id' => $user->id,
                'properties' => [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);

            return redirect()
                ->route('profile.edit')
                ->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            Log::error('Password update failed: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update password.');
        }
    }

    /**
     * Show user activity log.
     */
    public function activity(Request $request)
    {
        $user = auth()->user();

        // Build query for user activities
        $query = Activity::where('causer_id', $user->id)
            ->where('causer_type', get_class($user));

        // Apply filters
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        // Get paginated activities
        $activities = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        // Get activity statistics
        $stats = [
            'total' => Activity::where('causer_id', $user->id)->count(),
            'today' => Activity::where('causer_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'this_week' => Activity::where('causer_id', $user->id)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
            'this_month' => Activity::where('causer_id', $user->id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count(),
            'by_type' => Activity::where('causer_id', $user->id)
                ->selectRaw('log_name, count(*) as count')
                ->groupBy('log_name')
                ->pluck('count', 'log_name')
                ->toArray(),
        ];

        // Get activity types for filter
        $activityTypes = Activity::where('causer_id', $user->id)
            ->distinct()
            ->pluck('log_name');

        // Get recent activities for timeline
        $recentActivities = Activity::where('causer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('profile.activity', compact(
            'user',
            'activities',
            'stats',
            'activityTypes',
            'recentActivities'
        ));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log activity before deleting
        Activity::create([
            'log_name' => 'account',
            'description' => "User '{$user->name}' deleted their account",
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'causer_type' => get_class($user),
            'causer_id' => $user->id,
            'properties' => [
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'ip' => request()->ip(),
            ],
        ]);

        auth()->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Clear user's activity log.
     */
    public function clearActivity()
    {
        try {
            $user = auth()->user();

            $deletedCount = Activity::where('causer_id', $user->id)
                ->where('causer_type', get_class($user))
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} activity records.",
                'count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear activity log.',
            ], 500);
        }
    }
}
