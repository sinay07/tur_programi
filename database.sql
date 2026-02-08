-- Tur Şirketi Veritabanı
-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS avusturtest CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE avusturtest;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(100),
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    aktif TINYINT(1) DEFAULT 1,
    INDEX idx_telefon (telefon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Admin kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS adminler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Şehirler tablosu
CREATE TABLE IF NOT EXISTS sehirler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sehir_adi VARCHAR(50) NOT NULL,
    sehir_slug VARCHAR(50) UNIQUE NOT NULL,
    aciklama TEXT,
    gorsel VARCHAR(255),
    aktif TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Takvim (Tur Programı) tablosu
CREATE TABLE IF NOT EXISTS takvim (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sehir_id INT NOT NULL,
    tarih DATE NOT NULL,
    aciklama TEXT,
    aktif TINYINT(1) DEFAULT 1,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sehir_id) REFERENCES sehirler(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tarih_sehir (tarih, sehir_id),
    INDEX idx_tarih (tarih)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Aktiviteler tablosu
CREATE TABLE IF NOT EXISTS aktiviteler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sehir_id INT NOT NULL,
    baslik VARCHAR(200) NOT NULL,
    aciklama TEXT,
    adres TEXT,
    gorsel VARCHAR(255),
    fiyat DECIMAL(10,2) DEFAULT 0,
    sure VARCHAR(50),
    aktif TINYINT(1) DEFAULT 1,
    sira INT DEFAULT 0,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sehir_id) REFERENCES sehirler(id) ON DELETE CASCADE,
    INDEX idx_sehir (sehir_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Restoranlar tablosu
CREATE TABLE IF NOT EXISTS restoranlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sehir_id INT NOT NULL,
    baslik VARCHAR(200) NOT NULL,
    aciklama TEXT,
    adres TEXT,
    gorsel VARCHAR(255),
    mutfak_turu VARCHAR(100),
    ortalama_fiyat VARCHAR(50),
    telefon VARCHAR(15),
    aktif TINYINT(1) DEFAULT 1,
    sira INT DEFAULT 0,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sehir_id) REFERENCES sehirler(id) ON DELETE CASCADE,
    INDEX idx_sehir (sehir_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Şehirleri ekle
INSERT INTO sehirler (sehir_adi, sehir_slug, aciklama, aktif) VALUES
('Gaziantep', 'gaziantep', 'Gastronomi başkenti Gaziantep, lezzetleri ve tarihi dokusuyla benzersiz bir şehir.', 1),
('Diyarbakır', 'diyarbakir', 'Dicle nehri kenarında tarihi surlarıyla ünlü Diyarbakır.', 1),
('Adıyaman', 'adiyaman', 'Nemrut Dağı ve tarihi zenginlikleriyle Adıyaman.', 1),
('Şanlıurfa', 'sanliurfa', 'Peygamberler şehri Şanlıurfa, tarihi ve kültürel zenginlikleriyle dikkat çekiyor.', 1),
('Batman', 'batman', 'Hasankeyf ve tarihi köprüleriyle Batman.', 1),
('Mardin', 'mardin', 'Taş evleri ve eşsiz mimarisiyle Mardin.', 1);

-- Varsayılan admin kullanıcı (kullanıcı adı: admin, şifre: admin123)
INSERT INTO adminler (kullanici_adi, sifre, ad_soyad, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin@avustur.com');

-- Demo kullanıcılar
INSERT INTO kullanicilar (ad_soyad, telefon, email, aktif) VALUES
('Ahmet Yılmaz', '05551234567', 'ahmet@example.com', 1),
('Mehmet Demir', '05559876543', 'mehmet@example.com', 1),
('Ayşe Kaya', '05551112233', 'ayse@example.com', 1);

-- Gaziantep için demo aktiviteler
INSERT INTO aktiviteler (sehir_id, baslik, aciklama, adres, fiyat, sure, sira) VALUES
(1, 'Zeugma Mozaik Müzesi', 'Dünyanın en büyük mozaik müzelerinden biri. Antik çağdan kalma muhteşem mozaikler.', 'Mithatpaşa Mahallesi, Hacı Sani Konukoğlu Bulvarı, Şehitkamil/Gaziantep', 50.00, '2-3 saat', 1),
(1, 'Gaziantep Kalesi', 'Şehrin merkezindeki tarihi kale. Muhteşem şehir manzarası.', 'Kozluca Mahallesi, Kale Sokak, Şahinbey/Gaziantep', 0.00, '1-2 saat', 2),
(1, 'Bakırcılar Çarşısı', 'Geleneksel el sanatları ve bakır işlemeciliğinin yapıldığı tarihi çarşı.', 'Şahinbey/Gaziantep', 0.00, '1 saat', 3),
(1, 'Emine Göğüş Mutfak Müzesi', 'Gaziantep mutfak kültürünü tanıtan interaktif müze.', 'Eyüboğlu Mahallesi, Şehitkamil/Gaziantep', 30.00, '1-2 saat', 4);

-- Gaziantep için demo restoranlar
INSERT INTO restoranlar (sehir_id, baslik, aciklama, adres, mutfak_turu, ortalama_fiyat, telefon, sira) VALUES
(1, 'İmam Çağdaş', 'Gaziantep\'in en ünlü kebap ve baklava mekanlarından biri.', 'Uğur Mumcu Caddesi, Şehitkamil/Gaziantep', 'Türk Mutfağı', '200-400 TL', '03422305555', 1),
(1, 'Orkide Restaurant', 'Modern Türk mutfağı ve Gaziantep lezzetleri.', 'İncilipınar Mahallesi, Şehitkamil/Gaziantep', 'Türk Mutfağı', '300-500 TL', '03423609090', 2),
(1, 'Tahmis Kahvesi', 'Tarihi atmosferde Türk kahvesi ve tatlı keyfi.', 'Kozluca Mahallesi, Şahinbey/Gaziantep', 'Kahve & Tatlı', '50-100 TL', '03422303636', 3);

-- Diyarbakır için demo aktiviteler
INSERT INTO aktiviteler (sehir_id, baslik, aciklama, adres, fiyat, sure, sira) VALUES
(2, 'Diyarbakır Surları', 'UNESCO Dünya Mirası Listesi\'nde yer alan tarihi surlar.', 'Sur İlçesi, Diyarbakır', 0.00, '2-3 saat', 1),
(2, 'Hevsel Bahçeleri', 'Dicle nehri kenarında tarihi bahçeler ve doğal güzellik.', 'Sur İlçesi, Diyarbakır', 0.00, '1-2 saat', 2),
(2, 'On Gözlü Köprü', 'Dicle üzerindeki tarihi taş köprü.', 'Sur İlçesi, Diyarbakır', 0.00, '30 dakika', 3);

-- Diyarbakır için demo restoranlar
INSERT INTO restoranlar (sehir_id, baslik, aciklama, adres, mutfak_turu, ortalama_fiyat, telefon, sira) VALUES
(2, 'Ciğerci Murat', 'Meşhur Diyarbakır ciğeri.', 'Yenişehir/Diyarbakır', 'Türk Mutfağı', '150-250 TL', '04122285555', 1),
(2, 'Selim Amca\'nın Sofrası', 'Geleneksel Diyarbakır mutfağı.', 'Sur/Diyarbakır', 'Türk Mutfağı', '200-350 TL', '04122236666', 2);

-- Şanlıurfa için demo aktiviteler
INSERT INTO aktiviteler (sehir_id, baslik, aciklama, adres, fiyat, sure, sira) VALUES
(4, 'Balıklıgöl', 'Hz. İbrahim\'in ateşe atıldığı kutsal göl.', 'Haliliye/Şanlıurfa', 0.00, '1-2 saat', 1),
(4, 'Göbeklitepe', 'Dünyanın en eski tapınak kompleksi.', 'Örencik Köyü, Şanlıurfa', 100.00, '2-3 saat', 2),
(4, 'Urfa Kalesi', 'Şehrin tepesindeki tarihi kale.', 'Haliliye/Şanlıurfa', 0.00, '1 saat', 3);

-- Şanlıurfa için demo restoranlar
INSERT INTO restoranlar (sehir_id, baslik, aciklama, adres, mutfak_turu, ortalama_fiyat, telefon, sira) VALUES
(4, 'Cevahir Konuk Evi', 'Geleneksel Urfa mutfağı ve atmosferi.', 'Balıklıgöl, Haliliye/Şanlıurfa', 'Türk Mutfağı', '250-400 TL', '04143132222', 1),
(4, 'Çardaklı Restaurant', 'Urfa kebabı ve mezeler.', 'Haliliye/Şanlıurfa', 'Türk Mutfağı', '200-350 TL', '04143151515', 2);

-- Demo takvim verileri (Ekim 2025 için)
INSERT INTO takvim (sehir_id, tarih, aciklama) VALUES
(1, '2025-10-15', 'Gaziantep Tur Programı'),
(1, '2025-10-16', 'Gaziantep Tur Programı - 2. Gün'),
(2, '2025-10-18', 'Diyarbakır Tur Programı'),
(4, '2025-10-20', 'Şanlıurfa Tur Programı'),
(4, '2025-10-21', 'Şanlıurfa Tur Programı - 2. Gün'),
(3, '2025-10-23', 'Adıyaman Tur Programı'),
(6, '2025-10-25', 'Mardin Tur Programı'),
(6, '2025-10-26', 'Mardin Tur Programı - 2. Gün'),
(5, '2025-10-28', 'Batman Tur Programı');

