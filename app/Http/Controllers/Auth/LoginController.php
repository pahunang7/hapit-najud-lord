<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;

class LoginController extends Controller
{

    /**
     * Show login form
     */
    public function showLoginForm(): View|RedirectResponse
    {
        // Already logged in
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->job_title);
        }

        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
{
    $request->validate([
        'nin' => 'required',
        'password' => 'required',
    ]);

    // Attempt login using nin + password
    if (!Auth::attempt([
        'nin' => $request->nin,
        'password' => $request->password
    ])) {

        return back()->withErrors([
            'nin' => 'Invalid NIN or password.',
        ])->withInput();
    }

    // Regenerate session
    $request->session()->regenerate();

    $staff = Auth::user();

    return $this->redirectByRole($staff->job_title);
}

    /**
     * Logout user
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect users based on role
     */
    private function redirectByRole(string $jobTitle): RedirectResponse
{
    $jobTitle = strtolower($jobTitle);

    return match (true) {

        str_contains($jobTitle, 'manager')
            => redirect()->route('manager.dashboard'),

        str_contains($jobTitle, 'supervisor')
            => redirect()->route('supervisor.dashboard'),

        str_contains($jobTitle, 'secretary')
            => redirect()->route('secretary.dashboard'),

        default
            => redirect()->route('staff.dashboard'),
    };
}
}