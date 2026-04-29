<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = auth()->user();
            $user->update($request->validated());

            // Log activity
            activity()
                ->causedBy($user)
                ->withProperties($request->validated())
                ->log('Profile updated');

            return redirect()
                ->route('profile.edit')
                ->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update profile.');
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return redirect()
                ->route('profile.edit')
                ->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update password.');
        }
    }

    public function activity()
    {
        $user = auth()->user();
        $activities = \App\Models\Activity::where('causer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.activity', compact('user', 'activities'));
    }
}
