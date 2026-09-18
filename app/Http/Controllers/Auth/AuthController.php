<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller; use App\Models\User; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Hash;
class AuthController extends Controller {
 public function loginForm(){return view('auth.login');}
 public function registerForm(){return view('auth.register');}
 public function register(Request $r){$d=$r->validate(['name'=>'required|max:120','email'=>'required|email|unique:users,email','password'=>'required|min:8|confirmed']);$u=User::create($d);Auth::login($u);return redirect()->route('dashboard');}
 public function login(Request $r){$d=$r->validate(['email'=>'required|email','password'=>'required']); if(!Auth::attempt($d,$r->boolean('remember'))){return back()->withErrors(['email'=>'Email atau password salah.'])->onlyInput('email');}$r->session()->regenerate();return redirect()->intended(route('dashboard'));}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect('/');}
}
