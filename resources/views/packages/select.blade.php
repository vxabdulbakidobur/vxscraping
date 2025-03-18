<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Paket Seçimi</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Figtree', sans-serif;
        }
        .package-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .package-card:hover {
            transform: translateY(-5px);
            border-color: #f59e0b;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .price {
            color: #f59e0b;
        }
        .select-btn {
            background-color: #f59e0b;
            transition: all 0.3s ease;
        }
        .select-btn:hover {
            background-color: #d97706;
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Paket Seçimi
                </h2>
            </div>
        </header>

        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('warning') }}</span>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('info') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($packages as $package)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg package-card">
                                <div class="p-6 bg-white border-b border-gray-200">
                                    <h3 class="text-lg font-bold text-center mb-2">{{ $package->name }}</h3>
                                    
                                    <div class="mb-4">
                                        <p class="text-gray-700">{{ $package->description }}</p>
                                    </div>
                                    
                                    <div class="space-y-2 mb-6">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Site Sayısı:</span>
                                            <span class="font-semibold">{{ $package->site_count }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">URL Sayısı:</span>
                                            <span class="font-semibold">{{ $package->url_count }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Tarama Sıklığı:</span>
                                            <span class="font-semibold">{{ $package->scan_frequency }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">İşlemci Sayısı:</span>
                                            <span class="font-semibold">{{ $package->processor_count }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Bellek:</span>
                                            <span class="font-semibold">{{ $package->memory }}</span>
                                        </div>
                                    </div>
                                    
                                    <form action="{{ route('packages.select', $package) }}" method="POST" class="package-form">
                                        @csrf
                                        <button type="submit" class="w-full select-btn text-white font-bold py-2 px-4 rounded">
                                            @if ($customer && $customer->hasActivePackage())
                                                Paketi Değiştir
                                            @else
                                                Paketi Seç
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($customer && $customer->hasActivePackage())
                const packageForms = document.querySelectorAll('.package-form');
                packageForms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        if (confirm('Aktif bir paketiniz bulunmaktadır. Yeni bir paket seçmek, mevcut paketinizi değiştirecektir. Devam etmek istediğinize emin misiniz?')) {
                            this.submit();
                        }
                    });
                });
            @endif
        });
    </script>
</body>
</html> 