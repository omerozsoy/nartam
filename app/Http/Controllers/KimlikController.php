<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KimlikController extends Controller
{
    public function girisFormu(): View
    {
        return view('auth.giris');
    }

    public function girisYap(Request $request): RedirectResponse
    {
        $veri = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($veri, $request->boolean('beni_hatirla'))) {
            throw ValidationException::withMessages([
                'email' => 'E-posta veya şifre hatalı.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/')->with('basari', 'Giriş yapıldı.');
    }

    public function kayitFormu(): View
    {
        return view('auth.kayit');
    }

    public function kayitOl(Request $request): RedirectResponse
    {
        $veri = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefon' => ['required', 'string', 'regex:/^[0-9+\s()-]{7,20}$/'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'telefon.regex' => 'Geçerli bir cep telefonu girin.',
        ]);

        $kullanici = User::create([
            'name' => $veri['name'],
            'email' => $veri['email'],
            'telefon' => $veri['telefon'],
            'password' => $veri['password'], // model 'hashed' cast'i ile hash'lenir
        ]);

        Auth::login($kullanici);
        $request->session()->regenerate();

        return redirect('/')->with('basari', 'Hoş geldiniz!');
    }

    public function cikis(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
