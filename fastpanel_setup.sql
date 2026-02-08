-- Avustur Projesi FastPanel Kurulum Skripti
-- Veritabanı: avusturtest
-- Kullanıcı: avusturtest_usr / 123123Aa (FastPanel'de önceden oluşturulmalı)

CREATE DATABASE IF NOT EXISTS avusturtest CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE avusturtest;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admin_bildirimler;
DROP TABLE IF EXISTS siparis_urunler;
DROP TABLE IF EXISTS siparisler;
DROP TABLE IF EXISTS menu_urunler;
DROP TABLE IF EXISTS menu_kategoriler;
DROP TABLE IF EXISTS restoranlar;
DROP TABLE IF EXISTS aktiviteler;
DROP TABLE IF EXISTS takvim;
DROP TABLE IF EXISTS sehirler;
DROP TABLE IF EXISTS adminler;
DROP TABLE IF EXISTS kullanicilar;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(100),
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    aktif TINYINT(1) DEFAULT 1,
    INDEX idx_telefon (telefon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE adminler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE sehirler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sehir_adi VARCHAR(50) NOT NULL,
    sehir_slug VARCHAR(50) UNIQUE NOT NULL,
    aciklama TEXT,
    gorsel VARCHAR(255),
    aktif TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE takvim (
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

CREATE TABLE aktiviteler (
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

CREATE TABLE restoranlar (
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

CREATE TABLE menu_kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restoran_id INT NOT NULL,
    kategori_adi VARCHAR(100) NOT NULL,
    aciklama TEXT,
    sira INT DEFAULT 0,
    aktif TINYINT(1) DEFAULT 1,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restoran_id) REFERENCES restoranlar(id) ON DELETE CASCADE,
    INDEX idx_restoran (restoran_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE menu_urunler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    urun_adi VARCHAR(200) NOT NULL,
    aciklama TEXT,
    fiyat DECIMAL(10,2) DEFAULT 0,
    gorsel VARCHAR(255),
    porsiyon_bilgisi VARCHAR(100),
    kalori VARCHAR(50),
    aktif TINYINT(1) DEFAULT 1,
    sira INT DEFAULT 0,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES menu_kategoriler(id) ON DELETE CASCADE,
    INDEX idx_kategori (kategori_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE siparisler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    restoran_id INT NOT NULL,
    koltuk_no VARCHAR(10) NOT NULL,
    toplam_fiyat DECIMAL(10,2) DEFAULT 0,
    durum ENUM('beklemede', 'hazirlaniyor', 'yolda', 'teslim_edildi', 'iptal') DEFAULT 'beklemede',
    siparis_notu TEXT,
    siparis_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (restoran_id) REFERENCES restoranlar(id) ON DELETE CASCADE,
    INDEX idx_kullanici (kullanici_id),
    INDEX idx_restoran (restoran_id),
    INDEX idx_durum (durum),
    INDEX idx_tarih (siparis_tarihi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE siparis_urunler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siparis_id INT NOT NULL,
    urun_id INT NOT NULL,
    urun_adi VARCHAR(200) NOT NULL,
    kategori_adi VARCHAR(100) NOT NULL,
    adet INT DEFAULT 1,
    birim_fiyat DECIMAL(10,2) DEFAULT 0,
    toplam_fiyat DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (siparis_id) REFERENCES siparisler(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_id) REFERENCES menu_urunler(id) ON DELETE CASCADE,
    INDEX idx_siparis (siparis_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE admin_bildirimler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(200) NOT NULL,
    mesaj TEXT NOT NULL,
    tip ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    okundu TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_okundu (okundu),
    INDEX idx_tarih (olusturma_tarihi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT INTO sehirler (sehir_adi, sehir_slug, aciklama, aktif) VALUES
('Gaziantep', 'gaziantep', 'Gastronomi başkenti Gaziantep, lezzetleri ve tarihi dokusuyla benzersiz bir şehir.', 1),
('Diyarbakır', 'diyarbakir', 'Dicle nehri kenarında tarihi surlarıyla ünlü Diyarbakır.', 1),
('Adıyaman', 'adiyaman', 'Nemrut Dağı ve tarihi zenginlikleriyle Adıyaman.', 1),
('Şanlıurfa', 'sanliurfa', 'Peygamberler şehri Şanlıurfa, tarihi ve kültürel zenginlikleriyle dikkat çekiyor.', 1),
('Batman', 'batman', 'Hasankeyf ve tarihi köprüleriyle Batman.', 1),
('Mardin', 'mardin', 'Taş evleri ve eşsiz mimarisiyle Mardin.', 1);

INSERT INTO adminler (kullanici_adi, sifre, ad_soyad, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin@avustur.com')
ON DUPLICATE KEY UPDATE kullanici_adi = VALUES(kullanici_adi);

INSERT INTO kullanicilar (ad_soyad, telefon, email, aktif) VALUES
('Ahmet Yılmaz', '05551234567', 'ahmet@example.com', 1),
('Mehmet Demir', '05559876543', 'mehmet@example.com', 1),
('Ayşe Kaya', '05551112233', 'ayse@example.com', 1);

INSERT INTO aktiviteler (sehir_id, baslik, aciklama, adres, fiyat, sure, sira) VALUES
(1, 'Zeugma Mozaik Müzesi', 'Dünyanın en büyük mozaik müzelerinden biri. Antik çağdan kalma muhteşem mozaikler.', 'Mithatpaşa Mahallesi, Hacı Sani Konukoğlu Bulvarı, Şehitkamil/Gaziantep', 50.00, '2-3 saat', 1),
(1, 'Gaziantep Kalesi', 'Şehrin merkezindeki tarihi kale. Muhteşem şehir manzarası.', 'Kozluca Mahallesi, Kale Sokak, Şahinbey/Gaziantep', 0.00, '1-2 saat', 2),
(1, 'Bakırcılar Çarşısı', 'Geleneksel el sanatları ve bakır işlemeciliğinin yapıldığı tarihi çarşı.', 'Şahinbey/Gaziantep', 0.00, '1 saat', 3),
(1, 'Emine Göğüş Mutfak Müzesi', 'Gaziantep mutfak kültürünü tanıtan interaktif müze.', 'Eyüboğlu Mahallesi, Şehitkamil/Gaziantep', 30.00, '1-2 saat', 4),
(2, 'Diyarbakır Surları', 'UNESCO Dünya Mirası Listesi''nde yer alan tarihi surlar.', 'Sur İlçesi, Diyarbakır', 0.00, '2-3 saat', 1),
(2, 'Hevsel Bahçeleri', 'Dicle nehri kenarında tarihi bahçeler ve doğal güzellik.', 'Sur İlçesi, Diyarbakır', 0.00, '1-2 saat', 2),
(2, 'On Gözlü Köprü', 'Dicle üzerindeki tarihi taş köprü.', 'Sur İlçesi, Diyarbakır', 0.00, '30 dakika', 3),
(4, 'Balıklıgöl', 'Hz. İbrahim''in ateşe atıldığı kutsal göl.', 'Haliliye/Şanlıurfa', 0.00, '1-2 saat', 1),
(4, 'Göbeklitepe', 'Dünyanın en eski tapınak kompleksi.', 'Örencik Köyü, Şanlıurfa', 100.00, '2-3 saat', 2),
(4, 'Urfa Kalesi', 'Şehrin tepesindeki tarihi kale.', 'Haliliye/Şanlıurfa', 0.00, '1 saat', 3);

INSERT INTO restoranlar (sehir_id, baslik, aciklama, adres, mutfak_turu, ortalama_fiyat, telefon, sira) VALUES
(1, 'İmam Çağdaş', 'Gaziantep''in en ünlü kebap ve baklava mekanlarından biri.', 'Uğur Mumcu Caddesi, Şehitkamil/Gaziantep', 'Türk Mutfağı', '200-400 TL', '03422305555', 1),
(1, 'Orkide Restaurant', 'Modern Türk mutfağı ve Gaziantep lezzetleri.', 'İncilipınar Mahallesi, Şehitkamil/Gaziantep', 'Türk Mutfağı', '300-500 TL', '03423609090', 2),
(1, 'Tahmis Kahvesi', 'Tarihi atmosferde Türk kahvesi ve tatlı keyfi.', 'Kozluca Mahallesi, Şahinbey/Gaziantep', 'Kahve & Tatlı', '50-100 TL', '03422303636', 3),
(2, 'Ciğerci Murat', 'Meşhur Diyarbakır ciğeri.', 'Yenişehir/Diyarbakır', 'Türk Mutfağı', '150-250 TL', '04122285555', 1),
(2, 'Selim Amca''nın Sofrası', 'Geleneksel Diyarbakır mutfağı.', 'Sur/Diyarbakır', 'Türk Mutfağı', '200-350 TL', '04122236666', 2),
(4, 'Cevahir Konuk Evi', 'Geleneksel Urfa mutfağı ve atmosferi.', 'Balıklıgöl, Haliliye/Şanlıurfa', 'Türk Mutfağı', '250-400 TL', '04143132222', 1),
(4, 'Çardaklı Restaurant', 'Urfa kebabı ve mezeler.', 'Haliliye/Şanlıurfa', 'Türk Mutfağı', '200-350 TL', '04143151515', 2);

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

INSERT INTO menu_kategoriler (restoran_id, kategori_adi, aciklama, sira) VALUES
(1, 'Kebaplar', 'Gaziantep''in meşhur kebapları', 1),
(1, 'Ara Sıcaklar', 'Sıcak mezeler ve ara yemekler', 2),
(1, 'Tatlılar', 'Meşhur Antep baklavası ve tatlılar', 3),
(1, 'İçecekler', 'Soğuk ve sıcak içecekler', 4);

INSERT INTO menu_urunler (kategori_id, urun_adi, aciklama, fiyat, porsiyon_bilgisi, sira) VALUES
(1, 'Beyran Çorbası', 'Geleneksel Gaziantep beyran çorbası', 180.00, '1 Porsiyon', 1),
(1, 'Ali Nazik Kebap', 'Közlenmiş patlıcan, yoğurt ve kebap', 320.00, '1 Porsiyon', 2),
(1, 'Adana Kebap', 'Özel baharatlarla hazırlanmış Adana kebap', 280.00, '1 Porsiyon', 3),
(1, 'Urfa Kebap', 'Az acılı Urfa kebabı', 280.00, '1 Porsiyon', 4),
(1, 'Patlıcan Kebap', 'Közlenmiş patlıcan ile kebap', 290.00, '1 Porsiyon', 5),
(2, 'Humus', 'Nohut ezmesi, tahin ve zeytinyağı', 120.00, '1 Porsiyon', 1),
(2, 'Ezme', 'Domates, biber, soğan ezmesi', 100.00, '1 Porsiyon', 2),
(2, 'Muhammara', 'Kırmızı biber ve ceviz ezmesi', 130.00, '1 Porsiyon', 3),
(2, 'Lahmacun', 'İnce hamurlu Gaziantep lahmacunu', 65.00, '1 Adet', 4),
(3, 'Fıstıklı Baklava', 'Antep fıstığı ile hazırlanmış baklava', 220.00, '1 Porsiyon', 1),
(3, 'Burma Kadayıf', 'Tel kadayıf sarma tatlı', 200.00, '1 Porsiyon', 2),
(3, 'Künefe', 'Sıcak, peynirli künefe', 180.00, '1 Porsiyon', 3),
(3, 'Katmer', 'Kaymak ve fıstıklı katmer', 160.00, '1 Porsiyon', 4),
(4, 'Ayran', 'Ev yapımı ayran', 35.00, '1 Bardak', 1),
(4, 'Şalgam', 'Acılı/Acısız şalgam suyu', 40.00, '1 Bardak', 2),
(4, 'Türk Kahvesi', 'Geleneksel Türk kahvesi', 60.00, '1 Fincan', 3),
(4, 'Çay', 'Demleme çay', 20.00, '1 Bardak', 4);

INSERT INTO siparisler (kullanici_id, restoran_id, koltuk_no, toplam_fiyat, durum) VALUES
(1, 1, '14', 615.00, 'beklemede'),
(2, 1, '7', 680.00, 'hazirlaniyor'),
(3, 1, '19', 575.00, 'teslim_edildi');

INSERT INTO siparis_urunler (siparis_id, urun_id, urun_adi, kategori_adi, adet, birim_fiyat, toplam_fiyat) VALUES
(1, 3, 'Adana Kebap', 'Kebaplar', 1, 280.00, 280.00),
(1, 4, 'Urfa Kebap', 'Kebaplar', 1, 280.00, 280.00),
(1, 17, 'Ayran', 'İçecekler', 2, 35.00, 70.00),
(2, 2, 'Ali Nazik Kebap', 'Kebaplar', 1, 320.00, 320.00),
(2, 13, 'Fıstıklı Baklava', 'Tatlılar', 1, 220.00, 220.00),
(2, 19, 'Türk Kahvesi', 'İçecekler', 2, 60.00, 120.00),
(2, 20, 'Çay', 'İçecekler', 1, 20.00, 20.00),
(3, 5, 'Patlıcan Kebap', 'Kebaplar', 1, 290.00, 290.00),
(3, 15, 'Künefe', 'Tatlılar', 1, 180.00, 180.00),
(3, 18, 'Şalgam', 'İçecekler', 2, 40.00, 80.00),
(3, 20, 'Çay', 'İçecekler', 1, 20.00, 20.00);

SET FOREIGN_KEY_CHECKS = 1;

