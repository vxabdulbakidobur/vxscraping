VX Scraping Sistemi Detaylı Teknik Dokümantasyonu
1. Sistem Genel Bakış
VX Scraping, web sitelerinden veri çekme (web scraping) işlemlerini yönetmek, gerçekleştirmek ve sonuçları depolamak için tasarlanmış kapsamlı bir sistemdir. Sistem, iki ana bileşenden oluşmaktadır:
1.	Laravel Yönetim Paneli (vx-scraping-master): Kullanıcı arayüzü, site profilleri yönetimi, scraping kuralları tanımlama ve sonuçları görüntüleme
2.	Node.js Scraping İşçisi (vx-scraping-nodes-master): Asıl scraping işlemlerini gerçekleştiren, kuyruk yönetimi yapan ve verileri depolayan bileşen


2. Laravel Uygulaması (vx-scraping-master)
2.1 Teknoloji Yığını
•	Framework: Laravel 11.x
•	PHP Sürümü: 8.2+
•	Veritabanı: MongoDB (mongodb/laravel-mongodb paketi ile)
•	Admin Panel: Filament 3.x
•	Diğer Bağımlılıklar: Laravel Tinker, Faker, Pest (test)
2.2 Dizin Yapısı
Laravel uygulaması, standart Laravel dizin yapısını takip etmektedir. Önemli dizinler ve dosyalar şunlardır:
•	app/: Uygulamanın ana kodu
•	Enums/: Enum sınıfları (QueueStatusEnum, ScrapingStatusEnum)
•	Filament/: Admin panel kaynakları
•	Http/: Kontrolcüler ve middleware'ler
•	Models/: Veritabanı modelleri
•	Providers/: Servis sağlayıcıları
•	config/: Yapılandırma dosyaları
•	routes/: API ve web rotaları
•	resources/: Görünüm dosyaları ve varlıklar
2.3 Veri Modelleri
Laravel uygulaması, aşağıdaki ana veri modellerini içermektedir:
2.3.1 Site Modeli
Site modeli, scraping yapılacak web sitelerinin profillerini temsil eder. Aşağıdaki alanları içerir:
•	user_id: Site profilini oluşturan kullanıcı
•	customer_id: Site profilinin ait olduğu müşteri
•	token: API erişimi için benzersiz token
•	name: Site adı
•	pagination_item_selector: Sayfalama bağlantıları için CSS selektörü
•	product_item_selector: Ürün öğeleri için CSS selektörü
•	include_subcategories: Alt kategorilerin dahil edilip edilmeyeceği
•	subcategory_selector: Alt kategori bağlantıları için CSS selektörü
•	status: Sitenin durumu (aktif, pasif, vb.)
•	queue_status: Kuyruk durumu (boşta, işleniyor, tamamlandı, vb.)
•	last_scraping_date: Son scraping tarihi
Site modeli, aşağıdaki ilişkilere sahiptir:
•	user: Site profilini oluşturan kullanıcı
•	customer: Site profilinin ait olduğu müşteri
•	siteCategories: Site kategorileri
•	siteScrapingRules: Site scraping kuralları
2.3.2 SiteCategory Modeli
SiteCategory modeli, bir site profiline ait kategorileri temsil eder. Aşağıdaki alanları içerir:
•	site_id: Kategorinin ait olduğu site
•	name: Kategori adı
•	url: Kategori URL'si
•	parent_id: Üst kategori ID'si (alt kategoriler için)
•	is_active: Kategorinin aktif olup olmadığı
SiteCategory modeli, aşağıdaki ilişkilere sahiptir:
•	site: Kategorinin ait olduğu site
•	parent: Üst kategori
•	children: Alt kategoriler
2.3.3 SiteScrapingRule Modeli
SiteScrapingRule modeli, bir site profiline ait scraping kurallarını temsil eder. Aşağıdaki alanları içerir:
•	site_id: Kuralın ait olduğu site
•	name: Kural adı (örn. "title", "price", "description")
•	selector: CSS selektörü veya XPath ifadesi
•	attribute: Çekilecek öznitelik (örn. "text", "href", "src")
•	is_required: Kuralın zorunlu olup olmadığı
•	is_multiple: Kuralın birden fazla değer döndürüp döndürmediği
•	is_active: Kuralın aktif olup olmadığı
SiteScrapingRule modeli, aşağıdaki ilişkilere sahiptir:
•	site: Kuralın ait olduğu site
2.3.4 Product Modeli
Product modeli, scraping sonucunda elde edilen ürün verilerini temsil eder. Aşağıdaki alanları içerir:
•	site_id: Ürünün ait olduğu site
•	category_id: Ürünün ait olduğu kategori
•	url: Ürün URL'si
•	data: Ürün verileri (JSON formatında)
2.4 Enum Sınıfları
Laravel uygulaması, aşağıdaki enum sınıflarını içermektedir:
2.4.1 ScrapingStatusEnum
ScrapingStatusEnum, bir site profilinin scraping durumunu temsil eder. Aşağıdaki değerleri içerir:
•	ACTIVE: Aktif
•	INACTIVE: Pasif
•	PENDING: Beklemede
•	COMPLETED: Tamamlandı
•	FAILED: Başarısız
2.4.2 QueueStatusEnum
QueueStatusEnum, bir site profilinin kuyruk durumunu temsil eder. Aşağıdaki değerleri içerir:
•	IDLE: Boşta
•	PROCESSING: İşleniyor
•	COMPLETED: Tamamlandı
•	FAILED: Başarısız
•	CANCELLED: İptal edildi
2.5 API Kontrolcüleri
Laravel uygulaması, aşağıdaki API kontrolcülerini içermektedir:
2.5.1 SiteController
SiteController, site profilleri ve ürün verileri ile ilgili API endpoint'lerini yönetir. Aşağıdaki metodları içerir:
•	getScrapingProduct: Belirli bir token için ürün verilerini getirir
•	setQueueStatusCompleted: Belirli bir site için kuyruk durumunu tamamlandı olarak işaretler
•	getProducts: Belirli bir site için ürünleri getirir
2.6 API Rotaları
Laravel uygulaması, aşağıdaki API rotalarını içermektedir:
•	GET /api/products/{token}: Belirli bir token için ürün verilerini getirir
•	POST /api/queue_status_completed/{siteId}: Belirli bir site için kuyruk durumunu tamamlandı olarak işaretler
•	GET /api/get_products/{siteId}: Belirli bir site için ürünleri getirir
2.7 Filament Admin Paneli
Laravel uygulaması, Filament admin panelini kullanarak aşağıdaki kaynakları yönetir:
•	SiteResource: Site profillerini yönetmek için
•	CustomerResource: Müşterileri yönetmek için
•	UserResource: Kullanıcıları yönetmek için

