<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CashierAccountController extends Controller
{
    public function show()
    {
        $userId = session('cashier_user.id');
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            abort(404, 'Kasir tidak ditemukan.');
        }

        // Fetch role
        $role = DB::table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $userId)
            ->value('r.name');

        $user->role_name = $role ?: 'cashier';
        $pageTitle = 'Akun Kasir';

        return view('cashier.account', compact('user', 'pageTitle'));
    }

    public function update(Request $request)
    {
        $userId = session('cashier_user.id');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        // Check if email already taken by someone else
        $emailExists = DB::table('users')
            ->where('email', $request->email)
            ->where('id', '!=', $userId)
            ->exists();

        if ($emailExists) {
            return back()->withErrors(['email' => 'Email ini sudah digunakan oleh pengguna lain.'])->withInput();
        }

        DB::table('users')->where('id', $userId)->update([
            'name' => $request->name,
            'email' => $request->email,
            'updated_at' => now(),
        ]);

        // Sync session
        session()->put('cashier_user.name', $request->name);
        session()->put('cashier_user.email', $request->email);

        return back()->with('success', 'Profil kasir berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $userId = session('cashier_user.id');

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            abort(404);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        DB::table('users')->where('id', $userId)->update([
            'password' => Hash::make($request->new_password),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Password kasir berhasil diperbarui.');
    }
}
