-- Mevcut Şehirleri Veritabanına Ekle
-- Bu dosyayı phpMyAdmin'de çalıştırın

-- Önce mevcut şehirleri temizle (eğer varsa)
-- DELETE FROM sehirler;

-- Mevcut şehirleri ekle
INSERT INTO sehirler (sehir_adi, sehir_slug, aciklama, aktif) VALUES
('Gaziantep', 'gaziantep', 'Gastronomi başkenti, baklava ve kebabın anavatanı', 1),
('Diyarbakır', 'diyarbakir', 'Tarihi surları ve Dicle nehriyle büyüleyici', 1),
('Adıyaman', 'adiyaman', 'Nemrut Dağı ve muhteşem gün doğumları', 1),
('Şanlıurfa', 'sanliurfa', 'Peygamberler şehri, Balıklıgöl ve Göbeklitepe', 1),
('Batman', 'batman', 'Hasankeyf\'in tarihi hazineleri', 1),
('Mardin', 'mardin', 'Taş evleri ve benzersiz mimarisi', 1)
ON DUPLICATE KEY UPDATE aciklama = VALUES(aciklama), aktif = VALUES(aktif);

