<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Password Reset', description: 'Password reset endpoints')]
class PasswordResetController extends Controller
{
    /**
     * POST /api/forgot-password
     *
     * Send mail with a reset link to the given user.
     */
    public function sendMail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 500);
    }

    /**
     * GET /password-update
     *
     * Reset the given user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[!@#$%^&*(),.?":{}|<>]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->away(config('auth.reset_password_redirect'))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
