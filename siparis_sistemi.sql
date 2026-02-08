-- Sipariş Sistemi Veritabanı Tabloları
USE avusturtest;

-- Siparişler Tablosu
CREATE TABLE IF NOT EXISTS siparisler (
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

-- Sipariş Ürünleri Tablosu
CREATE TABLE IF NOT EXISTS siparis_urunler (
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

-- Demo Sipariş (Test için)
INSERT INTO siparisler (kullanici_id, restoran_id, koltuk_no, toplam_fiyat, durum) VALUES
(1, 1, '14', 615.00, 'beklemede'),
(2, 1, '7', 680.00, 'hazirlaniyor'),
(3, 1, '19', 575.00, 'teslim_edildi');

-- Demo Sipariş Ürünleri
INSERT INTO siparis_urunler (siparis_id, urun_id, urun_adi, kategori_adi, adet, birim_fiyat, toplam_fiyat) VALUES
-- Mehmet'in siparişi (siparis_id: 1)
(1, 3, 'Adana Kebap', 'Kebaplar', 1, 280.00, 280.00),
(1, 4, 'Urfa Kebap', 'Kebaplar', 1, 280.00, 280.00),
(1, 17, 'Ayran', 'İçecekler', 2, 35.00, 70.00),

-- Elif'in siparişi (siparis_id: 2)
(2, 2, 'Ali Nazik Kebap', 'Kebaplar', 1, 320.00, 320.00),
(2, 13, 'Fıstıklı Baklava', 'Tatlılar', 1, 220.00, 220.00),
(2, 19, 'Türk Kahvesi', 'İçecekler', 2, 60.00, 120.00),
(2, 20, 'Çay', 'İçecekler', 1, 20.00, 20.00),

-- Caner'in siparişi (siparis_id: 3)
(3, 5, 'Patlıcan Kebap', 'Kebaplar', 1, 290.00, 290.00),
(3, 15, 'Künefe', 'Tatlılar', 1, 180.00, 180.00),
(3, 18, 'Şalgam', 'İçecekler', 2, 40.00, 80.00),
(3, 20, 'Çay', 'İçecekler', 1, 20.00, 20.00);

-- Kontrol
SELECT 'Siparişler' as Tablo, COUNT(*) as Adet FROM siparisler
UNION ALL
SELECT 'Sipariş Ürünleri', COUNT(*) FROM siparis_urunler;

-- Sipariş özeti görünümü
SELECT 
    s.id as siparis_no,
    k.ad_soyad,
    k.telefon,
    r.baslik as restoran,
    s.koltuk_no,
    s.toplam_fiyat,
    s.durum,
    s.siparis_tarihi,
    COUNT(su.id) as urun_sayisi
FROM siparisler s
INNER JOIN kullanicilar k ON s.kullanici_id = k.id
INNER JOIN restoranlar r ON s.restoran_id = r.id
LEFT JOIN siparis_urunler su ON s.id = su.siparis_id
GROUP BY s.id
ORDER BY s.siparis_tarihi DESC;



