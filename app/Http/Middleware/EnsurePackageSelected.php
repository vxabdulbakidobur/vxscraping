<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePackageSelected
{
    /**
     * Kullanıcının aktif bir paketi olup olmadığını kontrol eder.
     * Eğer aktif paketi yoksa, paket seçim sayfasına yönlendirir.
     * Aktif paketi varsa, normal erişime izin verir.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kullanıcı giriş yapmışsa ve admin veya super admin değilse
        if (auth()->check() && !auth()->user()->isAdmin()) {
            $customer = \App\Models\Customer::where('user_id', auth()->id())->first();
            
            // Login ve register sayfaları hariç tüm admin rotalarını kontrol et
            if ($request->is('admin*') && !$request->is('admin/login') && !$request->is('admin/register')) {
                // Paket seçim sayfaları hariç diğer sayfalara erişimi engelle
                if (!$request->routeIs('packages.index') && !$request->routeIs('packages.select')) {
                    // Eğer müşteri kaydı yoksa veya müşterinin aktif paketi yoksa
                    if (!$customer || !$customer->hasActivePackage()) {
                        return redirect()->route('packages.index')
                            ->with('warning', 'Site yönetim paneline erişmek için lütfen bir paket seçiniz.');
                    }
                }
            }
        }
        
        return $next($request);
    }
} 