<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (session()->get('admin_authenticated') === true) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($this->isDemoAdmin($credentials)) {
            $request->session()->put('admin_authenticated', true);
            $request->session()->put('admin_user', [
                'id' => 1,
                'name' => 'Tifanny Admin',
                'email' => 'admin@tifanny.test',
                'role' => 'owner',
            ]);

            return redirect()->route('admin.dashboard');
        }

        $user = null;

        try {
            $user = DB::table('users as u')
                ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
                ->join('roles as r', 'r.id', '=', 'ur.role_id')
                ->select('u.id', 'u.name', 'u.email', 'u.password', 'r.name as role_name')
                ->where('u.email', $credentials['email'])
                ->whereIn('r.name', ['owner', 'admin'])
                ->first();
        } catch (Throwable) {
            $user = null;
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email atau password admin tidak sesuai.'])->withInput();
        }

        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role_name,
        ]);

        return redirect()->route('admin.dashboard');
    }

    private function isDemoAdmin(array $credentials): bool
    {
        return $credentials['email'] === 'admin@tifanny.test'
            && $credentials['password'] === 'password';
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_authenticated', 'admin_user']);

        return redirect()->route('home');
    }
}
