<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Rules\Recaptcha;

class CustomerAuthController extends Controller
{
    /**
     * Show the customer login form.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle customer login request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required', new Recaptcha()],
        ], [
           'g-recaptcha-response.required' => 'Silakan centang "Saya bukan robot" terlebih dahulu.',
        ]);

        $credentials = collect($credentials)->only(['email', 'password'])->all();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $guestSessionId = $request->session()->getId();
        $guestCartId = $request->session()->get('cart_id');

        $request->session()->regenerate();

        app(CartService::class)->mergeGuestCart($guestSessionId, Auth::user(), $guestCartId);
        $request->session()->forget('cart_id');

        if (Auth::user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Show the customer registration form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * Handle customer registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
    'name' => ['required', 'string', 'max:100'],
    'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
    'phone' => ['nullable', 'string', 'max:30'],
    'password' => ['required', 'string', 'min:8', 'confirmed'],
    'g-recaptcha-response' => ['required', new Recaptcha],
], [
    'g-recaptcha-response.required' => 'Silakan centang "Saya bukan robot" terlebih dahulu.',
]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('home');
    }

    /**
     * Log the customer out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
