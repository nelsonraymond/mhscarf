<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Address;
use App\Models\Order;
use App\Models\Wishlist;

class ProfileController extends Controller
{
    /**
     * Display the authenticated customer profile.
     */
   public function show(Request $request): View
{
    $user = $request->user();

    return view('profile', [
        'user' => $user,
        'ordersCount' => Order::where('user_id', $user->id)->count(),
        'addressesCount' => Address::where('user_id', $user->id)->count(),
        'wishlistCount' => Wishlist::where('user_id', $user->id)->count(),
    ]);
}

    /**
     * Show the profile edit form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the authenticated customer's profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = $validated['password']; // password mutator will hash
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
