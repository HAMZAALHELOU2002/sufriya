<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // جلب المطعم المرتبط بالمستخدم
        $restaurant = $user->restaurants()->first();

        return view('profile.edit', [
            'user' => $user,
            'restaurant' => $restaurant,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'restaurant_name' => ['nullable', 'string', 'max:255'],
            'whatsapp_phone_number_id' => ['nullable', 'string', 'max:255'],
        ]);

        // تحديث بيانات المستخدم
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // تحديث بيانات المطعم المرتبط
        $restaurant = $user->restaurants()->first();

        if ($restaurant) {
            $restaurant->update([
                'name' => $validated['restaurant_name']
                    ?? $restaurant->name,
                'whatsapp_phone_number_id' => $validated['whatsapp_phone_number_id']
                    ?? $restaurant->whatsapp_phone_number_id,
            ]);
        }

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
