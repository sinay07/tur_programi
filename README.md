# Avustur - Tur Şirketi Web Sitesi

Modern ve kullanıcı dostu bir tur şirketi yönetim sistemi. Tamamen PHP tabanlı, responsive ve güzel tasarımlı.

## 🚀 Özellikler

### Kullanıcı Tarafı
- ✅ Telefon numarası ile giriş sistemi
- ✅ Otomatik tur yönlendirme (bugünün turuna göre)
- ✅ 6 farklı şehir sayfası (Gaziantep, Diyarbakır, Adıyaman, Şanlıurfa, Batman, Mardin)
- ✅ Her şehir için aktiviteler ve restoranlar
- ✅ Modern ve responsive tasarım
- ✅ Kullanıcı dostu arayüz

### Admin Paneli
- ✅ Güvenli admin girişi
- ✅ Dashboard ve istatistikler
- ✅ Kullanıcı yönetimi (ekleme, silme, aktif/pasif)
- ✅ Takvim yönetimi (tur programları)
- ✅ Aktivite yönetimi
- ✅ Restoran yönetimi
- ✅ Kolay kullanımlı arayüz

## 📋 Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu
- PDO PHP uzantısı

## 🔧 Kurulum

### 1. Dosyaları Kopyalayın
Tüm dosyaları web sunucunuzun root dizinine kopyalayın.

### 2. Veritabanını Oluşturun
`fastpanel_setup.sql` dosyasını MySQL veritabanınıza import edin:

```bash
mysql -u avusturtest_usr -p avusturtest < fastpanel_setup.sql
```

FastPanel kullanıyorsanız veritabanını ve kullanıcıyı panel üzerinden oluşturduktan sonra aynı dosyayı phpMyAdmin ile de import edebilirsiniz.

### 3. Veritabanı Bağlantısını Yapılandırın
`config.php` dosyası FastPanel için hazır değerlerle gelir. Gerekirse `.env` seti gibi çevresel değişkenler üzerinden şu anahtarları tanımlayabilirsiniz:

```php
putenv('DB_HOST=localhost');
putenv('DB_USER=avusturtest_usr');
putenv('DB_PASS=123123Aa');
putenv('DB_NAME=avusturtest');
```

### 4. Site URL'ini Ayarlayın
`SITE_URL` otomatik olarak algılanır. Alan adınız farklıysa şu şekilde override edebilirsiniz:

```php
putenv('SITE_URL=https://paneldeki-alanadiniz.com');
```

## 👤 Varsayılan Giriş Bilgileri

### Admin Paneli
- **URL:** `http://localhost/avustur/admin/`
- **Kullanıcı Adı:** `admin`
- **Şifre:** `admin123`

### Kullanıcı Girişi (Demo)
Aşağıdaki telefon numaralarından biriyle giriş yapabilirsiniz:
- `05551234567` (Ahmet Yılmaz)
- `05559876543` (Mehmet Demir)
- `05551112233` (Ayşe Kaya)

## 🗂️ Dosya Yapısı

```
avustur/
├── admin/                  # Admin panel
│   ├── includes/          # Admin ortak dosyalar
│   ├── index.php          # Dashboard
│   ├── login.php          # Admin giriş
│   ├── kullanicilar.php   # Kullanıcı yönetimi
│   ├── takvim.php         # Takvim yönetimi
│   ├── aktiviteler.php    # Aktivite yönetimi
│   └── restoranlar.php    # Restoran yönetimi
├── assets/                # Statik dosyalar
│   └── css/
│       └── style.css      # Ana CSS dosyası
├── includes/              # Ortak dosyalar
│   ├── header.php         # Sayfa başlığı
│   └── footer.php         # Sayfa altbilgisi
├── config.php             # Yapılandırma ve fonksiyonlar
├── database.sql           # Veritabanı yapısı
├── index.php              # Ana sayfa
├── login.php              # Kullanıcı giriş
├── logout.php             # Çıkış
├── gaziantep.php          # Gaziantep şehir sayfası
├── diyarbakir.php         # Diyarbakır şehir sayfası
├── adiyaman.php           # Adıyaman şehir sayfası
├── sanliurfa.php          # Şanlıurfa şehir sayfası
├── batman.php             # Batman şehir sayfası
└── mardin.php             # Mardin şehir sayfası
```

## 💡 Kullanım

### Kullanıcı Kaydı Oluşturma
1. Admin paneline giriş yapın
2. "Kullanıcılar" menüsüne tıklayın
3. "Yeni Kullanıcı Ekle" formunu doldurun
4. Kullanıcı eklendikten sonra, verdiğiniz telefon numarası ile giriş yapabilir

### Tur Programı Oluşturma
1. Admin paneline giriş yapın
2. "Takvim" menüsüne tıklayın
3. Şehir ve tarih seçerek tur programı ekleyin
4. Kullanıcılar giriş yaptıklarında o günkü tura otomatik yönlendirilecek

### Aktivite ve Restoran Ekleme
1. Admin paneline giriş yapın
2. "Aktiviteler" veya "Restoranlar" menüsüne tıklayın
3. İlgili formu doldurun ve kaydedin
4. Eklenen bilgiler şehir sayfalarında görünecek

## 🎨 Özelleştirme

### Renkleri Değiştirme
`assets/css/style.css` dosyasında `:root` bölümündeki CSS değişkenlerini düzenleyin:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    ...
}
```

### Site Adını Değiştirme
`config.php` dosyasında:

```php
define('SITE_NAME', 'Avustur');
```

## 📊 Veritabanı Tabloları

- **kullanicilar** - Kayıtlı kullanıcılar
- **adminler** - Admin kullanıcılar
- **sehirler** - Tur şehirleri
- **takvim** - Tur programları
- **aktiviteler** - Şehir aktiviteleri
- **restoranlar** - Şehir restoranları

## 🔐 Güvenlik

- Admin şifreleri `password_hash()` ile şifrelenir
- SQL injection koruması (PDO prepared statements)
- XSS koruması (sanitize fonksiyonu)
- Session güvenliği
- CSRF koruması önerilir (eklenebilir)

## 📱 Responsive Tasarım

Site tüm cihazlarda (mobil, tablet, desktop) mükemmel görünür. Modern CSS Grid ve Flexbox kullanılmıştır.

## 🆘 Destek

Herhangi bir sorun yaşarsanız:
1. Veritabanı bağlantısını kontrol edin
2. PHP hatalarını kontrol edin (`config.php` içinde error reporting açık)
3. Dosya izinlerini kontrol edin

## 📝 Lisans

Bu proje özgür yazılımdır ve dilediğiniz gibi kullanabilirsiniz.

## 🎯 Gelecek Özellikler

- Görsel yükleme sistemi
- E-posta bildirimleri
- Kullanıcı profil sayfası
- Tur rezervasyon sistemi
- Ödeme entegrasyonu
- Çoklu dil desteği

---

**Geliştirici Notu:** Bu proje modern web standartları kullanılarak geliştirilmiştir. Responsive tasarım, güvenlik ve kullanıcı deneyimi önceliklendirilmiştir.

