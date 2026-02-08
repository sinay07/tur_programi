-- Admin kullanıcısı oluşturma
-- Kullanıcı adı: admin
-- Şifre: admin123

USE avusturtest;

-- Eski admin varsa sil
DELETE FROM adminler WHERE kullanici_adi = 'admin';

-- Yeni admin ekle (şifre: admin123)
INSERT INTO adminler (kullanici_adi, sifre, ad_soyad, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin@avustur.com');

-- Kontrol
SELECT * FROM adminler;



