// Canlı müzayede istemcisi (Laravel):
//  1) Her saniye yerel geri sayım (sayaç).
//  2) Her birkaç saniyede /api/ilanlar'dan güncel fiyat/durum çeker (polling).
//  3) Teklif formunu AJAX ile gönderir; sonucu anında uygular.

const POLL_MS = 4000;
const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const KULLANICI_ID = document.querySelector('meta[name="user-id"]')?.getAttribute('content') ?? '';

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

    document.querySelectorAll('[data-id][data-durum]').forEach((kalem) => {
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
                yenilemeGerekli = true;
            }
        } else if (durum === 'dusuyor' && kalem.dataset.sonrakiDusus) {
            const kalan = Number(kalem.dataset.sonrakiDusus) - simdi;
            sayac.textContent = bicimleMMSS(kalan);
            if (kalan <= 0) {
                yenilemeGerekli = true;
            }
        }
    });

    if (yenilemeGerekli) {
        yenilemeGerekli = false;
        ilanlariCek();
    }
}

// --- 2) Polling ---
function ozetUygula(kalem, o) {
    const oncekiDurum = kalem.dataset.durum;
    const oncekiMin = Number(kalem.dataset.min || 0);

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
        const oncekiFiyat = Number(fiyat.dataset.deger || 0);
        rakamGuncelle(fiyat, o.guncelFiyat);
        // Fiyat değişince flash: düşünce yeşil, yükselince kırmızı
        if (oncekiFiyat > 0 && o.guncelFiyat !== oncekiFiyat) {
            const sinif = o.guncelFiyat < oncekiFiyat ? 'dustu' : 'yukseldi';
            fiyat.classList.remove('dustu', 'yukseldi');
            void fiyat.offsetWidth; // animasyonu yeniden tetikle
            fiyat.classList.add(sinif);
            setTimeout(() => fiyat.classList.remove(sinif), 20000);

            // Fiyat düşünce sayacı da yeşil yak
            if (sinif === 'dustu') {
                const sayacEl = kalem.querySelector('[data-alan="sayac"]');
                if (sayacEl) {
                    sayacEl.classList.remove('dustu');
                    void sayacEl.offsetWidth;
                    sayacEl.classList.add('dustu');
                    setTimeout(() => sayacEl.classList.remove('dustu'), 20000);
                }
            }
        }
    }
    const rozet = kalem.querySelector('.rozet');
    if (rozet) {
        rozet.textContent = o.durumEtiket;
    }

    // Taban fiyata ulaşıldıysa geri sayımı gizle
    const sayacKutu = kalem.querySelector('[data-alan="sayac"]');
    if (sayacKutu) {
        sayacKutu.style.display = o.tabanaUlasti ? 'none' : '';
    }
    const sayacEtiketEl = kalem.querySelector('[data-alan="sayac-etiket"]');
    if (sayacEtiketEl && o.durum === 'dusuyor') {
        sayacEtiketEl.style.display = o.tabanaUlasti ? 'none' : '';
    }
    // Açık Eksiltme'de teklif verilmez; +/- ayar kutusu gizli (gizli miktar düşen fiyatı gönderir)
    const peyKutu = kalem.querySelector('.pey-kutu');
    if (peyKutu) {
        peyKutu.style.display = o.durum === 'dusuyor' ? 'none' : '';
    }

    // Yapı değişimi (faz/kapanış) olduğunda sayfayı tazele.
    if (o.durum === 'kapandi' || (oncekiDurum === 'dusuyor' && o.durum === 'acik_artirma')) {
        window.location.reload();
        return;
    }

    // Girilebilir max input: min'i güncelle; kullanıcı yazmıyorsa ve değer düşükse yükselt
    const miktar = kalem.querySelector('[data-alan="miktar"]');
    if (miktar) {
        miktar.min = o.minTeklif;
        if (document.activeElement !== miktar && Number(miktar.value) < o.minTeklif) {
            miktar.value = o.minTeklif;
        }
    }
    // Kullanıcının durumu: önde (yeşil) / geçildi (kırmızı) / gizli
    const onde = kalem.querySelector('[data-alan="onde"]');
    if (onde) {
        if (o.benimDurum === 'onde') {
            onde.hidden = false;
            onde.textContent = '★ Şu an en yüksek teklife sahipsiniz';
            onde.className = 'onde-bilgi onde-yesil';
        } else if (o.benimDurum === 'gecildi') {
            onde.hidden = false;
            onde.textContent = '★ Teklifiniz geçilmiştir';
            onde.className = 'onde-bilgi onde-kirmizi';
        } else {
            onde.hidden = true;
        }
    }

    // Kendi gizli maksimum teklifi (yalnızca kullanıcıya)
    const bmax = kalem.querySelector('[data-alan="benim-max"]');
    if (bmax) {
        if (o.benimMax) {
            bmax.hidden = false;
            const t = bmax.querySelector('[data-alan="benim-max-tutar"]');
            if (t) {
                t.textContent = o.benimMaxBicim;
            }
        } else {
            bmax.hidden = true;
        }
    }

    // Yeni teklif geldiyse (min yükseldiyse) sayacı kırmızı yanıp söndür (fade in/out)
    if (oncekiMin > 0 && o.minTeklif > oncekiMin) {
        const sayac = kalem.querySelector('[data-alan="sayac"]');
        if (sayac) {
            sayac.classList.remove('yeni-teklif');
            void sayac.offsetWidth; // animasyonu yeniden tetikle
            sayac.classList.add('yeni-teklif');
            setTimeout(() => sayac.classList.remove('yeni-teklif'), 20000);
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
            const kalem = document.querySelector('[data-id="' + o.id + '"][data-durum]');
            if (kalem) {
                ozetUygula(kalem, o);
            }
        });
    } catch (e) {
        // ağ hatası: sessizce geç
    }
}

