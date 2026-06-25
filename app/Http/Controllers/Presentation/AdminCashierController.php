<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminCashierController extends Controller
{
    public function index()
    {
        $cashiers = DB::table('users as u')
            ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->leftJoin('sales as s', function ($join) {
                $join->on('s.user_id', '=', 'u.id')
                    ->where('s.note', 'like', '%POS kasir%')
                    ->where('s.sale_status', '=', 'completed');
            })
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.phone',
                'u.status',
                'u.created_at',
                DB::raw('COUNT(s.id) as transaction_count'),
                DB::raw('COALESCE(SUM(s.grand_total), 0) as revenue')
            )
            ->whereIn('r.name', ['cashier', 'kasir'])
            ->groupBy('u.id', 'u.name', 'u.email', 'u.phone', 'u.status', 'u.created_at')
            ->orderBy('u.name')
            ->get();

        $pageTitle = 'Manajemen Kasir';

        return view('admin.cashiers', compact('cashiers', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($data) {
            $roleId = $this->cashierRoleId();
            $userId = DB::table('users')->insertGetId([
                'branch_id' => 1,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Akun kasir berhasil ditambahkan.');
    }

    public function update(Request $request, int $cashier)
    {
        $this->abortIfNotCashier($cashier);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($cashier)],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'updated_at' => now(),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        DB::table('users')->where('id', $cashier)->update($payload);

        return back()->with('success', 'Data kasir berhasil diperbarui.');
    }

    public function destroy(int $cashier)
    {
        $this->abortIfNotCashier($cashier);

        DB::table('users')->where('id', $cashier)->update([
            'status' => 'inactive',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Akun kasir berhasil dinonaktifkan.');
    }

    private function cashierRoleId(): int
    {
        $role = DB::table('roles')->where('name', 'cashier')->first();

        if ($role) {
            return (int) $role->id;
        }

        return (int) DB::table('roles')->insertGetId([
            'name' => 'cashier',
            'display_name' => 'Kasir',
            'description' => 'Akses khusus dashboard kasir dan POS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function abortIfNotCashier(int $userId): void
    {
        $exists = DB::table('users as u')
            ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('u.id', $userId)
            ->whereIn('r.name', ['cashier', 'kasir'])
            ->exists();

        abort_if(! $exists, 404);
    }
}
