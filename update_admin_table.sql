-- Admin tablosuna kayit_tarihi kolonu ekle
ALTER TABLE adminler ADD COLUMN kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Mevcut adminlerin kayit_tarihi'ni güncelle (şu anki zaman)
UPDATE adminler SET kayit_tarihi = NOW() WHERE kayit_tarihi IS NULL;