// --- 3) AJAX teklif (modal onayı ile) ---
let bekleyenForm = null;

async function teklifGonder(form) {
    const mesaj = form.querySelector('[data-alan="teklif-mesaj"]');
    try {
        const yanit = await fetch('/teklif', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF,
            },
            body: new FormData(form),
        });
        const sonuc = await yanit.json();

        if (yanit.ok && sonuc.ok) {
            mesaj.textContent = '✓ Teklif alındı';
            mesaj.className = 'teklif-mesaj basarili';
            ozetUygula(form.closest('[data-id]'), sonuc.ilan);
        } else {
            const hata = sonuc.errors?.miktar?.[0] ?? sonuc.message ?? 'Teklif reddedildi';
            mesaj.textContent = hata;
            mesaj.className = 'teklif-mesaj hatali';
        }
    } catch (e) {
        mesaj.textContent = 'Bağlantı hatası';
        mesaj.className = 'teklif-mesaj hatali';
    }
}

function modalKapat() {
    bekleyenForm = null;
    const modal = document.querySelector('[data-alan="teklif-modal"]');
    if (modal) {
        modal.hidden = true;
    }
}

function teklifBagla() {
    const modal = document.querySelector('[data-alan="teklif-modal"]');

    document.querySelectorAll('.teklif-form').forEach((form) => {
        form.addEventListener('submit', (olay) => {
            olay.preventDefault();
            const tutar = Number(form.querySelector('[data-alan="miktar"]')?.value || 0);
            if (tutar <= 0) {
                return;
            }
            bekleyenForm = form;
            if (modal) {
                const tutarEl = modal.querySelector('[data-alan="modal-tutar"]');
                if (tutarEl) {
                    tutarEl.textContent = new Intl.NumberFormat('tr-TR').format(tutar) + ' ₺';
                }
                modal.hidden = false;
            } else {
                teklifGonder(form); // modal yoksa doğrudan gönder
            }
        });
    });

    if (modal) {
        modal.querySelector('[data-alan="modal-onayla"]')?.addEventListener('click', () => {
            const f = bekleyenForm;
            modalKapat();
            if (f) {
                teklifGonder(f);
            }
        });
        modal.querySelector('[data-alan="modal-vazgec"]')?.addEventListener('click', modalKapat);
        modal.addEventListener('click', (olay) => {
            if (olay.target === modal) {
                modalKapat();
            }
        });
        document.addEventListener('keydown', (olay) => {
            if (olay.key === 'Escape' && !modal.hidden) {
                modalKapat();
            }
        });
    }
}

// --- Kayan rakam (sliding digits) efekti ---
// Her hane 0-9'luk dikey bir şerittir; değer değişince translateY ile yeni rakama kayar.
const trBicim = new Intl.NumberFormat('tr-TR');

function rakamModel(el, sayi, animasyonlu) {
    const metin = trBicim.format(sayi) + ' ₺';
    const oncekiMetin = el.dataset.metin || '';

    // Yapı (uzunluk) aynıysa yalnızca şeritleri kaydır -> pürüzsüz animasyon.
    if (animasyonlu && el._seritler && oncekiMetin.length === metin.length) {
        let i = 0;
        for (const ch of metin) {
            if (ch >= '0' && ch <= '9') {
                el._seritler[i++].style.transform = 'translateY(-' + Number(ch) + 'em)';
            }
        }
        el.dataset.metin = metin;
        el.dataset.deger = sayi;
        return;
    }

    // Hane sayısı değişti (ya da ilk kurulum): baştan çiz.
    el.classList.add('rakam');
    el.innerHTML = '';
    el._seritler = [];

    for (const ch of metin) {
        if (ch >= '0' && ch <= '9') {
            const hane = document.createElement('span');
            hane.className = 'hane';
            const serit = document.createElement('span');
            serit.className = 'serit';
            if (!animasyonlu) {
                serit.style.transition = 'none';
            }
            for (let d = 0; d <= 9; d++) {
                const r = document.createElement('span');
                r.textContent = String(d);
                serit.appendChild(r);
            }
            serit.style.transform = 'translateY(-' + Number(ch) + 'em)';
            hane.appendChild(serit);
            el.appendChild(hane);
            el._seritler.push(serit);
            if (!animasyonlu) {
                requestAnimationFrame(() => { serit.style.transition = ''; });
            }
        } else {
            const sabit = document.createElement('span');
            sabit.className = 'sabit';
            sabit.textContent = ch;
            el.appendChild(sabit);
        }
    }

    el.dataset.metin = metin;
    el.dataset.deger = sayi;
}

