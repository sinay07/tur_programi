# 🏙️ Dinamik Şehir Yönetim Sistemi

## 📋 Özellikler

### ✅ Tamamlanan İşlemler
1. **Admin Panel - Şehir Yönetimi**
   - Şehirleri listele, ekle, düzenle, sil
   - Türkiye'nin 81 ilinden seçim
   - Otomatik PHP dosyası oluşturma
   - İstatistikler ve hızlı yönlendirmeler

2. **Dinamik Ana Sayfa**
   - Veritabanından şehir kartları
   - Sadece aktif şehirler gösterilir
   - Alfabetik sıralama

3. **Otomatik Dosya Oluşturma**
   - `sehir_slug.php` otomatik oluşturulur
   - Tam fonksiyonel template
   - Aktiviteler, restoranlar, menü sistemi

---

## 🚀 Kurulum

### Adım 1: SQL Dosyasını Çalıştır
```
1. phpMyAdmin'i aç: http://localhost/phpmyadmin
2. avusturtest veritabanını seç
3. "SQL" sekmesine tıkla
4. mevcut_sehirler_ekle.sql dosyasının içeriğini yapıştır
5. "Go" butonuna tıkla
```

### Adım 2: Admin Panele Git
```
URL: http://localhost/avustur/admin/sehirler.php
```

---

## 📖 Kullanım Kılavuzu

### Yeni Şehir Ekleme

1. **Admin Panel > Şehirler** menüsüne git
2. **"Yeni Şehir Ekle"** butonuna tıkla
3. **Şehir Seçimi:**
   - Dropdown'dan şehir seç (sadece henüz eklenmemiş şehirler)
   - Açıklama otomatik yüklenecek
4. **İkon Seçimi:**
   - Font Awesome ikon sınıfı gir (örn: `fas fa-landmark`)
   - Liste: https://fontawesome.com/icons
5. **Renk Seçimi:**
   - 10 farklı gradient'den birini seç
   - Önizleme kutusunda göreceksin
6. **"Şehir Ekle ve Dosya Oluştur"** butonuna tıkla

### Sonuç:
- ✅ Veritabanına şehir eklendi
- ✅ `sehir_slug.php` dosyası oluşturuldu
- ✅ Ana sayfada şehir kartı göründü
- ✅ Aktivite ve restoran eklemeye hazır

---

### Şehir Düzenleme

1. **Admin Panel > Şehirler** menüsünde **"Düzenle"** butonuna tıkla
2. Şehir adı, açıklama ve aktif/pasif durumunu değiştir
3. **Not:** Slug değiştirilemez (dosya adı korunur)
4. **"Kaydet"** butonuna tıkla

**Ek Özellikler:**
- İstatistikler (aktivite, restoran, tur sayısı)
- Hızlı yönlendirme butonları

---

### Şehir Silme

1. **Admin Panel > Şehirler** menüsünde **"Sil"** butonuna tıkla
2. **Uyarı sayfası** açılacak:
   - Silinecek aktivite sayısı
   - Silinecek restoran sayısı
   - Silinecek tur programı sayısı
   - PHP dosyası adı
3. **"Evet, Şehri Sil"** butonuna tıkla

**Not:** Bu işlem geri alınamaz!

---

## 🎨 Font Awesome İkonlar

Popüler ikonlar:
```
fas fa-utensils          (Yemek)
fas fa-fort-awesome      (Kale)
fas fa-mountain          (Dağ)
fas fa-mosque            (Cami)
fas fa-landmark          (Tarihi Yapı)
fas fa-home              (Ev)
fas fa-map-marker-alt    (Konum)
fas fa-city              (Şehir)
fas fa-monument          (Anıt)
fas fa-university        (Üniversite)
```

**Daha fazlası için:** https://fontawesome.com/icons

---

## 🌈 Gradient Renk Paletleri

