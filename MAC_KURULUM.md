# 🍎 Mac'te XAMPP Kurulumu ve Çalıştırma

## 📦 Yedek Bilgileri
- **Dosya Adı:** `avustur_backup_20251012_221037.zip`
- **Boyut:** ~1.4 MB
- **Konum:** `C:\xampp\htdocs\`

---

## 🚀 Mac'te Kurulum Adımları

### 1️⃣ XAMPP'i İndirin ve Kurun

1. **XAMPP for macOS İndirin:**
   ```
   https://www.apachefriends.org/download.html
   ```
   - macOS için en son sürümü seçin
   - PHP 8.0 veya üzeri önerilen

2. **DMG Dosyasını Çalıştırın:**
   - İndirilen `.dmg` dosyasını açın
   - XAMPP'i Applications klasörüne sürükleyin

3. **XAMPP'i Başlatın:**
   ```bash
   sudo /Applications/XAMPP/xamppfiles/xampp start
   ```

### 2️⃣ Projeyi Kopyalayın

1. **ZIP Dosyasını Extract Edin:**
   ```bash
   cd ~/Downloads
   unzip avustur_backup_20251012_221037.zip
   ```

2. **XAMPP htdocs Klasörüne Taşıyın:**
   ```bash
   sudo mv avustur /Applications/XAMPP/xamppfiles/htdocs/
   ```

3. **İzinleri Ayarlayın:**
   ```bash
   sudo chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/avustur
   sudo chown -R daemon:daemon /Applications/XAMPP/xamppfiles/htdocs/avustur
   ```

### 3️⃣ Veritabanını Kurun

1. **phpMyAdmin'i Açın:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Veritabanı Oluşturun:**
   - Sol tarafta "New" butonuna tıklayın
   - Veritabanı adı: `avusturtest`
   - Collation: `utf8mb4_turkish_ci`
   - Create butonuna tıklayın

3. **SQL Dosyasını İçe Aktarın:**

   ```
   - SQL sekmesine tıklayın
   - fastpanel_setup.sql dosyasını seçin veya içeriğini yapıştırın
   - Go butonuna tıklayın
   ```

4. **Admin Kullanıcısı Oluşturun:**
   ```sql
   INSERT INTO adminler (kullanici_adi, sifre, ad_soyad, email, kayit_tarihi) 
   VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin@avustur.com', NOW())
   ON DUPLICATE KEY UPDATE kullanici_adi = kullanici_adi;
   ```

### 4️⃣ Konfigürasyonu Kontrol Edin

1. **config.php Dosyasını Açın:**
   ```bash
   nano /Applications/XAMPP/xamppfiles/htdocs/avustur/config.php
   ```

2. **Ayarları Kontrol Edin:**
   ```php
   putenv('DB_HOST=localhost');
   putenv('DB_USER=avusturtest_usr');
   putenv('DB_PASS=123123Aa');
   putenv('DB_NAME=avusturtest');
   putenv('SITE_URL=http://localhost/avustur');
   ```
   Yerel ortamda farklı kullanıcı/şifre kullanacaksanız değerleri buna göre güncelleyin.

3. **Kaydet ve Çık:**
   - `Ctrl + O` (Enter)
   - `Ctrl + X`

### 5️⃣ PHP GD Extension'ı Aktif Edin

1. **php.ini Dosyasını Bulun:**
   ```bash
   /Applications/XAMPP/xamppfiles/etc/php.ini
   ```

2. **GD Extension'ı Aktif Edin:**
   ```bash
   sudo nano /Applications/XAMPP/xamppfiles/etc/php.ini
   ```
   
   Şu satırı bulun ve başındaki `;` işaretini kaldırın:
   ```
   ;extension=gd
   ```
   
   Şöyle olmalı:
   ```
   extension=gd
   ```

3. **Apache'yi Yeniden Başlatın:**
   ```bash
   sudo /Applications/XAMPP/xamppfiles/xampp restart
   ```

### 6️⃣ Uploads Klasörü İzinleri

```bash
sudo mkdir -p /Applications/XAMPP/xamppfiles/htdocs/avustur/uploads/urunler
sudo chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/avustur/uploads
```

---

## 🧪 Test Etme

### 1. **Ana Sayfayı Açın:**
```
http://localhost/avustur
```

### 2. **Admin Panele Giriş:**
```
URL: http://localhost/avustur/admin/login.php
Kullanıcı Adı: admin
Şifre: admin123
```

### 3. **Kullanıcı Girişi (Test):**
```
URL: http://localhost/avustur/login.php
Telefon: 05551234567 (veya veritabanındaki herhangi bir telefon)
```

---

## 🔧 Sorun Giderme

### Apache Başlamıyor?
```bash
# Port kontrolü
sudo lsof -i :80

# Apache'yi manuel başlat
sudo /Applications/XAMPP/xamppfiles/bin/apachectl start
```

### MySQL Başlamıyor?
```bash
# MySQL'i manuel başlat
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
```

### Permission Denied Hatası?
```bash
# Tüm izinleri düzelt
sudo chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/avustur
sudo chown -R daemon:daemon /Applications/XAMPP/xamppfiles/htdocs/avustur
```

### GD Extension Çalışmıyor?
```bash
# PHP versiyonunu kontrol et
php -v

