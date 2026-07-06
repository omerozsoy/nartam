// Canlı müzayede istemcisi:
//  1) Her saniye yerel geri sayım (sayaç).
//  2) Her birkaç saniyede /api/ilanlar'dan güncel fiyat/durum çeker (polling).
//  3) Teklif formunu AJAX ile gönderir; sonucu anında uygular.

const POLL_MS = 4000;

function ikiHane(n) {
    return String(n).padStart(2, '0');
}

function bicimleHHMMSS(sn) {
    sn = Math.max(0, sn);
    return ikiHane(Math.floor(sn / 3600)) + ':' + ikiHane(Math.floor((sn % 3600) / 60)) + ':' + ikiHane(sn % 60);
}

function bicimleMMSS(sn) {
    sn = Math.max(0, sn);
    return ikiHane(Math.floor(sn / 60)) + ':' + ikiHane(sn % 60);
}

// --- 1) Her saniye sayaç ---
let yenilemeGerekli = false;

function sayaclariGuncelle() {
    const simdi = Math.floor(Date.now() / 1000);

    document.querySelectorAll('.kalem').forEach((kalem) => {
        const sayac = kalem.querySelector('[data-alan="sayac"]');
        if (!sayac) {
            return;
        }
        const durum = kalem.dataset.durum;

        if (durum === 'acik_artirma' && kalem.dataset.bitis) {
            const kalan = Number(kalem.dataset.bitis) - simdi;
            sayac.textContent = bicimleHHMMSS(kalan);
            kalem.classList.toggle('kritik', kalan <= 120);
            if (kalan <= 0) {
                yenilemeGerekli = true; // kapandı -> sunucudan taze durum al
            }
        } else if (durum === 'dusuyor' && kalem.dataset.sonrakiDusus) {
            const kalan = Number(kalem.dataset.sonrakiDusus) - simdi;
            sayac.textContent = bicimleMMSS(kalan);
            if (kalan <= 0) {
                yenilemeGerekli = true; // fiyat düştü -> taze fiyat al
            }
        }
    });

    if (yenilemeGerekli) {
        yenilemeGerekli = false;
        ilanlariCek();
    }
}

// --- 2) Polling: sunucudan güncel özetler ---
function ozetUygula(kalem, o) {
    const oncekiDurum = kalem.dataset.durum;

    kalem.classList.remove('durum-dusuyor', 'durum-acik_artirma', 'durum-kapandi');
    kalem.classList.add('durum-' + o.durum);
    kalem.dataset.durum = o.durum;
    kalem.dataset.min = o.minTeklif;

    if (o.bitisTs) {
        kalem.dataset.bitis = o.bitisTs;
    } else {
        delete kalem.dataset.bitis;
    }
    if (o.sonrakiDususTs) {
        kalem.dataset.sonrakiDusus = o.sonrakiDususTs;
    } else {
        delete kalem.dataset.sonrakiDusus;
    }

    const fiyat = kalem.querySelector('[data-alan="fiyat"]');
    if (fiyat) {
        fiyat.textContent = o.guncelFiyatBicim;
    }
    const rozet = kalem.querySelector('.rozet');
    if (rozet) {
        rozet.textContent = o.durumEtiket;
    }

    // Yapı değişimi (faz/kapanış) olduğunda formu yeniden çizmek yerine sayfayı tazele.
    if (o.durum === 'kapandi' || (oncekiDurum === 'dusuyor' && o.durum === 'acik_artirma')) {
        window.location.reload();
        return;
    }

    // Min teklif yükseldiyse input'u ayarla (kullanıcının yazdığından fazlaysa).
    const miktar = kalem.querySelector('[data-alan="miktar"]');
    if (miktar) {
        miktar.min = o.minTeklif;
        if (Number(miktar.value) < o.minTeklif) {
            miktar.value = o.minTeklif;
        }
    }
}

async function ilanlariCek() {
    try {
        const yanit = await fetch('/api/ilanlar', { headers: { Accept: 'application/json' } });
        if (!yanit.ok) {
            return;
        }
        const ilanlar = await yanit.json();
        ilanlar.forEach((o) => {
            const kalem = document.querySelector('.kalem[data-id="' + o.id + '"]');
            if (kalem) {
                ozetUygula(kalem, o);
            }
        });
    } catch (e) {
        // ağ hatası: sessizce geç, bir sonraki turda tekrar dener
    }
}

// --- 3) AJAX teklif ---
function teklifBagla() {
    document.querySelectorAll('.teklif-form').forEach((form) => {
        form.addEventListener('submit', async (olay) => {
            olay.preventDefault();
            const mesaj = form.querySelector('[data-alan="teklif-mesaj"]');
            const veri = new FormData(form);
            veri.append('ajax', '1');

            try {
                const yanit = await fetch('/teklif', { method: 'POST', body: veri });
                const sonuc = await yanit.json();
                if (sonuc.ok) {
                    mesaj.textContent = '✓ Teklif alındı';
                    mesaj.className = 'teklif-mesaj basarili';
                    ozetUygula(form.closest('.kalem'), sonuc.ilan);
                } else {
                    mesaj.textContent = sonuc.hata || 'Teklif reddedildi';
                    mesaj.className = 'teklif-mesaj hatali';
                }
            } catch (e) {
                mesaj.textContent = 'Bağlantı hatası';
                mesaj.className = 'teklif-mesaj hatali';
            }
        });
    });
}

sayaclariGuncelle();
setInterval(sayaclariGuncelle, 1000);
setInterval(ilanlariCek, POLL_MS);
teklifBagla();
