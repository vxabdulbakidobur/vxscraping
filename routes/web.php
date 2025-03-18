<?php

use App\Http\Controllers\PackageSelectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ana sayfayı doğrudan Filament paneline yönlendir
Route::get('/', function () {
    return redirect('/admin');
});

// Dashboard rotası - paket kontrolü yapılır
Route::get('/dashboard', function () {
    // Kullanıcı giriş yapmışsa ve müşteri kaydı varsa paket kontrolü yap
    if (auth()->check()) {
        $customer = \App\Models\Customer::where('user_id', auth()->id())->first();
        
        // Eğer müşteri kaydı var ama aktif paketi yoksa paket seçim sayfasına yönlendir
        if ($customer && !$customer->hasActivePackage()) {
            return redirect()->route('packages.index');
        }
    }
    
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Paket seçim rotaları
Route::middleware('auth')->group(function () {
    // Paket seçim sayfası
    Route::get('/packages', [PackageSelectionController::class, 'index'])->name('packages.index');
    Route::post('/packages/{package}/select', [PackageSelectionController::class, 'select'])->name('packages.select');
});

// E-posta kontrolü ve yönlendirme rotaları
Route::post('/check-email', [PackageSelectionController::class, 'checkEmail'])->name('check.email');
Route::get('/redirect-to-packages', [PackageSelectionController::class, 'redirectToPackages'])->name('packages.redirect');
