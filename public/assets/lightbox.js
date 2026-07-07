// Site geneli basit lightbox:
// Bir bağlantı (<a>) içinde OLMAYAN görsellere tıklanınca büyük halini açar.
// Kart/slider gibi linkli küçük görseller normal gezinmede kalır.
(function () {
    function kur() {
        const arka = document.createElement('div');
        arka.className = 'lightbox';
        arka.hidden = true;
        arka.innerHTML =
            '<button type="button" class="lightbox-kapat" aria-label="Kapat">&times;</button>' +
            '<img class="lightbox-resim" alt="">';
        document.body.appendChild(arka);

        const buyuk = arka.querySelector('.lightbox-resim');

        function ac(kaynak, alt) {
            buyuk.src = kaynak;
            buyuk.alt = alt || '';
            arka.hidden = false;
            document.body.classList.add('lightbox-acik');
        }
        function kapat() {
            arka.hidden = true;
            buyuk.removeAttribute('src');
            document.body.classList.remove('lightbox-acik');
        }

        document.addEventListener('click', (olay) => {
            // Lightbox içine tıklama: kapat
            if (arka.contains(olay.target)) {
                if (olay.target === buyuk) {
                    return; // resmin kendisine tıklamak kapatmasın
                }
                kapat();
                return;
            }

            const img = olay.target.closest('img');
            if (!img) {
                return;
            }
            // Bağlantı içindeki görseller (kart/slider) gezinmede kalsın
            if (img.closest('a')) {
                return;
            }
            // Devre dışı bırakılmış görseller
            if (img.classList.contains('lightbox-yok')) {
                return;
            }
            const kaynak = img.currentSrc || img.getAttribute('src');
            if (!kaynak) {
                return;
            }
            olay.preventDefault();
            ac(kaynak, img.getAttribute('alt'));
        });

        document.addEventListener('keydown', (olay) => {
            if (olay.key === 'Escape' && !arka.hidden) {
                kapat();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', kur);
    } else {
        kur();
    }
})();
