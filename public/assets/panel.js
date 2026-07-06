// Üye paneli canlı güncelleme: /api/hesabim'ı periyodik yoklar,
// satırların güncel fiyatını ve durumunu günceller; kazanma/kaybetme
// (kapanış) olunca sayfayı yeniler ki "Kazandıklarım" bölümüne taşınsın.

const PANEL_POLL = 5000;
const PANEL_ETIKET = { onde: 'Önde', geride: 'Geçildiniz', kazandi: 'Kazandınız', kaybetti: 'Kaybettiniz' };

async function panelGuncelle() {
    try {
        const yanit = await fetch('/api/hesabim', { headers: { Accept: 'application/json' } });
        if (!yanit.ok) {
            return;
        }
        const satirlar = await yanit.json();
        const idIle = {};
        satirlar.forEach((s) => { idIle[s.id] = s; });

        let yenile = false;
        document.querySelectorAll('#hesap-panel tr[data-id]').forEach((tr) => {
            const s = idIle[tr.dataset.id];
            if (!s) {
                return;
            }
            const fiyat = tr.querySelector('[data-alan="h-fiyat"]');
            if (fiyat) {
                fiyat.textContent = s.guncelFiyatBicim;
            }
            const durum = tr.querySelector('[data-alan="h-durum"]');
            if (durum) {
                durum.textContent = PANEL_ETIKET[s.durumum] ?? s.durumum;
                durum.className = 'durum-etiket d-' + s.durumum;
            }
            if (tr.dataset.durumum !== s.durumum && (s.durumum === 'kazandi' || s.durumum === 'kaybetti')) {
                yenile = true;
            }
            tr.dataset.durumum = s.durumum;
        });

        if (yenile) {
            window.location.reload();
        }
    } catch (e) {
        // ağ hatası: sessizce geç
    }
}

if (document.getElementById('hesap-panel')) {
    setInterval(panelGuncelle, PANEL_POLL);
}
