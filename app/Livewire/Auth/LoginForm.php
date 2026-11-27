<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = mb_strtolower(trim($credentials['email']));
        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 6)) {
            $this->addError('email', 'Terlalu banyak percobaan masuk. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.');
            $this->reset('password');

            return;
        }

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('email', 'Email atau kata sandi tidak sesuai.');
            $this->reset('password');

            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
