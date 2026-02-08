-- Admin Bildirim Sistemi
USE avusturtest;

-- Bildirimler Tablosu
CREATE TABLE IF NOT EXISTS admin_bildirimler (
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


