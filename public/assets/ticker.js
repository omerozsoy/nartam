// Menü altı canlı fiyat ticker'ı (CSS sonsuz kayan şerit).
// Açık Eksiltme'de fiyatı düşen ürünleri gösterir; her fiyat düşüşünde
// yeni bir giriş şeridin başına eklenir.
(function () {
    const wrap = document.querySelector('[data-alan="ticker"]');
    if (!wrap) {
        return;
    }
    const ray = wrap.querySelector('[data-alan="ticker-ray"]');
    const oncekiFiyat = {};
    let olaylar = [];
    const MAKS = 24;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function render() {
        if (!olaylar.length) {
            wrap.hidden = true;
            return;
        }
        wrap.hidden = false;
        const parca = olaylar.map((o) => {
            const gorsel = o.gorsel ? '<img class="ticker-gorsel" src="' + esc(o.gorsel) + '" alt="">' : '';
            return '<a class="ticker-item' + (o.dustu ? ' dustu' : '') + '" href="/ilan/' + encodeURIComponent(o.id) + '">' +
                gorsel + '<span class="ticker-ok">▾</span> ' + esc(o.ad) +
                ' <strong>' + esc(o.fiyat) + '</strong></a>';
        }).join('');
        // İki kopya = kesintisiz döngü (translateX -50%)
        ray.innerHTML = parca + parca;
    }

    async function cek() {
        try {
            const yanit = await fetch('/api/ilanlar', { headers: { Accept: 'application/json' } });
            if (!yanit.ok) {
                return;
            }
            const veriler = await yanit.json();
            const dusenler = veriler.filter((o) => o.durum === 'dusuyor');
            let degisti = false;

            dusenler.forEach((o) => {
                const onceki = oncekiFiyat[o.id];
                if (onceki != null && o.guncelFiyat < onceki) {
                    // Fiyat düştü → ticker'a gir
                    olaylar.unshift({ id: o.id, ad: o.baslik, fiyat: o.guncelFiyatBicim, gorsel: o.gorselUrl, dustu: true });
                    degisti = true;
                }
                oncekiFiyat[o.id] = o.guncelFiyat;
            });

            // İlk yükleme: mevcut düşen ürünlerle doldur
            if (!olaylar.length && dusenler.length) {
                olaylar = dusenler.map((o) => ({ id: o.id, ad: o.baslik, fiyat: o.guncelFiyatBicim, gorsel: o.gorselUrl, dustu: false }));
                degisti = true;
            }

            if (olaylar.length > MAKS) {
                olaylar = olaylar.slice(0, MAKS);
            }
            if (degisti) {
                render();
            }
        } catch (e) {
            /* ağ hatası: sessizce geç */
        }
    }

    cek();
    setInterval(cek, 5000);
})();