1. **Mor-Mavi:** `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
2. **Pembe-Kırmızı:** `linear-gradient(135deg, #f093fb 0%, #f5576c 100%)`
3. **Açık Mavi:** `linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)`
4. **Yeşil-Turkuaz:** `linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)`
5. **Pembe-Sarı:** `linear-gradient(135deg, #fa709a 0%, #fee140 100%)`
6. **Turkuaz-Mor:** `linear-gradient(135deg, #30cfd0 0%, #330867 100%)`
7. **Pastel Mavi-Pembe:** `linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)`
8. **Turuncu-Pembe:** `linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)`
9. **Leylak-Mavi:** `linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%)`
10. **Sarı-Açık Mavi:** `linear-gradient(135deg, #fddb92 0%, #d1fdff 100%)`

---

## 📂 Dosya Yapısı

```
avustur/
├── admin/
│   ├── sehirler.php           # Şehir listesi
│   ├── sehir_ekle.php         # Yeni şehir ekle
│   ├── sehir_duzenle.php      # Şehir düzenle
│   └── sehir_sil.php          # Şehir sil
├── index.php                   # Ana sayfa (dinamik)
├── gaziantep.php               # Şehir sayfası
├── diyarbakir.php              # Şehir sayfası
├── ... (diğer şehirler)
└── mevcut_sehirler_ekle.sql   # SQL kurulum dosyası
```

---

## 🔧 Teknik Detaylar

### Veritabanı Tablosu: `sehirler`
```sql
id              INT (Primary Key)
sehir_adi       VARCHAR(50)
sehir_slug      VARCHAR(50) UNIQUE
aciklama        TEXT
gorsel          VARCHAR(255)
aktif           TINYINT(1)
```

### Slug Oluşturma
- Türkçe karakterler İngilizce'ye çevrilir
- Boşluklar ve özel karakterler kaldırılır
- Küçük harfe dönüştürülür

Örnek:
- **Şanlıurfa** → `sanliurfa`
- **Kahramanmaraş** → `kahramanmaras`

### CASCADE Silme
Şehir silindiğinde otomatik silinen veriler:
- Aktiviteler
- Restoranlar
- Menü kategorileri ve ürünleri
- Tur programları

---

## ✨ Örnekler

### Örnek 1: Kayseri Ekleme
```
Şehir Adı: Kayseri
Slug: kayseri (otomatik)
Açıklama: Erciyes ve tarihi zenginlikler
İkon: fas fa-mountain
Gradient: Yeşil-Turkuaz
```

**Sonuç:**
- `kayseri.php` dosyası oluşturuldu
- Ana sayfada Kayseri kartı göründü
- http://localhost/avustur/kayseri.php aktif

### Örnek 2: İstanbul Ekleme
```
Şehir Adı: İstanbul
Slug: istanbul (otomatik)
Açıklama: İki kıtanın buluşma noktası
İkon: fas fa-mosque
Gradient: Mor-Mavi
```

**Sonuç:**
- `istanbul.php` dosyası oluşturuldu
- Ana sayfada İstanbul kartı göründü
- http://localhost/avustur/istanbul.php aktif

---

## 🐛 Sorun Giderme

### Şehir eklenmiyor
- ✅ SQL dosyasını çalıştırdın mı?
- ✅ Veritabanı bağlantısı aktif mi?
- ✅ `sehirler` tablosu var mı?

### PHP dosyası oluşturulmuyor
- ✅ Klasör yazma izinleri kontrol et
- ✅ `C:\xampp\htdocs\avustur\` klasörüne yazma izni var mı?

### Ana sayfada şehirler görünmüyor
- ✅ Şehir **aktif** mi kontrol et
- ✅ `mevcut_sehirler_ekle.sql` çalıştırıldı mı?
- ✅ Tarayıcı cache'ini temizle

---

## 🎯 İpuçları

1. **İkon Seçimi:** Şehrin karakterine uygun ikon seç
2. **Renk Seçimi:** Şehrin ruh haline uygun gradient seç
3. **Açıklama:** Kısa ve çarpıcı ol (max 100 karakter)
4. **Test:** Ekledikten sonra kullanıcı girişi yap ve test et
5. **İçerik:** Şehri ekledikten sonra aktivite ve restoran ekle

---

## 📞 Destek

Herhangi bir sorunla karşılaşırsan:
1. Tarayıcı console'una bak (F12)
2. PHP hata log'larını kontrol et
3. Veritabanı bağlantısını test et

---

## 🎉 Tebrikler!

Artık dinamik şehir sistemi çalışıyor! Türkiye'nin tüm 81 ilini ekleyebilir ve yönetebilirsin! 🚀

**Hoş kullanımlar!** ✨

