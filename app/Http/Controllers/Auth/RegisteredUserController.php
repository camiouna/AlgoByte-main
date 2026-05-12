<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member; // 1. Change User to Member
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 2. Update validation to match your 'members' table columns
        $request->validate([
            'username' => 'required|string|max:50|unique:'.Member::class,
            'email' => 'required|string|lowercase|email|max:100|unique:'.Member::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 3. Create a Member instance with your specific schema fields
        $member = Member::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Default preferences (as defined in your migration)
            'preferred_language' => 'javascript',
            'theme' => 'dark',
            'editor_font_size' => 14,
        ]);

        // 4. Trigger the Registered event so the verification email sends
        event(new Registered($member));

        // 5. Log the member in using the newly created object
        Auth::login($member);

        return redirect(route('home', absolute: false));
    }
}