# GD extension'ı kontrol et
php -m | grep gd

# Eğer gd görünmüyorsa:
sudo nano /Applications/XAMPP/xamppfiles/etc/php.ini
# extension=gd satırını bul ve ; işaretini kaldır
```

### Türkçe Karakterler Bozuk?
```sql
-- Veritabanı charset'ini kontrol et
ALTER DATABASE avusturtest CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

-- Tabloları güncelle
ALTER TABLE kullanicilar CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
ALTER TABLE adminler CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
ALTER TABLE sehirler CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
-- (Diğer tablolar için de tekrarla)
```

---

## 📂 Proje Yapısı

```
avustur/
├── admin/                    # Admin paneli
│   ├── includes/            # Admin ortak dosyalar
│   ├── index.php            # Dashboard
│   ├── login.php            # Admin giriş
│   ├── sehirler.php         # Şehir yönetimi (YENİ!)
│   ├── siparisler.php       # Sipariş yönetimi
│   ├── kullanicilar.php     # Kullanıcı yönetimi
│   ├── restoranlar.php      # Restoran yönetimi
│   ├── aktiviteler.php      # Aktivite yönetimi
│   ├── takvim.php           # Takvim yönetimi
│   ├── adminler.php         # Admin yönetimi
│   └── bildirimler.php      # Bildirim yönetimi
├── assets/                  # CSS, JS, resimler
├── includes/                # Ortak PHP dosyaları
├── uploads/                 # Yüklenen dosyalar
│   └── urunler/            # Ürün görselleri
├── config.php              # Veritabanı ve ayarlar
├── database.sql            # Ana veritabanı
├── restoran_menu_sistemi.sql
├── siparis_sistemi.sql
├── admin_bildirimler.sql
├── mevcut_sehirler_ekle.sql
├── index.php               # Ana sayfa (DİNAMİK!)
├── login.php               # Kullanıcı giriş
├── sepet.php               # Sepet sistemi
├── siparislerim.php        # Sipariş geçmişi
├── gaziantep.php           # Şehir sayfaları
├── diyarbakir.php
├── adiyaman.php
├── sanliurfa.php
├── batman.php
├── mardin.php
├── mugla.php               # (Örnek: Yeni eklenen)
└── ... (Diğer şehirler)
```

---

## ✨ Yeni Özellikler

### 🏙️ Dinamik Şehir Sistemi
- Admin panelden şehir ekle/düzenle/sil
- Otomatik PHP dosyası oluşturma
- 81 il listesi hazır
- Türkçe karakter desteği

### 🍽️ Restoran Menü Sistemi
- Kategoriler ve ürünler
- Görsel yükleme (otomatik 800x600px)
- Fiyat yönetimi (sadece admin görür)
- Modal popup menü

### 🛒 Sipariş Sistemi
- Sepet yönetimi
- Koltuk numarası ile sipariş
- 30 dakika içinde güncelleme
- Admin bildirim sistemi
- PDF/CSV export

### 👥 Admin Yönetimi
- Çoklu admin desteği
- Şifre değiştirme
- Rol bazlı yetkilendirme

---

## 🎨 Önemli Özellikler

1. **Responsive Tasarım** - Mobil uyumlu
2. **Modern UI/UX** - Gradient renkler, animasyonlar
3. **AJAX Entegrasyonu** - Sayfa yenilemeden işlem
4. **Güvenlik** - SQL injection koruması, XSS koruması
5. **Türkçe Destek** - Tam Türkçe karakter desteği
6. **Otomatik Yedekleme** - Veritabanı yedekleme sistemi

---

## 🔐 Güvenlik Notları

1. **Üretim Ortamında:**
   - Varsayılan admin şifresini değiştirin
   - `config.php` dosyasındaki `DEBUG_MODE`'u kapatın
   - `.htaccess` dosyalarını kontrol edin
   - SSL sertifikası kullanın

2. **Dosya İzinleri:**
   ```bash
   # Dosyalar
   sudo find /Applications/XAMPP/xamppfiles/htdocs/avustur -type f -exec chmod 644 {} \;
   
   # Klasörler
   sudo find /Applications/XAMPP/xamppfiles/htdocs/avustur -type d -exec chmod 755 {} \;
   
   # Uploads klasörü
   sudo chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/avustur/uploads
   ```

---

## 📞 Destek

Herhangi bir sorun yaşarsanız:

1. **Apache Error Log:**
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/error_log
   ```

2. **MySQL Error Log:**
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/mysql_error.log
   ```

3. **PHP Error Log:**
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log
   ```

---

## 🎉 Başarılar!

Kurulum tamamlandıktan sonra şunları yapabilirsiniz:

- ✅ Yeni şehirler ekleyin
- ✅ Restoranlar ve menüler oluşturun
- ✅ Siparişleri yönetin
- ✅ Kullanıcıları yönetin
- ✅ Tur programları oluşturun

**İyi kullanımlar!** 🚀

