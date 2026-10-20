<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const USERNAME = 'admin';

    private const PASSWORD = 'admin123';

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('iems.authenticated') === true) {
            return to_route('iems.overview');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:128'],
        ]);

        if (! hash_equals(self::USERNAME, $credentials['username']) || ! hash_equals(self::PASSWORD, $credentials['password'])) {
            throw ValidationException::withMessages([
                'username' => 'The username or password is incorrect.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('iems.authenticated', true);

        return redirect()->intended(route('iems.overview'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('success', 'You have been signed out.');
    }
}
