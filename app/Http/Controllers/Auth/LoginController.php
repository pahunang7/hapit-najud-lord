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

    // Find user manually
    $staff = Staff::where('nin', $request->nin)->first();

    // User not found
    if (!$staff) {
        return back()->withErrors([
            'nin' => 'Invalid NIN or password.',
        ]);
    }

    // Login user manually
    Auth::login($staff);

    // Regenerate session
    $request->session()->regenerate();

    // Redirect by role
    if (str_contains(strtolower($staff->job_title), 'manager')) {
        return redirect()->route('manager.dashboard');
    }

    if (str_contains(strtolower($staff->job_title), 'supervisor')) {
        return redirect()->route('supervisor.dashboard');
    }

    return redirect()->route('staff.dashboard');
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

            default
                => redirect()->route('staff.dashboard'),
        };
    }
}