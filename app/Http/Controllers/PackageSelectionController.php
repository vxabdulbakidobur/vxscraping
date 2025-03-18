<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PackageSelectionController extends Controller
{
    /**
     * Paket seçim sayfasını göster
     */
    public function index()
    {
        // Aktif paketleri getir
        $packages = Package::where('is_active', true)->get();
        
        // Kullanıcının müşteri bilgilerini getir
        $customer = Customer::where('user_id', Auth::id())->first();
        
        // Eğer müşteri zaten aktif bir pakete sahipse, uyarı mesajı göster
        $hasActivePackage = false;
        if ($customer && $customer->hasActivePackage()) {
            $hasActivePackage = true;
            // Flash mesajı olarak uyarı göster
            session()->flash('warning', 'Aktif bir paketiniz bulunmaktadır. Yeni bir paket seçmek, mevcut paketinizi değiştirecektir. Devam etmek istediğinize emin misiniz?');
        }
        
        return view('packages.select', compact('packages', 'customer', 'hasActivePackage'));
    }
    
    /**
     * Seçilen paketi müşteriye ata
     */
    public function select(Request $request, Package $package)
    {
        // Kullanıcının müşteri bilgilerini getir
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        // Paket bitiş tarihini 10 yıl olarak ayarla
        $expireDate = now()->addYears(10); // 100 yıl MySQL datetime sınırlarını aşıyor
        
        // Müşteri-paket ilişkisini oluştur
        $customer->packages()->attach($package->id, [
            'start_date' => now(),
            'expire_date' => $expireDate,
            'is_active' => true,
            // Fiyat bilgisini kaydetmeye gerek yok
        ]);
        
        // Müşterinin aktif paketini güncelle
        $customer->update([
            'package_id' => $package->id,
            'expire_date' => $expireDate,
        ]);
        
        return redirect('/admin')
            ->with('success', 'Paket başarıyla seçildi.');
    }

    /**
     * E-posta kontrolü yap ve kayıtlı ise paket sayfasına yönlendir
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if ($user) {
            // E-posta kayıtlı, session'a bilgiyi kaydet
            Session::put('registered_email', $email);
            Session::put('email_check_time', now()->timestamp);
            
            // Kullanıcıya bilgi mesajı göster
            return back()->with('info', 'Bu e-posta adresi kayıtlıdır. Bu e-posta adresi ile devam etmek istiyorsanız lütfen <a href="' . route('packages.redirect', ['email' => $email]) . '" class="text-blue-600 underline font-bold">tıklayınız</a>.');
        }

        // E-posta kayıtlı değil, normal kayıt akışına devam et
        return back()->with('info', 'Bu e-posta adresi kayıtlı değildir. Lütfen kayıt olunuz.');
    }

    /**
     * Kayıtlı e-posta ile paket sayfasına yönlendir
     */
    public function redirectToPackages(Request $request)
    {
        $email = $request->query('email');
        $sessionEmail = Session::get('registered_email');
        $checkTime = Session::get('email_check_time');
        
        // Session kontrolü yap - güvenlik için
        if (!$sessionEmail || $sessionEmail !== $email || !$checkTime || (now()->timestamp - $checkTime) > 3600) {
            return redirect()->route('login')->with('warning', 'Oturum süresi dolmuş veya geçersiz bağlantı. Lütfen tekrar giriş yapınız.');
        }
        
        // Kullanıcıyı bul
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Kullanıcı bulunamadı. Lütfen tekrar giriş yapınız.');
        }
        
        // Kullanıcı girişi yap
        Auth::login($user);
        
        // Session'ı temizle
        Session::forget(['registered_email', 'email_check_time']);
        
        // Paket sayfasına yönlendir
        return redirect()->route('packages.index');
    }
}
