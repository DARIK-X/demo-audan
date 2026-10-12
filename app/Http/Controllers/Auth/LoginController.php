<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create() { return view('auth.login'); }
    public function store(Request $request) {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (!Auth::attempt($credentials)) throw ValidationException::withMessages(['email' => 'Email немесе құпиясөз қате.']);
        $request->session()->regenerate();
        if (!$request->user()->is_admin) {
            Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
            throw ValidationException::withMessages(['email' => 'Бұл аккаунтқа басқару рұқсаты берілмеген.']);
        }
        return redirect()->intended(route('admin.index'));
    }
    public function destroy(Request $request) {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
