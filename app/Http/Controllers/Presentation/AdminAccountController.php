<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAccountController extends Controller
{
    public function show()
    {
        $userId = session('admin_user.id');
        $user = DB::table('users')->where('id', $userId)->first();

        // Fallback if demo admin doesn't exist in DB yet
        if (!$user) {
            $user = (object)[
                'id' => $userId,
                'name' => session('admin_user.name', 'Tifanny Admin'),
                'email' => session('admin_user.email', 'admin@tifanny.test'),
                'role_name' => session('admin_user.role', 'owner')
            ];
        } else {
            // Fetch role if exists
            $role = DB::table('user_roles as ur')
                ->join('roles as r', 'r.id', '=', 'ur.role_id')
                ->where('ur.user_id', $userId)
                ->value('r.name');
            
            $user->role_name = $role ?: session('admin_user.role', 'admin');
        }

        $pageTitle = 'Akun Admin';

        return view('admin.account', compact('user', 'pageTitle'));
    }

    public function update(Request $request)
    {
        $userId = session('admin_user.id');

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

        $userExists = DB::table('users')->where('id', $userId)->exists();

        if ($userExists) {
            DB::table('users')->where('id', $userId)->update([
                'name' => $request->name,
                'email' => $request->email,
                'updated_at' => now(),
            ]);
        } else {
            // If demo admin, create the user in DB
            DB::table('users')->insert([
                'id' => $userId,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password'), // default
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign owner role if role table exists
            $ownerRole = DB::table('roles')->where('name', 'owner')->first();
            if ($ownerRole) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $ownerRole->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Sync session
        session()->put('admin_user.name', $request->name);
        session()->put('admin_user.email', $request->email);

        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $userId = session('admin_user.id');

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::table('users')->where('id', $userId)->first();
        $dbPassword = $user ? $user->password : null;

        // If demo admin first time, check against default "password"
        if (!$user) {
            if ($request->current_password !== 'password') {
                return back()->withErrors(['current_password' => 'Password saat ini salah.']);
            }

            // Create user first
            DB::table('users')->insert([
                'id' => $userId,
                'name' => session('admin_user.name', 'Tifanny Admin'),
                'email' => session('admin_user.email', 'admin@tifanny.test'),
                'password' => Hash::make($request->new_password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ownerRole = DB::table('roles')->where('name', 'owner')->first();
            if ($ownerRole) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $ownerRole->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } else {
            if (!Hash::check($request->current_password, $dbPassword)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.']);
            }

            DB::table('users')->where('id', $userId)->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Password admin berhasil diperbarui.');
    }
}