function rakamKur(el) {
    rakamModel(el, Number(el.dataset.deger || 0), false);
}

function rakamGuncelle(el, sayi) {
    if (!el._seritler) {
        rakamKur(el);
    }
    rakamModel(el, sayi, true);
}

// --- Pey adımı (artırım tablosu) ile +/- ---
function peyAdimiHesap(deger) {
    const tablo = window.PEY_ADIMLARI || [];
    for (const k of tablo) {
        if (deger >= k.alt && (k.ust == null || deger <= k.ust)) {
            return k.adim;
        }
    }
    return 50;
}

function peyStepperBagla() {
    document.querySelectorAll('.teklif-form').forEach((form) => {
        const input = form.querySelector('[data-alan="miktar"]');
        if (!input) {
            return;
        }
        const arti = form.querySelector('[data-alan="pey-arti"]');
        const eksi = form.querySelector('[data-alan="pey-eksi"]');
        if (arti) {
            arti.addEventListener('click', () => {
                const v = Number(input.value) || 0;
                input.value = v + peyAdimiHesap(v);
            });
        }
        if (eksi) {
            eksi.addEventListener('click', () => {
                const v = Number(input.value) || 0;
                const min = Number(input.min) || 0;
                input.value = Math.max(min, v - peyAdimiHesap(v));
            });
        }
    });
}

// --- Otomatik tamamlamalı arama ---
function htmlKacis(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
}

function aramaBagla() {
    const kutu = document.querySelector('[data-alan="arama"]');
    if (!kutu) {
        return;
    }
    const girdi = kutu.querySelector('[data-alan="arama-girdi"]');
    const liste = kutu.querySelector('[data-alan="arama-oneri"]');
    let zamanlayici = null;
    let sonSorgu = -1;

    function gizle() {
        liste.hidden = true;
    }

    function goster(veriler) {
        if (!veriler.length) {
            liste.innerHTML = '<li class="arama-bos">Sonuç bulunamadı</li>';
            liste.hidden = false;
            return;
        }
        liste.innerHTML = veriler.map((o) => {
            const altParcalari = [];
            if (o.lotNo) {
                altParcalari.push('Lot ' + o.lotNo);
            }
            if (o.altBaslik) {
                altParcalari.push(htmlKacis(o.altBaslik));
            }
            const gorsel = o.gorselUrl
                ? '<img src="' + htmlKacis(o.gorselUrl) + '" alt="">'
                : '';
            return '<li><a href="' + htmlKacis(o.url) + '">' +
                '<span class="arama-gorsel">' + gorsel + '</span>' +
                '<span class="arama-metin"><strong>' + htmlKacis(o.baslik) + '</strong>' +
                '<small>' + altParcalari.join(' · ') + '</small></span></a></li>';
        }).join('');
        liste.hidden = false;
    }

    async function cek(q) {
        try {
            const yanit = await fetch('/api/ara?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!yanit.ok) {
                return;
            }
            goster(await yanit.json());
        } catch (e) {
            /* sessizce yut */
        }
    }

    girdi.addEventListener('input', () => {
        const q = girdi.value.trim();
        clearTimeout(zamanlayici);
        if (q.length < 2) {
            liste.innerHTML = '';
            gizle();
            return;
        }
        zamanlayici = setTimeout(() => cek(q), 220);
    });

    girdi.addEventListener('focus', () => {
        if (girdi.value.trim().length >= 2 && liste.children.length) {
            liste.hidden = false;
        }
    });

    girdi.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            gizle();
            girdi.blur();
        }
    });

    document.addEventListener('click', (e) => {
        if (!kutu.contains(e.target)) {
            gizle();
        }
    });
}

document.querySelectorAll('[data-alan="fiyat"]').forEach(rakamKur);

sayaclariGuncelle();
setInterval(sayaclariGuncelle, 1000);
setInterval(ilanlariCek, POLL_MS);
teklifBagla();
peyStepperBagla();
aramaBagla();
