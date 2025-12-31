<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SecurityCodeGate;
use Illuminate\Http\Request;

class SecurityCodeController extends Controller
{
    /**
     * Show the security code form.
     */
    public function show()
    {
        return view('auth.security-code');
    }

    /**
     * Verify the security code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'security_code' => 'required|string',
        ]);

        $code = $request->input('security_code');

        if (SecurityCodeGate::isValidCode($code)) {
            $request->session()->put('security_code_verified', true);

            // Redirect to intended URL or home
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'security_code' => 'Invalid security code. Please try again.',
        ]);
    }
}
