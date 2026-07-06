// İlanlar için canlı sayaç.
//  - Açık artırma (data-bitis): bitişe kalan süreyi HH:MM:SS geri sayar.
//  - Düşüş fazı (data-sonraki-dusus): bir sonraki fiyat düşüşüne kalan süre MM:SS.
// Süre bitince ilgili faz için sayfayı yeniler (sunucudan güncel durum gelsin).

function ikiHane(n) {
    return String(n).padStart(2, '0');
}

function bicimleHHMMSS(saniye) {
    const s = Math.max(0, saniye);
    const sa = Math.floor(s / 3600);
    const dk = Math.floor((s % 3600) / 60);
    const sn = s % 60;
    return ikiHane(sa) + ':' + ikiHane(dk) + ':' + ikiHane(sn);
}

function bicimleMMSS(saniye) {
    const s = Math.max(0, saniye);
    const dk = Math.floor(s / 60);
    const sn = s % 60;
    return ikiHane(dk) + ':' + ikiHane(sn);
}

let yenilemeGerekli = false;

function guncelle() {
    const simdi = Math.floor(Date.now() / 1000);

    document.querySelectorAll('.kalem').forEach((kalem) => {
        const sayac = kalem.querySelector('.sayac');
        if (!sayac) {
            return;
        }

        const durum = kalem.dataset.durum;

        if (durum === 'acik_artirma' && kalem.dataset.bitis) {
            const kalan = Number(kalem.dataset.bitis) - simdi;
            sayac.textContent = bicimleHHMMSS(kalan);
            if (kalan <= 120) {
                kalem.classList.add('kritik');
            }
            if (kalan <= 0) {
                yenilemeGerekli = true;
            }
        } else if (durum === 'dusuyor' && kalem.dataset.sonrakiDusus) {
            const kalan = Number(kalem.dataset.sonrakiDusus) - simdi;
            sayac.textContent = bicimleMMSS(kalan);
            if (kalan <= 0) {
                yenilemeGerekli = true; // fiyat düştü, güncel değeri sunucudan al
            }
        }
    });

    if (yenilemeGerekli) {
        window.location.reload();
    }
}

guncelle();
setInterval(guncelle, 1000);
