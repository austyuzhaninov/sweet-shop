<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordFormRequest;
use App\Http\Requests\ResetPasswordFormRequest;
use App\Http\Requests\SignInFormRequest;
use App\Http\Requests\SignUpFormRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Psy\Util\Str;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.index');
    }

    public function signIn(SignInFormRequest $request): RedirectResponse
    {

//        Notification::route('sms')->route('mail')->notify();

        //TODO: 3rd lesson rate limit
        if (!auth()->attempt($request->validated())) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function signUp(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('auth.sign-up');
    }

    public function store(SignUpFormRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);

        event(new Registered($user));

        auth()->login($user);

        return redirect()->route('home');
    }

    public function logOut(Request $request): RedirectResponse
    {
        auth()->logout();
        request()->session()->invalidate();         // Перегенерация csrf
        request()->session()->regenerateToken();    // Регенерация токена сессии

        return redirect()->route('home');
    }

    public function forgot(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(ForgotPasswordFormRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // TODO: 3rd lesson
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['message' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function reset(string $token): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('auth.reset-password', [
            'token' => $token
        ]);
    }

    public function resetPassword(ResetPasswordFormRequest $request): RedirectResponse
    {

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->setRememberToken(str()->random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function github(): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function githubCallback()
    {
        $githubUser = Socialite::driver('github')->user();

        //TODO: 3rd lesson move to custom table
        $user = User::query()->updateOrCreate([
            'github_id' => $githubUser->id,
        ], [
            'name' => $githubUser->name,
            'email' => $githubUser->email,
            'password' => bcrypt(str()->random(20)),
//            'github_token' => $githubUser->token,
//            'github_refresh_token' => $githubUser->refreshToken,
        ]);

        auth()->login($user);

        return redirect()->intended(route('home'));
    }
}