3. Node.js Uygulaması (vx-scraping-nodes-master)
3.1 Teknoloji Yığını
•	Runtime: Node.js
•	Framework: Express
•	Kuyruk Yönetimi: Bull, Redis
•	Web Scraping: Puppeteer
•	Veritabanı: MongoDB (Mongoose)
•	Diğer Bağımlılıklar: Axios, Bull-Board, dotenv
3.2 Dizin Yapısı
Node.js uygulaması, aşağıdaki dizin yapısını takip etmektedir:
•	config/: Yapılandırma dosyaları
•	database.js: MongoDB bağlantı yapılandırması
•	puppeteerConfig.js: Puppeteer yapılandırması
•	axios.js: Axios HTTP istemci yapılandırması
•	models/: Veritabanı modelleri
•	siteCategorySchema.js: Kategori şeması
•	siteProductSchema.js: Ürün şeması
•	queues/: Kuyruk tanımları ve işleyicileri
•	scrapeQueue.js: Kuyruk tanımları
•	processScrape.js: Kuyruk işleyicileri
•	services/: İş mantığı servisleri
•	scrapingService.js: Scraping işlemlerini gerçekleştiren servis
•	app.js: Ana uygulama dosyası
3.3 Veritabanı Modelleri
Node.js uygulaması, aşağıdaki veritabanı modellerini içermektedir:
3.3.1 SiteCategory Modeli
SiteCategory modeli, bir site profiline ait kategorileri temsil eder. Aşağıdaki alanları içerir:
•	category_id: Kategori ID'si
•	category_name: Kategori adı
•	url: Kategori URL'si
Bu model, her site için dinamik olarak oluşturulur. Örneğin, site ID'si 123 olan bir site için site_123_categories koleksiyonu oluşturulur.
3.3.2 SiteProduct Modeli
SiteProduct modeli, scraping sonucunda elde edilen ürün verilerini temsil eder. Aşağıdaki alanları içerir:
•	category_id: Ürünün ait olduğu kategori ID'si
•	category_name: Ürünün ait olduğu kategori adı
•	product_link: Ürün URL'si
•	scraping_data: Ürün verileri (JSON formatında)
Bu model, her site için dinamik olarak oluşturulur. Örneğin, site ID'si 123 olan bir site için site_123_products koleksiyonu oluşturulur.
3.4 Kuyruk Yapısı
Node.js uygulaması, aşağıdaki kuyrukları içermektedir:
3.4.1 siteQueue
siteQueue, site kategorilerini işlemek için kullanılır. Her iş, aşağıdaki verileri içerir:
•	site: Site profili bilgileri
•	category: İşlenecek kategori bilgileri
3.4.2 productQueue
productQueue, ürün sayfalarını işlemek için kullanılır. Her iş, aşağıdaki verileri içerir:
•	siteId: Site ID'si
•	category_id: Kategori ID'si
•	category_name: Kategori adı
•	productLink: Ürün URL'si
•	rules: Ürün sayfasında uygulanacak scraping kuralları
3.5 Scraping Servisi
Node.js uygulaması, aşağıdaki scraping servislerini içermektedir:
3.5.1 processSite
processSite fonksiyonu, bir site kategorisini işlemek için kullanılır. Aşağıdaki adımları gerçekleştirir:
1.	Puppeteer tarayıcısını başlatır
2.	Kategori sayfasını ziyaret eder
3.	Ürün linklerini çıkarır
4.	Her ürün linki için productQueue'ya bir iş ekler
5.	Sayfalama varsa, sonraki sayfaları da işler
3.5.2 scrapeCategory
scrapeCategory fonksiyonu, bir kategori sayfasını işlemek için kullanılır. Aşağıdaki adımları gerçekleştirir:
1.	Kategori sayfasını ziyaret eder
2.	Ürün linklerini çıkarır
3.	Her ürün linki için productQueue'ya bir iş ekler
4.	Sayfalama varsa, sonraki sayfaları da işler
5.	Alt kategoriler varsa, onları da işler
3.5.3 productPageScrap
productPageScrap fonksiyonu, bir ürün sayfasını işlemek için kullanılır. Aşağıdaki adımları gerçekleştirir:
1.	Ürün sayfasını ziyaret eder
2.	Tanımlanan scraping kurallarını uygular
3.	Ürün verilerini çıkarır
4.	Verileri döndürür



