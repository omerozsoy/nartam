// Otomatik tamamlamalı arama (üst menüde, tüm sayfalarda).
// Öneri açılır listesi her sayfada; #lotlar varsa alttaki listeyi de canlı filtreler.
(function () {
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

        // Sayfadaki listeyi de canlı filtrele (varsa)
        function izgaraFiltrele(q) {
            const aktif = q.length >= 2;
            const kucuk = q.toLowerCase();
            let gorunen = 0;
            document.querySelectorAll('#lotlar .lot').forEach((kart) => {
                const metin = kart.dataset.ara || '';
                const eslesti = !aktif || metin.indexOf(kucuk) !== -1;
                kart.classList.toggle('arama-gizli', !eslesti);
                if (eslesti) {
                    gorunen++;
                }
            });
            document.querySelectorAll('.lot-bolum').forEach((bolum) => {
                const toplam = bolum.querySelectorAll('.lot').length;
                const gizli = bolum.querySelectorAll('.lot.arama-gizli').length;
                bolum.classList.toggle('arama-gizli', aktif && toplam > 0 && gizli === toplam);
            });
            const slider = document.querySelector('[data-alan="slider"]');
            if (slider) {
                slider.classList.toggle('arama-gizli', aktif);
            }
            const yok = document.querySelector('[data-alan="arama-yok"]');
            if (yok) {
                yok.hidden = !(aktif && gorunen === 0);
            }
        }

        girdi.addEventListener('input', () => {
            const q = girdi.value.trim();
            izgaraFiltrele(q);
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', aramaBagla);
    } else {
        aramaBagla();
    }
})();
