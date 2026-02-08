// Sepete ekle fonksiyonu (AJAX)
function sepeteEkle(urunId, restoranId, button) {
    // Butonu devre dışı bırak
    button.disabled = true;
    const eskiMetin = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ekleniyor...';
    
    // FormData oluştur
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('urun_id', urunId);
    formData.append('restoran_id', restoranId);
    
    // AJAX ile gönder
    fetch('/avustur/sepet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Başarı bildirimi göster
            gosterBildirim('✅ Ürün sepete eklendi!', 'success');
            
            // Sepet sayacını güncelle
            guncelSepetSayaci();
            
            // Butonu eski haline getir
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = eskiMetin;
            }, 500);
        } else {
            gosterBildirim('❌ Bir hata oluştu!', 'error');
            button.disabled = false;
            button.innerHTML = eskiMetin;
        }
    })
    .catch(error => {
        console.error('Hata:', error);
        gosterBildirim('❌ Bir hata oluştu!', 'error');
        button.disabled = false;
        button.innerHTML = eskiMetin;
    });
}

// Bildirim göster
function gosterBildirim(mesaj, tip) {
    const bildirim = document.createElement('div');
    bildirim.textContent = mesaj;
    bildirim.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${tip === 'success' ? '#48bb78' : '#f56565'};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        font-weight: 600;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(bildirim);
    
    setTimeout(() => {
        bildirim.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => bildirim.remove(), 300);
    }, 2000);
}

// Sepet sayacını güncelle
function guncelSepetSayaci() {
    fetch('/avustur/sepet.php?action=bilgi')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const sepetSayac = document.querySelector('.sepet-sayac');
                if (sepetSayac && data.adet > 0) {
                    sepetSayac.textContent = data.adet;
                    sepetSayac.style.display = 'flex';
                }
            }
        });
}


