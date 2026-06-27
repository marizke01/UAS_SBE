<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class CashierAuthController extends Controller
{
    public function showLogin()
    {
        if (session()->get('cashier_authenticated') === true) {
            return redirect()->route('cashier.dashboard');
        }

        return view('auth.cashier-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = null;

        try {
            $user = DB::table('users as u')
                ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
                ->join('roles as r', 'r.id', '=', 'ur.role_id')
                ->select('u.id', 'u.name', 'u.email', 'u.password', 'r.name as role_name')
                ->where('u.email', $credentials['email'])
                ->where('u.status', 'active')
                ->whereIn('r.name', ['cashier', 'kasir'])
                ->first();
        } catch (Throwable $e) {
            dd($e->getMessage());
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email atau password kasir tidak sesuai.'])->withInput();
        }

        $request->session()->put('cashier_authenticated', true);
        $request->session()->put('cashier_user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role_name,
        ]);

        return redirect()->route('cashier.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['cashier_authenticated', 'cashier_user']);

        return redirect()->route('home');
    }

}
