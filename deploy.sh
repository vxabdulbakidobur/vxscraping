#!/bin/bash

cd "$(dirname "$0")"
set -e

echo "Başlatılıyor: Git master branch alınıyor"
git pull origin master

echo "Başlatılıyor: Composer bağımlılıklarını yükleme"
/usr/bin/php8.3 /usr/local/bin/composer install --optimize-autoloader

echo "Başlatılıyor: Veritabanı sıfırlama ve seedleme"
/usr/bin/php8.3 artisan migrate --seed

echo "Başlatılıyor: Önbellek temizleme"
/usr/bin/php8.3 artisan optimize:clear

echo "Başlatılıyor: NPM bağımlılıklarını yükleme ve derleme"
npm install && npm run build

echo "Başlatılıyor: Konfigürasyon önbelleği oluşturma"
/usr/bin/php8.3 artisan config:cache

echo "Başlatılıyor: Rotalar önbelleği oluşturma"
/usr/bin/php8.3 artisan route:cache

echo "Başlatılıyor: Görünümler önbelleği oluşturma"
/usr/bin/php8.3 artisan view:cache

echo "Başlatılıyor: Genel optimizasyon"
/usr/bin/php8.3 artisan optimize

echo "Başlatılıyor: Filament önbelleği oluşturma"
/usr/bin/php8.3 artisan filament:optimize

echo "Başlatılıyor: Eski depolamayı kaldırma"
rm public/storage

echo "Başlatılıyor: Depolama dizinini sembolik bağlantı ile oluşturma"
/usr/bin/php8.3 artisan storage:link

echo "Başlatılıyor: Depolama yetkilendirmeleri"
chmod -R 775 storage
chmod -R 775 public/storage

echo "Dağıtım tamamlandı!"