3.6 API Endpoint'leri
Node.js uygulaması, aşağıdaki API endpoint'lerini içermektedir:
3.6.1 POST /scrape
Bu endpoint, scraping işlemini başlatmak için kullanılır. Aşağıdaki adımları gerçekleştirir:
1.	İstek gövdesinden site profilini ve kategorileri alır
2.	Her kategori için siteQueue'ya bir iş ekler
3.	Başarı durumunu döndürür
3.6.2 POST /cancel_scraping
Bu endpoint, devam eden scraping işlemini iptal etmek için kullanılır. Aşağıdaki adımları gerçekleştirir:
1.	İstek gövdesinden site ID'sini alır
2.	siteQueue ve productQueue'daki ilgili işleri bulur
3.	İşleri başarısız olarak işaretler ve kaldırır
4.	Başarı durumunu döndürür
3.6.3 GET /admin/queues
Bu endpoint, Bull-Board arayüzünü sağlar. Kuyruk durumunu ve işleri görüntülemek için kullanılır.

4. Sistemin Çalışma Akışı
4.1 Site Profili Oluşturma
1.	Kullanıcı, Laravel yönetim paneli üzerinden bir site profili oluşturur
2.	Site için CSS selektörleri ve scraping kuralları tanımlanır
3.	Site kategorileri eklenir
4.	Sistem, site için benzersiz bir token oluşturur
4.2 Scraping İşlemini Başlatma
1.	Kullanıcı, Laravel panelinden scraping işlemini başlatır
2.	Laravel, site profilini ve kategorileri Node.js uygulamasına gönderir
3.	Node.js uygulaması, her kategori için siteQueue'ya bir iş ekler
4.	Laravel, site durumunu "işleniyor" olarak günceller
4.3 Kategori İşleme
1.	siteQueue işleyicisi, kategori işini alır
2.	processSite fonksiyonu çağrılır
3.	Puppeteer, kategori sayfasını ziyaret eder
4.	Ürün linkleri çıkarılır
5.	Her ürün linki için productQueue'ya bir iş eklenir
6.	Sayfalama varsa, sonraki sayfalar da işlenir
7.	Alt kategoriler varsa, onlar da işlenir
4.4 Ürün İşleme
1.	productQueue işleyicisi, ürün işini alır
2.	productPageScrap fonksiyonu çağrılır
3.	Puppeteer, ürün sayfasını ziyaret eder
4.	Tanımlanan scraping kuralları uygulanır
5.	Ürün verileri çıkarılır
6.	Veriler, MongoDB'ye kaydedilir
4.5 İşlem Tamamlandığında
1.	Tüm işler tamamlandığında, Node.js uygulaması Laravel'e bildirim gönderir
2.	Laravel, site durumunu "tamamlandı" olarak günceller
3.	Kullanıcı, Laravel paneli üzerinden sonuçları görüntüleyebilir
4.6 Veri Erişimi
1.	Kullanıcı, Laravel paneli üzerinden çekilen verilere erişebilir
2.	Veriler, JSON formatında dışa aktarılabilir
3.	Veriler, API üzerinden erişilebilir
5. Çevre Değişkenleri
5.1 Laravel (.env)
•	APP_NAME: Uygulama adı
•	APP_URL: Laravel uygulamasının URL'si
•	NODE_APP_URL: Node.js uygulamasının URL'si
•	DB_CONNECTION: Veritabanı bağlantı türü
•	DB_HOST: Veritabanı sunucusu
•	DB_PORT: Veritabanı portu
•	DB_DATABASE: Veritabanı adı
•	DB_USERNAME: Veritabanı kullanıcı adı
•	DB_PASSWORD: Veritabanı şifresi
5.2 Node.js (.env)
•	MONGODB_URI: MongoDB bağlantı URL'si
•	REDIS_HOST: Redis sunucusu
•	REDIS_PORT: Redis portu
•	API_BASE_URL: Laravel API'sinin temel URL'si
•	PRODUCT_QUEUE_CONCURRENCY: Paralel ürün işleme sayısı
6. Kurulum ve Dağıtım
6.1 Laravel Uygulaması
1.	Kaynak kodunu klonlayın
2.	Bağımlılıkları yükleyin: composer install
3.	.env dosyasını yapılandırın
4.	Veritabanı tablolarını oluşturun: php artisan migrate
5.	Uygulamayı başlatın: php artisan serve
6.2 Node.js Uygulaması
1.	Kaynak kodunu klonlayın
2.	Bağımlılıkları yükleyin: npm install
3.	.env dosyasını yapılandırın
4.	Uygulamayı başlatın: node app.js
6.3 Docker ile Dağıtım
Sistem, Docker ve Docker Compose ile dağıtılabilir. Bu, kurulum ve dağıtım sürecini basitleştirir ve tutarlı bir ortam sağlar.
7. Sonuç
VX Scraping sistemi, web sitelerinden veri çekme işlemlerini yönetmek ve gerçekleştirmek için kapsamlı bir çözüm sunar. Laravel yönetim paneli ve Node.js scraping işçisi olmak üzere iki ana bileşenden oluşan sistem, ölçeklenebilir, güvenilir ve performanslı bir şekilde çalışır.
Sistem, aşağıdaki avantajları sunar:
•	Esneklik: Farklı web siteleri için özelleştirilebilir scraping kuralları
•	Ölçeklenebilirlik: Paralel işleme ve dağıtık mimari
•	Güvenilirlik: Kuyruk yönetimi ve hata toleransı
•	Kullanıcı Dostu: Filament admin paneli ile kolay yönetim
•	Performans: Puppeteer ve Bull ile optimize edilmiş scraping işlemleri
Bu dokümantasyon, VX Scraping sisteminin genel yapısını, bileşenlerini ve çalışma akışını açıklamaktadır. Sistem, web scraping ihtiyaçları için kapsamlı ve güçlü bir çözüm sunar.
