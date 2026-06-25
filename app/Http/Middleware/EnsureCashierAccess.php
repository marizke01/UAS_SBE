<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCashierAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('cashier_authenticated') !== true) {
            return redirect()->route('cashier.login')->with('error', 'Silakan login kasir terlebih dahulu.');
        }

        return $next($request);
    }
}
