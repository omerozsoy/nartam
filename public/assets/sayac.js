// Müzayede kalemleri için canlı geri sayım sayacı.
// Her .kalem elemanının data-bitis (unix timestamp, saniye) değerine göre sayar.

function bicimle(kalanSaniye) {
    if (kalanSaniye <= 0) {
        return 'KAPANDI';
    }
    const dk = Math.floor(kalanSaniye / 60);
    const sn = kalanSaniye % 60;
    return String(dk).padStart(2, '0') + ':' + String(sn).padStart(2, '0');
}

function guncelle() {
    const simdi = Math.floor(Date.now() / 1000);

    document.querySelectorAll('.kalem').forEach((kalem) => {
        const bitis = Number(kalem.dataset.bitis);
        const kalan = bitis - simdi;
        const sayac = kalem.querySelector('.sayac');
        const buton = kalem.querySelector('button');

        sayac.textContent = bicimle(kalan);

        if (kalan <= 0) {
            kalem.classList.add('kapali');
            if (buton) {
                buton.disabled = true;
            }
        } else if (kalan <= 30) {
            kalem.classList.add('kritik');
        }
    });
}

guncelle();
setInterval(guncelle, 1000);
