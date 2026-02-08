-- Restoran Menü Sistemi Tabloları
-- Kategoriler ve Ürünler için

USE avusturtest;

-- Menü Kategorileri Tablosu
CREATE TABLE IF NOT EXISTS menu_kategoriler (
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

-- Menü Ürünleri Tablosu
CREATE TABLE IF NOT EXISTS menu_urunler (
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

-- Demo Kategoriler (Gaziantep - İmam Çağdaş için)
INSERT INTO menu_kategoriler (restoran_id, kategori_adi, aciklama, sira) VALUES
(1, 'Kebaplar', 'Gaziantep\'in meşhur kebapları', 1),
(1, 'Ara Sıcaklar', 'Sıcak mezeler ve ara yemekler', 2),
(1, 'Tatlılar', 'Meşhur Antep baklavası ve tatlılar', 3),
(1, 'İçecekler', 'Soğuk ve sıcak içecekler', 4);

-- Demo Ürünler
INSERT INTO menu_urunler (kategori_id, urun_adi, aciklama, fiyat, porsiyon_bilgisi, sira) VALUES
-- Kebaplar
(1, 'Beyran Çorbası', 'Geleneksel Gaziantep beyran çorbası', 180.00, '1 Porsiyon', 1),
(1, 'Ali Nazik Kebap', 'Közlenmiş patlıcan, yoğurt ve kebap', 320.00, '1 Porsiyon', 2),
(1, 'Adana Kebap', 'Özel baharatlarla hazırlanmış Adana kebap', 280.00, '1 Porsiyon', 3),
(1, 'Urfa Kebap', 'Az acılı Urfa kebabı', 280.00, '1 Porsiyon', 4),
(1, 'Patlıcan Kebap', 'Közlenmiş patlıcan ile kebap', 290.00, '1 Porsiyon', 5),

-- Ara Sıcaklar
(2, 'Humus', 'Nohut ezmesi, tahin ve zeytinyağı', 120.00, '1 Porsiyon', 1),
(2, 'Ezme', 'Domates, biber, soğan ezmesi', 100.00, '1 Porsiyon', 2),
(2, 'Muhammara', 'Kırmızı biber ve ceviz ezmesi', 130.00, '1 Porsiyon', 3),
(2, 'Lahmacun', 'İnce hamurlu Gaziantep lahmacunu', 65.00, '1 Adet', 4),

-- Tatlılar
(3, 'Fıstıklı Baklava', 'Antep fıstığı ile hazırlanmış baklava', 220.00, '1 Porsiyon', 1),
(3, 'Burma Kadayıf', 'Tel kadayıf sarma tatlı', 200.00, '1 Porsiyon', 2),
(3, 'Künefe', 'Sıcak, peynirli künefe', 180.00, '1 Porsiyon', 3),
(3, 'Katmer', 'Kaymak ve fıstıklı katmer', 160.00, '1 Porsiyon', 4),

-- İçecekler
(4, 'Ayran', 'Ev yapımı ayran', 35.00, '1 Bardak', 1),
(4, 'Şalgam', 'Acılı/Acısız şalgam suyu', 40.00, '1 Bardak', 2),
(4, 'Türk Kahvesi', 'Geleneksel Türk kahvesi', 60.00, '1 Fincan', 3),
(4, 'Çay', 'Demleme çay', 20.00, '1 Bardak', 4);

-- Kontrol
SELECT 'Kategoriler' as Tablo, COUNT(*) as Adet FROM menu_kategoriler
UNION ALL
SELECT 'Ürünler', COUNT(*) FROM menu_urunler;



